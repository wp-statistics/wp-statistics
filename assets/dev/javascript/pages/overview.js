if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "overview-new") {

    // Define callback functions for ajaxQ
    wps_js.traffic_data_success = function(data) {
        return data;
    };

    wps_js.traffic_data_error = function(error) {
        console.error('Traffic data error:', error);
        return error;
    };

    function loadTrafficData(startDate = null, endDate = null) {
        return new Promise((resolve, reject) => {
            let data = {
                'action': 'wp_statistics_traffic_summary_metabox_get_data',
                'wps_nonce': wps_js.global.rest_api_nonce
            };

            // Handle date parameters
            if (startDate) {
                data.from = startDate;
                data.to = endDate || startDate;
            } else {
                // Default to last 30 days
                data.from = moment().subtract(29, 'days').format('YYYY-MM-DD');
                data.to = moment().format('YYYY-MM-DD');
            }

            // Store the resolve function to be called from the success callback
            wps_js.traffic_data_success = function(data) {
                resolve(data);
                return data;
            };

            // Store the reject function to be called from the error callback
            wps_js.traffic_data_error = function(error) {
                reject(error);
                return error;
            };

            wps_js.ajaxQ(
                wps_js.global.admin_url + 'admin-ajax.php',
                data,
                'traffic_data_success',
                'traffic_data_error',
                'GET',
                false
            );
        });
    }

    function renderMetaboxContent(response) {
        let metaBoxInner = jQuery('#traffic_summary .inside');
        
        if (response && response.output) {
            metaBoxInner.html(response.output);
            initDatePickerHandlers(); // Initialize date picker handlers after content is rendered
        }

        let selector = "#traffic_summary .handle-actions button:first";
        if (!jQuery('#traffic_summary .wps-refresh').length) {
            jQuery(`<button class="handlediv wps-refresh" type="button" title="` + wps_js._('reload') + `"></button>`).insertBefore(selector);
        }

        const key = 'traffic_summary';
        let html = '<div class="c-footer"><div class="c-footer__filter js-widget-filters">';
        html += `
            <button class="c-footer__filter__btn js-filters-toggle">` + wps_js._('str_30days') + `</button>
            <div class="c-footer__filters">
                <div class="c-footer__filters__current-filter">
                    <span class="c-footer__current-filter__title js-filter-title">Last 30 days</span>
                    <span class="c-footer__current-filter__date-range hs-filter-range">` + moment().subtract(29, 'days').format('MMM D, YYYY') + ' - ' + moment().format('MMM D, YYYY') + `</span>
                </div>
                <div class="c-footer__filters__list">
                    <button data-metabox-key="${key}" data-filter="today" class="c-footer__filters__list-item">` + wps_js._('str_today') + `</button>
                    <button data-metabox-key="${key}" data-filter="yesterday" class="c-footer__filters__list-item">` + wps_js._('str_yesterday') + `</button>
                    <button data-metabox-key="${key}" data-filter="this_week" class="c-footer__filters__list-item">` + wps_js._('str_this_week') + `</button>
                    <button data-metabox-key="${key}" data-filter="last_week" class="c-footer__filters__list-item">` + wps_js._('str_last_week') + `</button>
                    <button data-metabox-key="${key}" data-filter="this_month" class="c-footer__filters__list-item">` + wps_js._('str_this_month') + `</button>
                    <button data-metabox-key="${key}" data-filter="last_month" class="c-footer__filters__list-item">` + wps_js._('str_last_month') + `</button>
                    <button class="c-footer__filters__list-item c-footer__filters__list-item--more js-show-more-filters">` + wps_js._('str_more') + `</button>
                    <div class="c-footer__filters__more-filters js-more-filters">
                        <button data-metabox-key="${key}" data-filter="7days" class="c-footer__filters__list-item">` + wps_js._('str_7days') + `</button>
                        <button data-metabox-key="${key}" data-filter="30days" class="c-footer__filters__list-item">` + wps_js._('str_30days') + `</button>
                        <button data-metabox-key="${key}" data-filter="90days" class="c-footer__filters__list-item">` + wps_js._('str_90days') + `</button>
                        <button data-metabox-key="${key}" data-filter="6months" class="c-footer__filters__list-item">` + wps_js._('str_6months') + `</button>
                        <button data-metabox-key="${key}" data-filter="this_year" class="c-footer__filters__list-item">` + wps_js._('str_this_year') + `</button>
                        <button class="c-footer__filters__close-more-filters js-close-more-filters">` + wps_js._('str_back') + `</button>
                    </div>
                    <input type="text" class="c-footer__filters__custom-date-input js-datepicker-input"/>
                    <button data-metabox-key="${key}" data-filter="custom" class="c-footer__filters__list-item c-footer__filters__list-item--custom js-custom-datepicker">` + wps_js._('str_custom') + `</button>
                </div>
            </div>
        </div>`;

        html += `<div class="c-footer__more">`;
        html += `<a class="c-footer__more__link" href="` + response.page_url + `">more<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.4191 7.98577L7.73705 5.22727L8.44415 4.5L12.3333 8.50003L8.44415 12.5L7.73705 11.7727L10.4191 9.01429H4.33325V7.98577H10.4191Z" fill="#56585A"/></svg></a>`;
        html += `</div></div></div>`;

        metaBoxInner.append(html);
    }

    function initDatePickerHandlers() {
        // Toggle filters visibility using event delegation
        jQuery(document).off('click', '.js-filters-toggle').on('click', '.js-filters-toggle', function() {
            jQuery('.js-widget-filters').removeClass('is-active');
            jQuery('.postbox').removeClass('has-focus');
            jQuery(this).closest('.js-widget-filters').toggleClass('is-active');
            jQuery(this).closest('.postbox').toggleClass('has-focus');

            /**
             * Open filters to the downside if there's not enough space.
             */
            const targetTopPosition = jQuery(this)[0].getBoundingClientRect().top;
            if (targetTopPosition < 350) {
                jQuery(this).closest('.js-widget-filters').addClass('is-down');
            }
        });

        // Handle show more filters click
        jQuery(document).off('click', '.js-show-more-filters').on('click', '.js-show-more-filters', function(e) {
            e.preventDefault();
            jQuery(this).closest('.c-footer__filters__list').find('.js-more-filters').addClass('is-open');
        });

        // Handle close more filters click
        jQuery(document).off('click', '.js-close-more-filters').on('click', '.js-close-more-filters', function(e) {
            e.preventDefault();
            jQuery(this).closest('.js-more-filters').removeClass('is-open');
        });

        // Initialize datepicker for custom date selection
        jQuery('.js-datepicker-input').each(function() {
            if (!jQuery(this).data('daterangepicker')) {
                jQuery(this).daterangepicker({
                    autoUpdateInput: false,
                    autoApply: true,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'YYYY-MM-DD'
                    }
                });
            }
        });

        // Handle custom date picker click
        jQuery(document).off('click', 'button[data-filter="custom"]').on('click', 'button[data-filter="custom"]', function() {
            var metaboxKey = jQuery(this).attr("data-metabox-key");
            var dateInput = jQuery('#' + metaboxKey + ' .inside .js-datepicker-input').first();
            
            if (!dateInput.data('daterangepicker')) {
                dateInput.daterangepicker({
                    autoUpdateInput: false,
                    autoApply: true,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'YYYY-MM-DD'
                    }
                });

                // Add event listener for date selection
                dateInput.on('apply.daterangepicker', function(ev, picker) {
                    const startDate = picker.startDate.format('YYYY-MM-DD');
                    const endDate = picker.endDate.format('YYYY-MM-DD');
                    
                    // Update UI
                    jQuery('.js-filter-title').text('Custom Range');
                    jQuery('.hs-filter-range').text(
                        startDate === endDate ? 
                        picker.startDate.format('MMM D, YYYY') : 
                        picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY')
                    );
                    jQuery('.js-filters-toggle').text('Custom Range');
                    jQuery('.c-footer__filters').removeClass('is-active');
                    jQuery('.postbox.has-focus').removeClass('has-focus');

                    // Load data
                    showLoadingSkeleton();
                    loadTrafficData(startDate, endDate).then(renderMetaboxContent);
                });
            }
            
            dateInput.data('daterangepicker').show();
        });

        // Handle date selection
        jQuery('.js-datepicker-input').off('apply.daterangepicker').on('apply.daterangepicker', function(ev, picker) {
            const startDate = picker.startDate.format('YYYY-MM-DD');
            const endDate = picker.endDate.format('YYYY-MM-DD');
            
            // Update UI
            jQuery('.js-filter-title').text('Custom Range');
            jQuery('.hs-filter-range').text(
                startDate === endDate ? 
                picker.startDate.format('MMM D, YYYY') : 
                picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY')
            );
            jQuery('.js-filters-toggle').text('Custom Range');
            jQuery('.c-footer__filters').removeClass('is-active');
            jQuery('.postbox.has-focus').removeClass('has-focus');

            // Load data
            showLoadingSkeleton();
            loadTrafficData(startDate, endDate).then(renderMetaboxContent);
        });

        // Handle date selection without apply button
        jQuery('.js-datepicker-input').off('hide.daterangepicker').on('hide.daterangepicker', function(ev, picker) {
            if (picker.startDate) {
                const startDate = picker.startDate.format('YYYY-MM-DD');
                const endDate = picker.endDate ? picker.endDate.format('YYYY-MM-DD') : startDate;
                
                // Update UI
                jQuery('.js-filter-title').text('Custom Range');
                jQuery('.hs-filter-range').text(
                    startDate === endDate ? 
                    picker.startDate.format('MMM D, YYYY') : 
                    picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY')
                );
                jQuery('.js-filters-toggle').text('Custom Range');
                jQuery('.c-footer__filters').removeClass('is-active');
                jQuery('.postbox.has-focus').removeClass('has-focus');

                // Load data
                showLoadingSkeleton();
                loadTrafficData(startDate, endDate).then(renderMetaboxContent);
            }
        });

        // Handle filter button clicks (excluding special buttons)
        jQuery(document).off('click', '.c-footer__filters__list-item:not(.js-show-more-filters):not(.js-close-more-filters):not([data-filter="custom"])').on('click', '.c-footer__filters__list-item:not(.js-show-more-filters):not(.js-close-more-filters):not([data-filter="custom"])', function() {
            const filter = jQuery(this).data('filter');
            let startDate, endDate;

            switch(filter) {
                case 'today':
                    startDate = endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'yesterday':
                    startDate = endDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                    break;
                case 'this_week':
                    startDate = moment().startOf('week').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'last_week':
                    startDate = moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD');
                    endDate = moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD');
                    break;
                case 'this_month':
                    startDate = moment().startOf('month').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'last_month':
                    startDate = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                    endDate = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
                    break;
                case '7days':
                    startDate = moment().subtract(6, 'days').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case '30days':
                    startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case '90days':
                    startDate = moment().subtract(89, 'days').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case '6months':
                    startDate = moment().subtract(6, 'months').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'this_year':
                    startDate = moment().startOf('year').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
            }

            // Update UI
            jQuery('.js-filter-title').text(jQuery(this).text());
            jQuery('.hs-filter-range').text(moment(startDate).format('MMM D, YYYY') + ' - ' + moment(endDate).format('MMM D, YYYY'));
            jQuery('.js-filters-toggle').text(jQuery(this).text());
            jQuery('.c-footer__filters').removeClass('is-active');
            jQuery('.postbox.has-focus').removeClass('has-focus');

            // Load data
            showLoadingSkeleton();
            if (startDate === endDate) {
                loadTrafficData(startDate).then(renderMetaboxContent);
            } else {
                loadTrafficData(startDate, endDate).then(renderMetaboxContent);
            }
        });
    }

    function showLoadingSkeleton() {
        let metaBoxInner = jQuery('#traffic_summary .inside');
        metaBoxInner.html('<div class="wps-skeleton-container"><div class="wps-skeleton-container__skeleton wps-skeleton-container__skeleton--full wps-skeleton-container__skeleton--h-150"></div></div>');
    }

    // Initial load with default dates (last 30 days)
    loadTrafficData().then(renderMetaboxContent);

    // Add refresh button click handler
    jQuery(document).on('click', '#traffic_summary .wps-refresh', function() {
        showLoadingSkeleton();
        loadTrafficData().then(renderMetaboxContent);
    });
}