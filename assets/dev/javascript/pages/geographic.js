if (
    wps_js.isset(wps_js.global, 'request_params', 'page') &&
    wps_js.global.request_params.page === "geographic" &&
    (!wps_js.isset(wps_js.global, 'request_params', 'tab') || wps_js.global.request_params.tab === "overview") &&
    (!wps_js.isset(wps_js.global, 'request_params', 'type') || wps_js.global.request_params.type !== "single-country")
) {
    const wpsVectorMap = document.getElementById('wp-statistics-visitors-map')
    if (wpsVectorMap) {
        const mapData = Wp_Statistics_Geographic_Object.map_chart_data;
        let pin = Array();
        let colors = {};
        if (mapData && mapData?.codes?.length > 0) {
            const geoCountryData = {};
            mapData.codes.forEach((code, index) => {
                const visitors_raw = Number(mapData.raw_data[index]) || 0;
                const visitors = mapData.data[index];
                geoCountryData[code.toLowerCase()] = {
                    label: mapData.labels[index],
                    flag: mapData.flags[index],
                    visitors_raw,
                    visitors
                };
            });

            const maxVisitors = Math.max(1, ...Object.values(geoCountryData).map(country => country.visitors_raw));

            Object.keys(geoCountryData).forEach(code => {
                const country = geoCountryData[code];

                const intensity = country.visitors_raw / maxVisitors;
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
                const countryName = label.text();

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
    }

    const renderHorizontalChart = (id, data) => {
        const chartElement = document.getElementById(id);
        if (chartElement) {
            const parentElement = jQuery(`#${id}`).parent();
            if (!data.data || data.data.length === 0) {
                parentElement.html(wps_js.no_results());
            } else {
                wps_js.horizontal_bar(id, data.labels, data.data, data.icons);
            }
        }
    }

    wps_js.render_wp_statistics_hitsmap_widget()
    const topCountries = Wp_Statistics_Geographic_Object.europe_chart_data;
    renderHorizontalChart('geographic--top-countries', topCountries);

    const visitorsContinent = Wp_Statistics_Geographic_Object.continent_chart_data;
    renderHorizontalChart('geographic-visitors-continent', visitorsContinent);
}