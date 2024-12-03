if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "overview-new") {

    wps_js.traffic_data_success = function(data) {
        return data;
    };

    wps_js.traffic_data_error = function(error) {
        console.error('Traffic data error:', error);
        return error;
    };

    function loadTrafficData(startDate = null, endDate = null, date_filter = null) {
        return new Promise((resolve, reject) => {
            let data = {
                'action': 'wp_statistics_traffic_summary_metabox_get_data',
                'wps_nonce': wps_js.global.rest_api_nonce
            };

            if (date_filter) {
                data.date_filter = date_filter;
            }

            if (startDate && endDate) {
                data.from = startDate;
                data.to = endDate;
            }

            wps_js.traffic_data_success = function(data) {
                resolve(data);
                return data;
            };

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

    function updateTrafficData(date_filter, startDate = null, endDate = null) {
        const today = moment().format('YYYY-MM-DD');
        
        switch(date_filter) {
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'yesterday':
                startDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                endDate = startDate;
                break;
            case 'this_week':
                startDate = moment().startOf('week').format('YYYY-MM-DD');
                endDate = today;
                break;
            case 'last_week':
                startDate = moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD');
                break;
            case 'this_month':
                startDate = moment().startOf('month').format('YYYY-MM-DD');
                endDate = moment().endOf('month').format('YYYY-MM-DD');
                break;
            case 'last_month':
                startDate = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
                break;
            case '7days':
                startDate = moment().subtract(6, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '14days':
                startDate = moment().subtract(13, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '30days':
                startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '90days':
                startDate = moment().subtract(89, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '6months':
                startDate = moment().subtract(6, 'months').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '12months':
                startDate = moment().subtract(12, 'months').format('YYYY-MM-DD');
                endDate = today;
                break;
            case 'this_year':
                startDate = moment().startOf('year').format('YYYY-MM-DD');
                endDate = moment().endOf('year').format('YYYY-MM-DD');
                break;
            case 'last_year':
                startDate = moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD');
                break;
            case 'total':
                startDate = null;
                endDate = null;
                break;
            default:
                startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                endDate = today;
        }

        loadTrafficData(startDate, endDate, date_filter).then(function(response) {
            renderMetaboxContent(response);
        });
    }

    function renderMetaboxContent(response) {
        let metaBoxInner = jQuery('#traffic_summary .inside');
        
        if (response && response.output) {
            metaBoxInner.html(response.output);
            initDatePickerHandlers();  
        }

        let selector = "#traffic_summary .handle-actions button:first";
        if (!jQuery('#traffic_summary .wps-refresh').length) {
            jQuery(`<button class="handlediv wps-refresh" type="button" title="` + wps_js._('reload') + `"></button>`).insertBefore(selector);
        }

        const key = 'traffic_summary';
        let html = '<div class="c-footer"><div class="c-footer__filter js-widget-filters">';
        if (response.options && response.options.datepicker) {
            let defaultDate='30days';
            if(response.filters && response.filters.date && response.filters.date.filter){
                
                if(response.filters.date.type==='custom'){
                    if(response.filters.date.filter.from){
                        defaultDate=response.filters.date.filter.from +' - '+ response.filters.date.filter.to;
                    }else{
                        defaultDate='30days';
                    }
                }else{
                    defaultDate=response.filters.date.filter;
                }
            }

         html += `
            <button class="c-footer__filter__btn js-filters-toggle">` + wps_js._(`str_${defaultDate}`) + `</button>
            <div class="c-footer__filters">
                <div class="c-footer__filters__current-filter">
                    <span class="c-footer__current-filter__title js-filter-title">` + wps_js._(`str_${defaultDate}`) + `</span>
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
            </div> `;
        }
        html += `</div>`;
        if (response.options && response.options.button) {
            html += response.options.button;
         }
         html += `</div></div>`;

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
                    
                    jQuery('.js-filter-title').text('Custom Range');
                    jQuery('.hs-filter-range').text(
                        startDate === endDate ? 
                        picker.startDate.format('MMM D, YYYY') : 
                        picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY')
                    );
                    jQuery('.js-filters-toggle').text('Custom Range');
                    jQuery('.c-footer__filters').removeClass('is-active');
                    jQuery('.postbox.has-focus').removeClass('has-focus');

                    showLoadingSkeleton();
                    loadTrafficData(startDate, endDate, 'custom').then(renderMetaboxContent);
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
            loadTrafficData(startDate, endDate, 'custom').then(renderMetaboxContent);
        });

        // Handle date selection without apply button
        jQuery('.js-datepicker-input').off('hide.daterangepicker').on('hide.daterangepicker', function(ev, picker) {
            if (picker.startDate) {
                const startDate = picker.startDate.format('YYYY-MM-DD');
                const endDate = picker.endDate ? picker.endDate.format('YYYY-MM-DD') : startDate;
                
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
                loadTrafficData(startDate, endDate, 'custom').then(renderMetaboxContent);
            }
        });

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
                    endDate = moment().endOf('month').format('YYYY-MM-DD');
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
                    endDate = moment().endOf('year').format('YYYY-MM-DD');
                    break;
            }

            jQuery('.js-filter-title').text(jQuery(this).text());
            jQuery('.hs-filter-range').text(moment(startDate).format('MMM D, YYYY') + ' - ' + moment(endDate).format('MMM D, YYYY'));
            jQuery('.js-filters-toggle').text(jQuery(this).text());
            jQuery('.c-footer__filters').removeClass('is-active');
            jQuery('.postbox.has-focus').removeClass('has-focus');

            showLoadingSkeleton();
            loadTrafficData(startDate, endDate, filter).then(renderMetaboxContent);
        });
    }

    function showLoadingSkeleton() {
        let metaBoxInner = jQuery('#traffic_summary .inside');
        metaBoxInner.html('<div class="wps-skeleton-container"><div class="wps-skeleton-container__skeleton wps-skeleton-container__skeleton--full wps-skeleton-container__skeleton--h-150"></div></div>');
    }

    // Initial load without date_filter
    loadTrafficData().then(renderMetaboxContent);

    // Add refresh button click handler
    jQuery(document).on('click', '#traffic_summary .wps-refresh', function() {
        // Refresh without sending date_filter
        loadTrafficData().then(renderMetaboxContent);
    });

    // Handle filter changes
    jQuery(document).on('change', '.js-filter-select', function() {
        const date_filter = jQuery(this).val();
        const today = moment().format('YYYY-MM-DD');
        let startDate, endDate;
        
        switch(date_filter) {
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'yesterday':
                startDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                endDate = startDate;
                break;
            case 'this_week':
                startDate = moment().startOf('week').format('YYYY-MM-DD');
                endDate = today;
                break;
            case 'last_week':
                startDate = moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD');
                break;
            case 'this_month':
                startDate = moment().startOf('month').format('YYYY-MM-DD');
                endDate = moment().endOf('month').format('YYYY-MM-DD');
                break;
            case 'last_month':
                startDate = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
                break;
            case '7days':
                startDate = moment().subtract(6, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '14days':
                startDate = moment().subtract(13, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '30days':
                startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '90days':
                startDate = moment().subtract(89, 'days').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '6months':
                startDate = moment().subtract(6, 'months').format('YYYY-MM-DD');
                endDate = today;
                break;
            case '12months':
                startDate = moment().subtract(12, 'months').format('YYYY-MM-DD');
                endDate = today;
                break;
            case 'this_year':
                startDate = moment().startOf('year').format('YYYY-MM-DD');
                endDate = moment().endOf('year').format('YYYY-MM-DD');
                break;
            case 'last_year':
                startDate = moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD');
                endDate = moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD');
                break;
            case 'total':
                startDate = null;
                endDate = null;
                break;
            default:
                startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                endDate = today;
        }

        loadTrafficData(startDate, endDate, date_filter).then(renderMetaboxContent);
    });

     jQuery(document).on('change', '.js-date-custom', function() {
        const startDate = jQuery('.js-date-custom-from').val();
        const endDate = jQuery('.js-date-custom-to').val();
        
        if (startDate && endDate) {
            loadTrafficData(startDate, endDate, 'custom').then(renderMetaboxContent);
        }
    });
}