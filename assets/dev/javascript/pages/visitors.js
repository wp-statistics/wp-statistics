if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors") {
    let inMemoryCache = null;

    new FilterModal({
        formSelector: '#wp_statistics_visitors_filter_form',
        onOpen: handleVisitorsModalOpen,
        onSubmit: handleVisitorsModalSubmit,
    });

    /**
     * Handles the visitors filter modal open event.
     */
    function handleVisitorsModalOpen() {
        const containerSelector = "#wps-visitors-filter-form";
        const dropdowns = jQuery(containerSelector).find('.filter-select');
        const spinner = new Spinner({ container: containerSelector });

        if (inMemoryCache) {
            populateVisitorsFilters(inMemoryCache, dropdowns);
        } else {
            fetchVisitorsFilters(spinner, dropdowns);
        }
    }

    /**
     * Fetches visitor filter data via AJAX.
     * @param {Object} spinner - The spinner instance.
     * @param {Object} dropdowns - The dropdown elements.
     */
    function fetchVisitorsFilters(spinner, dropdowns) {
        spinner.show();

        let params = {
            wps_nonce: wps_js.global.rest_api_nonce,
            action: 'wp_statistics_visitors_page_filters',
        };
        params = Object.assign(params, wps_js.global.request_params);

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function (data) {
                if (data) {
                    inMemoryCache = data;
                    populateVisitorsFilters(data, dropdowns);
                }
            },
            error: function () {
                jQuery("span.tb-close-icon").click();
            },
            complete: function () {
                spinner.hide();
            }
        });
    }

    /**
     * Populates filters into the dropdowns for the visitors filter modal.
     * @param {Object} data - The filter data.
     * @param {Object} dropdowns - The dropdown elements.
     */
    function populateVisitorsFilters(data, dropdowns) {
        dropdowns.each(function () {
            const dropdown = jQuery(this);
            const fieldName = dropdown.attr('data-type');
            const options = data[fieldName];

            if (options) {
                dropdown.empty().append('<option value="">' + wps_js._('all') + '</option>');
                Object.keys(options).forEach(key => {
                    const formattedKey = key.toLowerCase().replace(/ /g, '_');
                    dropdown.append(`<option value="${formattedKey}">${options[key]}</option>`);
                });
            }
        });
    }

    /**
     * Handles the visitors filter modal submit event.
     * @param {Object} e - The submit event.
     */
    function handleVisitorsModalSubmit(e) {
        const targetForm = jQuery(e.target);
        disableEmptyVisitorsFields(targetForm);
        appendVisitorsSortingOrder(targetForm);
         return true;
    }

    /**
     * Disables empty fields in the visitors filter form.
     * @param {Object} form - The form element.
     */
    function disableEmptyVisitorsFields(form) {
        const forms = {
            input: ['ip'],
            select: ['agent', 'platform', 'location', 'referrer', 'user_id'],
        };

        Object.keys(forms).forEach((type) => {
            forms[type].forEach((name) => {
                const input = form.find(`${type}[name="${name}"]`);
                if (input.val().trim() === '') {
                    input.prop('disabled', true);
                }
            });
        });
    }

    /**
     * Appends the sorting order to the visitors filter form if applicable.
     * @param {Object} form - The form element.
     */
    function appendVisitorsSortingOrder(form) {
        const order = wps_js.getLinkParams('order');
        if (order) {
            form.append('<input type="hidden" name="order" value="' + order + '">');
        }
    }

    if (document.getElementById('trafficTrendsChart')) {
        const data = Wp_Statistics_Visitors_Object.traffic_chart_data;
        wps_js.new_line_chart(data, 'trafficTrendsChart', null);
    }

    if (document.getElementById('LoggedInUsersChart')) {
        const data = Wp_Statistics_Visitors_Object.logged_in_chart_data;
        wps_js.new_line_chart(data, 'LoggedInUsersChart', null);
    }
}
