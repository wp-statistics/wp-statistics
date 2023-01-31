if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "pages") {

    // Check has Custom Page
    if (wps_js.isset(wps_js.global, 'request_params', 'ID') && wps_js.isset(wps_js.global, 'request_params', 'type')) {

        // Create Params
        let params;

        // Check Days ago or Between
        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            params = {'from': wps_js.global.request_params.from, 'to': wps_js.global.request_params.to};
        } else {
            params = {'ago': 30};
        }

        // Add Page ID and type
        params = Object.assign(params, {
            'ID': wps_js.global.request_params.ID,
            'type': wps_js.global.request_params.type
        });

        // Run MetaBox
        wps_js.run_meta_box('pages-chart', params, false);

        // Set Select2 For List
        if (wps_js.exist_tag("form#wp-statistics-select-pages")) {
            wps_js.select2();
        }

        // Submit Change Page Select Form
        jQuery(document).on('change', 'select[name=ID]', function () {
            jQuery("span.submit-form").html(wps_js._('please_wait'));
            jQuery(this).closest('form').trigger('submit');
        });

        // Display Top Browsers Chart
        if (wps_js.exist_tag("div[data-top-browsers-chart='true']")) {
            let browsersEl = jQuery("div[data-top-browsers-chart='true']");
            // Get Names
            let browserNames = jQuery(browsersEl).data('browsers-names');
            // Get Values
            let browserValues = jQuery(browsersEl).data('browsers-values');
            // Get Background Color
            let backgroundColor = [];
            let color;
            for (let i = 0; i <= 10; i++) {
                color = wps_js.random_color(i);
                backgroundColor.push('rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.4)');
            }
            // Prepare Data
            let data = [{
                label: wps_js._('browsers'),
                data: browserValues,
                backgroundColor: backgroundColor
            }];
            // Add html after browsersEl
            jQuery(browsersEl).after('<div class="o-wrap"><div class="c-chart c-chart--limited-height"><canvas id="' + wps_js.chart_id('browsers') + '" height="220"></canvas></div></div>');
            // Remove browsersEl
            jQuery(browsersEl).remove();
            // Check Data
            if(browserNames.length && browserValues.length) {
                // Show Chart
                wps_js.pie_chart(wps_js.chart_id('browsers'), browserNames, data);
            } else {
                jQuery('#wp-statistics-browsers-widget').empty().html(wps_js.no_meta_box_data());
            }
        }

    } else {

        // Create Params
        let params = {};

        // Check Pagination
        if (wps_js.isset(wps_js.global, 'request_params', 'pagination-page')) {
            params['paged'] = wps_js.global.request_params['pagination-page'];
        }

        // Check Days ago or Between
        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            params['from'] = wps_js.global.request_params.from;
            params['to'] = wps_js.global.request_params.to;
        } else {
            params['ago'] = 30;
        }

        // Run Pages list MetaBox
        //wps_js.run_meta_box('pages', params, false);

        // Run Top Pages chart Meta Box
        wps_js.run_meta_box('top-pages-chart', params, false);
    }
}