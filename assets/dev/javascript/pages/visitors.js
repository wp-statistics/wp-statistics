if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors" ) {
    // TickBox
    jQuery(document).on('click', "div#visitors-filter", function (e) {
        e.preventDefault();

        // Show
        tb_show('', '#TB_inline?&width=430&height=520&inlineId=visitors-filter-popup');

        // Add Content
        setTimeout(function () {

            var tickBox_DIV = "#wps-visitors-filter-form";
            if (!wps_js.exist_tag(tickBox_DIV + " input[type=submit]")) {

                // Set PlaceHolder
                jQuery(tickBox_DIV).html('<div style="height: 50px;"></div>' + wps_js.line_placeholder(5));

                // Check Use Cached Data
                var filter_data = localStorage.getItem('wp-statistics-visitors-filter') ? JSON.parse(localStorage.getItem('wp-statistics-visitors-filter')) : {};
                if (!wps_js.isset(filter_data, 'timestamp') || !wps_js.isset(filter_data, 'value') || (wps_js.isset(filter_data, 'timestamp') && wps_js.isset(filter_data, 'value'))) {

                    // Create Params
                    let params = {
                        'wps_nonce': wps_js.global.rest_api_nonce,
                        'action': 'wp_statistics_visitors_page_filters'
                    };
                    params = Object.assign(params, wps_js.global.request_params);

                    // Create Ajax
                    jQuery.ajax({
                        url: wps_js.global.admin_url + 'admin-ajax.php',
                        type: 'GET',
                        dataType: "json",
                        data: params,
                        timeout: 30000,
                        success: function (data) {

                            // Load function
                            wp_statistics_show_visitors_filter(tickBox_DIV, data);
                        },
                        error: function (xhr, status, error) {
                            jQuery("span.tb-close-icon").click();
                        }
                    });
                } else {
                    wp_statistics_show_visitors_filter(tickBox_DIV, filter_data['value']);
                }

            }
        }, 500);

    });

    // submit and disable empty value
    var FORM_ID = '#wp_statistics_visitors_filter_form';
    jQuery(document).on('submit', FORM_ID, function () {
        // Check IS IP
        var Input_IP = jQuery(FORM_ID + " input[name=ip]").val();
        if (Input_IP.length > 0 && (Input_IP.includes('#hash#') === false && wps_js.isIP(Input_IP) === false)) {
            alert(wps_js._('er_valid_ip'));
            return false;
        }

        // Remove Empty Parameter
        let forms = {
            'input': ['ip'],
            'select': ['agent', 'platform', 'location', 'referrer', 'user_id']
        };
        Object.keys(forms).forEach(function (type) {
            forms[type].forEach((name) => {
                let input = jQuery(FORM_ID + " " + type + "[name=" + name + "]");
                if (input.val().length < 1) {
                    input.prop('disabled', true);
                }
            });
        });

        // Set Order
        let order = wps_js.getLinkParams('order');
        if (order != null) {
            jQuery(this).append('<input type="hidden" name="order" value="' + order + '" /> ');
        }

        // Show Loading
        jQuery("span.filter-loading").html(wps_js._('please_wait'));

        return true;
    });

    // Show Filter form
    function wp_statistics_show_visitors_filter(tickBox_DIV, data) {

        // Create Table
        let html = '<table class="o-table">';

        // Show List Select
        let select = {
            /**
             * Key: global i18n
             * [0]: select name
             * [1]: data key from ajax
             */
            'browsers': ['agent', 'browsers'],
            'country': ['location', 'location'],
            'platform': ['platform', 'platform'],
            'referrer': ['referrer', 'referrer'],
            'user': ['user_id', 'users']
        };

        Object.keys(select).forEach((key) => {
            html += `<tr><td>${wps_js._(key)}</td></tr>`;
            html += `<tr><td><select name="${select[key][0]}" class="select2 wps-width-100" data-type-show="select2">`;
            html += `<option value=''>${wps_js._('all')}</option>`;
            let current_value = wps_js.getLinkParams(select[key][0]);
            Object.keys(data[select[key][1]]).forEach(function (item) {
                html += `<option value='${item}' ${((current_value != null && current_value.replace(/\+/g, ' ') == item) ? `selected` : ``)}>${data[select[key][1]][item]}</option>`;
            });
            html += `</select></td></tr>`;
        });

        // Add IP
        html += `<tr><td>${wps_js._('ip')}</td></tr>`;
        html += `<tr><td><input name="ip" value="${(wps_js.getLinkParams('ip') != null ? decodeURIComponent(wps_js.getLinkParams('ip')) : ``)}" class="wps-width-100" placeholder='xxx.xxx.xxx.xxx' autocomplete="off"></td></tr>`;



        // Submit Button
        html += `<tr><td></td></tr>`;
        html += `<tr><td><input type="submit" value="${wps_js._('filter')}" class="button-primary"> &nbsp; <span class="filter-loading"></span></td></tr>`;
        html += `</table>`;
        jQuery(tickBox_DIV).html(html);
        wps_js.select2();
    }

    // Add Traffic Trends chart
    if (document.getElementById('trafficTrendsChart')) {
        const data = Wp_Statistics_Visitors_Object.traffic_chart_data;
        wps_js.new_line_chart(data, 'trafficTrendsChart', null);
    }
}

// When close TickBox
//jQuery(window).bind('tb_unload', function () {});
