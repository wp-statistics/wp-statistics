if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "overview-new") {
    function initializeDatePicker() {
        const datePickerElement = jQuery('.js-date-range-picker-input');
        const datePickerBtn = jQuery('.js-date-range-picker-btn');
        const datePickerForm = jQuery('.js-date-range-picker-form');

        if (datePickerElement.length) {
            datePickerBtn.on('click', function () {
                datePickerElement.trigger('click');
            });

            datePickerElement.daterangepicker({
                autoApply: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This Week': [moment().startOf('week'), moment().endOf('week')],
                    'Last Week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),
                locale: {
                    format: datePickerBtn.data('date-format') || 'MM/DD/YYYY'
                }
            });

            datePickerElement.on('apply.daterangepicker', function (ev, picker) {
                const inputFrom = datePickerForm.find('.js-date-range-picker-input-from').first();
                const inputTo = datePickerForm.find('.js-date-range-picker-input-to').first();
                inputFrom.val(picker.startDate.format('YYYY-MM-DD'));
                inputTo.val(picker.endDate.format('YYYY-MM-DD'));
                datePickerForm.submit();
            });
        }
    }



    jQuery.ajax({
        url: wps_js.global.admin_url + 'admin-ajax.php',
        type: 'GET',
        data: {
            'action': 'wp_statistics_traffic_summary_metabox_get_data',
            'wps_nonce': wps_js.global.rest_api_nonce,
        },
        success: function (response) {

            let metaBoxInner =jQuery('#traffic_summary .inside');

            if (response && response.output) {
                metaBoxInner.html(response.output);
            }

            let selector = "#traffic_summary" + " .handle-actions button:first";
            jQuery(`<button class="handlediv wps-refresh" type="button" title="` + wps_js._('reload') + `"></button>`).insertBefore(selector);
            const selectedDateFilter = 'Last 30 days';

            let html = '<div class="c-footer"><div class="c-footer__filter js-widget-filters">';
                 html += `

                <button data-date-format="M j, Y" class="c-footer__filter__btn js-date-range-picker-btn">
                <span>Last 30 Days</span>
                </button>
                <input type="text" class="c-footer__filters__custom-date-input js-date-range-picker-input">
                 <form action="" method="get" style="display: none" class="js-date-range-picker-form">
                    <input name="page" type="hidden" value="">
              
                </form>
             `;

            html += `</div><div class="c-footer__more">`;
            html += `<a class="c-footer__more__link" href="` + response.page_url + `">more<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.4191 7.98577L7.73705 5.22727L8.44415 4.5L12.3333 8.50003L8.44415 12.5L7.73705 11.7727L10.4191 9.01429H4.33325V7.98577H10.4191Z" fill="#56585A"/></svg></a>`;
            html += `</div></div>`;

            metaBoxInner.append(html);
            initializeDatePicker();
        }
    });

}