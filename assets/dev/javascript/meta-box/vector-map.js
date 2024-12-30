wps_js.render_wp_statistics_hitsmap_widget = function (response, key) {
    if (!response?.response) return;
    const keyName = key.replace(/_/g, '-');
    const {output, data: params} = response.response;
    let pin = Array();
    let colors = {};
    wps_js.metaBoxInner(keyName).html(output);

    if (params?.codes?.length > 0) {
        const countryData = {};
        params.codes.forEach((code, index) => {
            const visitors = Number(params.data[index]) || 0;
            countryData[code.toLowerCase()] = {
                label: params.labels[index],
                flag: params.flags[index],
                visitors
            };
        });

        const maxVisitors = Math.max(1, ...Object.values(countryData).map(country => country.visitors));

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


    jQuery('#wp-statistics-visitors-map').vectorMap({
        map: 'world_en',
        backgroundColor: '#fff',
        borderColor: '#fff',
        borderOpacity: 0.6,
        color: '#e6e6e6',
        selectedColor: '#596773',
        hoverColor: '#596773',


        colors: colors,
        onLabelShow: function (element, label, code) {
            const lowerCode = code.toLowerCase();
            if (pin[lowerCode]) {
                label.html(pin[lowerCode]);
                return;
            }

            const imageUrl = `${wps_js.global.assets_url}/images/flags/${lowerCode}.svg`;
            const countryName = label.text(); // Extract plain text only

            fetch(imageUrl)
                .then(response => {
                    const flagImage = response.ok
                        ? `<img src='${imageUrl}' alt="${countryName}" title="${countryName}" class='log-tools wps-flag'/>`
                        : '';

                    label.html(`
                <div class='map-html-marker'>
                    <div class="map-country-header">
                        ${flagImage}
                        <span>${countryName}</span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>0</div>
                    </div>
                </div>
            `);
                })
                .catch(error => {
                    console.error('Error fetching the image:', error);

                    label.html(`
                <div class='map-html-marker'>
                    <div class="map-country-header">
                        <span>${countryName}</span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>0</div>
                    </div>
                </div>
            `);
                });
        },
    });

    wps_js.initDatePickerHandlers();
};