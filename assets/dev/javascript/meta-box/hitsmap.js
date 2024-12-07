wps_js.hitsmap_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {
        return `<div class="o-wrap"><div id="wp-statistics-visitors-map"></div></div>`;
    },

    meta_box_init: function (args = []) {
        let pin = Array();
        let colors = {};
        
        if (args.hasOwnProperty('codes') && args.codes.length > 0) {
            const countryData = {};
            args.codes.forEach((code, index) => {
                countryData[code.toLowerCase()] = {
                    label: args.labels[index],
                    flag: args.flags[index],
                    visitors: Number(args.data[index])
                };
            });
            
            // Maximum number of visitors
            const maxVisitors = Math.max(...Object.values(countryData).map(country => country.visitors));
            
            // Generate colors
            Object.keys(countryData).forEach(code => {
                const country = countryData[code];
                
                const intensity = country.visitors / maxVisitors;
                // Lighter blue (rgb(235, 245, 255)) to Medium blue (rgb(100, 181, 246))
                const r = Math.round(235 - (135 * intensity));  // From 235 to 100
                const g = Math.round(245 - (64 * intensity));   // From 245 to 181
                const b = Math.round(255 - (9 * intensity));    // From 255 to 246
                
                colors[code] = `rgb(${r}, ${g}, ${b})`;
                
                 pin[code] = `<div class='map-html-marker'>
                    <div class="map-country-header">
                        <img src='${country.flag}' 
                            alt="${country.label}" 
                            title='${country.label}' 
                            class='log-tools wps-flag'/> 
                        ${country.label} (${country.visitors})
                    </div>
                </div>`;
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
            colors: colors,
            onLabelShow: function (element, label, code) {
                const lowerCode = code.toLowerCase();
                if (pin[lowerCode] !== undefined) {
                    label.html(pin[lowerCode]);
                } else {
                    label.html(label.html() + ' [0]');
                }
            },
        });

        const widgetWrapper = jQuery("#wp-statistics-hitsmap-widget");
        const sideSortable = jQuery("#side-sortables");
        const normalSortable = jQuery("#normal-sortables");

        const observerConfig = { attributes: false, childList: true, subtree: false };

        const observer = new MutationObserver(function (mutations) {
            if (sideSortable.find(widgetWrapper).length || normalSortable.find(widgetWrapper).length) {
                window.dispatchEvent(new Event('resize'));
                observer.disconnect();
            }
        });

         if (sideSortable.length) {
            observer.observe(document.getElementById('side-sortables'), observerConfig);
        }
        if (normalSortable.length) {
            observer.observe(document.getElementById('normal-sortables'), observerConfig);
        }

    }
};