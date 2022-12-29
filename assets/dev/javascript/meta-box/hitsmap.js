wps_js.hitsmap_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {
        return `<div class="o-wrap"><div id="wp-statistics-visitors-map"></div></div>`;
    },

    meta_box_init: function (args = []) {
        let pin = Array();

        // Prepare Country Pin
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
        }

        // Load Jquery Map
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

        const widgetWrapper = jQuery("#wp-statistics-hitsmap-widget");
        const sideSortable = jQuery("#side-sortables");
        const normalSortable = jQuery("#normal-sortables");
        const observerConfig = {attributes: false, childList: true, characterData: false, subtree: true};

        const observer = new MutationObserver(function (mutations) {
            if (sideSortable.find(widgetWrapper).length || normalSortable.find(widgetWrapper).length) {
                window.dispatchEvent(new Event('resize'));
            }
        });

        observer.observe(document.getElementById('side-sortables'), observerConfig);
        observer.observe(document.getElementById('normal-sortables'), observerConfig);
    }
};