if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "pages") {
    let inMemoryCache = null;

    /**
     * Initialize the filter modal.
     */
    new FilterModal({
        formSelector: '#wp_statistics_visitors_filter_form',
        onOpen: handleModalOpen,
        onSubmit: handleModalSubmit,
    });

    /**
     * Handles the modal open event.
     */
    function handleModalOpen() {
        const containerSelector = ".wps-modal-filter-form";
        const spinner = new Spinner({ container: containerSelector });
        const currentReferrer = wps_js.getLinkParams('url');
        const dropdowns = jQuery(containerSelector).find('.filter-select');

        if (inMemoryCache) {
            populateFilters(inMemoryCache, dropdowns);
        } else {
            fetchFilterData(spinner, dropdowns);
        }

        initializeSelect2(containerSelector, currentReferrer);
    }

    /**
     * Fetches filter data via AJAX.
     * @param {Object} spinner - The spinner instance.
     * @param {Object} dropdowns - The dropdown elements.
     */
    function fetchFilterData(spinner, dropdowns) {
        spinner.show();

        let params = {
            wps_nonce: wps_js.global.rest_api_nonce,
            action: 'wp_statistics_page_insight_filters',
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
                    populateFilters(data, dropdowns);
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
     * Populates filters into the dropdowns.
     * @param {Object} data - The filter data.
     * @param {Object} dropdowns - The dropdown elements.
     */
    function populateFilters(data, dropdowns) {
        dropdowns.each(function () {
            const dropdown = jQuery(this);
            const fieldName = dropdown.attr('data-type');
            const options = data[fieldName];

            if (options) {
                dropdown.empty().append('<option value="">' + wps_js._('all') + '</option>');
                Object.keys(options).forEach(key => {
                    dropdown.append(`<option value="${key}">${options[key]}</option>`);
                });
            }
        });
    }

    /**
     * Initializes the Select2 dropdowns.
     * @param {string} containerSelector - The selector for the container.
     * @param {string} currentReferrer - The current referrer value.
     */
    function initializeSelect2(containerSelector, currentReferrer) {
        jQuery(`${containerSelector} .wps-select2`).select2({
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term,
                        action: 'wp_statistics_search_url',
                        paged: params.page || 1
                    };

                    if (wps_js.isset(wps_js.global, 'request_params')) {
                        const requestParams = wps_js.global.request_params;
                        if (requestParams.page) {
                            query.page = requestParams.page;
                        }
                    }

                    return query;
                },
                processResults: function (data) {
                    return {
                        results: data.results.map(item => ({
                            id: decodeURIComponent(item.id),
                            text: item.text,
                        })),
                    };
                },
                error: function (xhr, status, error) {
                    console.error('AJAX request error:', status, error);
                },
            },
            minimumInputLength: 1,
            placeholder: wps_js._('Select a referrer'),
            allowClear: true,
        }).off('change');

        // Pre-select the current referrer if available
        if (currentReferrer) {
            const select = jQuery(`${containerSelector} .wps-select2`);
            const decodedValue = decodeURIComponent(currentReferrer);
            const option = new Option(decodedValue, decodedValue, true, true);
            select.append(option).trigger('change');
        }
    }

    /**
     * Handles the modal submit event.
     * @param {Event} e - The submit event.
     */
    function handleModalSubmit(e) {
        const targetForm = jQuery(e.target);
        disableEmptyFields(targetForm);
        appendSortingOrder(targetForm);
    }

    /**
     * Disables empty fields in the form.
     * @param {Object} form - The form element.
     */
    function disableEmptyFields(form) {
        const forms = {
            select: ['author_id', 'url'],
        };

        Object.keys(forms).forEach((type) => {
            forms[type].forEach((name) => {
                const input = form.find(`${type}[name="${name}"]`);
                if (input.val() && input.val().trim() === '') {
                    input.prop('disabled', true);
                }
            });
        });
    }

    /**
     * Appends the sorting order to the form if applicable.
     * @param {Object} form - The form element.
     */
    function appendSortingOrder(form) {
        const order = wps_js.getLinkParams('order');
        if (order) {
            form.append('<input type="hidden" name="order" value="' + order + '">');
        }
    }

}
