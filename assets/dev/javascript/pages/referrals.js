if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    // Add Income Visitor Chart

    // Helper function to render a chart or display no results
    function renderChart(chartId, searchData) {
        const chartElement = document.getElementById(chartId);

        if (chartElement) {
            const parentElement = jQuery(`#${chartId}`).parent();
            const placeholder = wps_js.rectangle_placeholder();
            parentElement.append(placeholder);

            if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
                parentElement.html(wps_js.no_results());
                jQuery('.wps-ph-item').remove();
            } else {
                jQuery('.wps-ph-item').remove();
                jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
                parentElement.removeClass('c-chart__wps-skeleton');
                wps_js.new_line_chart(searchData, chartId, null);
            }
        }
    }

    if (typeof Wp_Statistics_Referrals_Object !== 'undefined') {
        const sourceCategoriesData = Wp_Statistics_Referrals_Object.source_category_chart_data;
        renderChart('sourceCategoriesChart', sourceCategoriesData);

        const socialMedia = Wp_Statistics_Referrals_Object.social_media_chart_data;
        renderChart('socialMediaChart', socialMedia);

        const incomeVisitorData = Wp_Statistics_Referrals_Object.search_engine_chart_data;
        renderChart('incomeVisitorChart', incomeVisitorData);
    }

    new FilterModal({
        formSelector: '#wps-referrals-filter-form',
        height: 205,
        onOpen: handleReferralModalOpen,
        onSubmit: handleReferralModalSubmit,
    });

    /**
     * Handles the referral filter modal open event.
     */
    function handleReferralModalOpen() {
        const containerSelector = "#wps-referral-filter-div";
        const currentReferrer = wps_js.getLinkParams('referrer');

        initializeReferralSelect2(containerSelector, currentReferrer);
    }

    /**
     * Initializes the Select2 dropdown for referral filters.
     * @param {string} containerSelector - The selector for the container.
     * @param {string} currentReferrer - The current referrer value.
     */
    function initializeReferralSelect2(containerSelector, currentReferrer) {
        jQuery(`${containerSelector} .wps-select2`).select2({
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term,
                        action: 'wp_statistics_search_referrers',
                        paged: params.page || 1
                    };

                    if (wps_js.isset(wps_js.global, 'request_params')) {
                        const requestParams = wps_js.global.request_params;
                        if (requestParams.page) query.page = requestParams.page;
                    }
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: data.results.map(item => ({
                            id: item.id,
                            text: item.text,
                        })),
                    };
                },
                error: function (xhr, status, error) {
                    console.error('AJAX request error:', status, error);
                },
            },
            placeholder: wps_js._('Select a referrer'),
            allowClear: true,
        }).off('change');

        // Pre-select the current referrer if available
        if (currentReferrer) {
            const select = jQuery(`${containerSelector} .wps-select2`);
            const option = new Option(currentReferrer, currentReferrer, true, true);
            select.append(option).trigger('change');
        }
    }

    /**
     * Handles the referral filter modal submit event.
     * @param {Object} e - The submit event.
     */
    function handleReferralModalSubmit(e) {
        const targetForm = jQuery(e.target);
        disableEmptyReferralFields(targetForm);
        showReferralSubmitLoading();
        return true;
    }

    /**
     * Disables empty fields in the referral filter form.
     * @param {Object} form - The form element.
     */
    function disableEmptyReferralFields(form) {
        const forms = {
            select: ['referrer'],
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
     * Shows loading state on the referral filter modal submit button.
     */
    function showReferralSubmitLoading() {
        jQuery(".wps-tb-window-footer .button-primary")
            .html(wps_js._('loading'))
            .addClass('loading');
    }
}
