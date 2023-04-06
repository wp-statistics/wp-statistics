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
            if (browserNames.length && browserValues.length) {
                // Show Chart
                wps_js.pie_chart(wps_js.chart_id('browsers'), browserNames, data);
            } else {
                jQuery('#wp-statistics-browsers-widget').empty().html(wps_js.no_meta_box_data());
            }
        }

        // Display Top Platforms Chart
        if (wps_js.exist_tag("div[data-top-platforms-chart='true']")) {
            let platformsEl = jQuery("div[data-top-platforms-chart='true']");
            // Get Names
            let platformsNames = jQuery(platformsEl).data('platforms-names');
            // Get Values
            let platformsValues = jQuery(platformsEl).data('platforms-values');
            // Get Background Color
            let backgroundColor = [];
            let color;
            for (let i = 0; i <= 10; i++) {
                color = wps_js.random_color(i);
                backgroundColor.push('rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.4)');
            }
            // Prepare Data
            let data = [{
                label: wps_js._('platforms'),
                data: platformsValues,
                backgroundColor: backgroundColor
            }];
            // Add html after browsersEl
            jQuery(platformsEl).after('<div class="o-wrap"><div class="c-chart c-chart--limited-height"><canvas id="' + wps_js.chart_id('platforms') + '" height="220"></canvas></div></div>');
            // Remove browsersEl
            jQuery(platformsEl).remove();
            // Check Data
            if (platformsNames.length && platformsValues.length) {
                // Show Chart
                wps_js.pie_chart(wps_js.chart_id('platforms'), platformsNames, data);
            } else {
                jQuery('#wp-statistics-platforms-widget').empty().html(wps_js.no_meta_box_data());
            }
        }

        // Display Visitors Map
        if (wps_js.exist_tag("div[data-visitors-map='true']")) {
            let mapEl = jQuery("div[data-visitors-map='true']");
            // Get Response
            let args = jQuery(mapEl).data('response');
            // Add html after mapEl
            jQuery(mapEl).after('<div class="o-wrap"><div id="wp-statistics-visitors-map"></div></div>');
            // Remove mapEl
            jQuery(mapEl).remove();
            // Prepare Data
            let pin = Array();
            if (args.hasOwnProperty('country')) {
                Object.keys(args['country']).forEach(function (key) {
                    let t = `<div class='map-html-marker'><div class="map-country-header"><img src='${args['country'][key]['flag']}' alt="${args['country'][key]['name']}" title='${args['country'][key]['name']}' class='log-tools wps-flag'/> ${args['country'][key]['name']} (${args['total_country'][key]})</div>`;

                    // Get List visitors
                    Object.keys(args['visitor'][key]).forEach(function (visitor_id) {
                        t += `<p><img src='${args['visitor'][key][visitor_id]['browser']['logo']}' alt="${args['visitor'][key][visitor_id]['browser']['name']}" class='wps-flag log-tools' title='${args['visitor'][key][visitor_id]['browser']['name']}'/> ${args['visitor'][key][visitor_id]['ip']} ` + (["Unknown", "(Unknown)"].includes(args['visitor'][key][visitor_id]['city']) ? '' : '- ' + args['visitor'][key][visitor_id]['city']) + `</p>`;
                    });
                    t += `</div>`;

                    pin[key] = t;
                });

                jQuery('#wp-statistics-visitors-map').vectorMap({
                    map: 'world_en',
                    backgroundColor: '#fff',
                    borderColor: '#7e7e7e',
                    borderOpacity: 0.60,
                    color: '#e6e5e2',
                    selectedColor: '#9DA3F7',
                    hoverColor: '#404BF2',
                    colors: args['color'],
                    onLabelShow: function (element, label, code) {
                        if (pin[code] !== undefined) {
                            label.html(pin[code]);
                        } else {
                            label.html(label.html() + ' [0]<hr />');
                        }
                    },
                });
            } else {
                jQuery('#wp-statistics-visitors-map-widget').empty().html(wps_js.no_meta_box_data());
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

        // Check Post Type
        if (wps_js.isset(wps_js.global, 'request_params', 'type')) {
            params['type'] = wps_js.global.request_params['type'];
        }

        // Run Pages list MetaBox
        //wps_js.run_meta_box('pages', params, false);

        // Run Top Pages chart Meta Box
        wps_js.run_meta_box('top-pages-chart', params, false);
    }
}