wps_js.render_global_visitor_distribution = function (response, key){
    let pin = Array();
    let colors = {};
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        if (response.response?.data) {
            let params = response.response.data;
            if (params.hasOwnProperty('codes') && params.codes.length > 0) {
                const countryData = {};
                params.codes.forEach((code, index) => {
                    countryData[code.toLowerCase()] = {
                        label: params.labels[index],
                        flag: params.flags[index],
                        visitors: Number(params.data[index])
                    };
                });

                const maxVisitors = Math.max(...Object.values(countryData).map(country => country.visitors));
                Object.keys(countryData).forEach(code => {
                    const country = countryData[code];

                    const intensity = country.visitors / maxVisitors;
                    // #EBF5FF to #3288D7
                    const r = Math.round(235 - (185 * intensity));  // From 235 to 50
                    const g = Math.round(245 - (109 * intensity));  // From 245 to 136
                    const b = Math.round(255 - (40 * intensity));   // From 255 to 215

                    colors[code] = `rgb(${r}, ${g}, ${b})`;

                    pin[code] = `<div class='map-html-marker'>
                    <div class="map-country-header">
                        <img src='${country.flag}' 
                            alt="${country.label}" 
                            title='${country.label}' 
                            class='log-tools wps-flag'/> 
                            <span>${country.label}  </span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>${country.visitors}</div>
                    </div>
                </div>`;
                });
            }

        }
        jQuery('#wp-statistics-visitors-map').vectorMap({
            map: 'world_en',
            backgroundColor: '#fff',
            borderColor: '#fff',
            borderOpacity: 0.60,
            color: '#e6e6e6',
            selectedColor: '#596773',
            hoverColor: '#596773',


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
    }
    wps_js.initDatePickerHandlers();
};