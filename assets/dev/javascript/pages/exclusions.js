if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "exclusions") {

    function renderChart(chartId, data) {
        const chartElement = document.getElementById(chartId);
        if (chartElement) {
            const parentElement = jQuery(`#${chartId}`).parent();
            const placeholder = wps_js.rectangle_placeholder();
            parentElement.append(placeholder);
            if (!data?.data?.datasets || data.data.datasets.length === 0) {
                parentElement.html(wps_js.no_results());
                jQuery('.wps-ph-item').remove();
            } else {
                jQuery('.wps-ph-item').remove();
                jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
                parentElement.removeClass('c-chart__wps-skeleton');
                wps_js.new_line_chart(data, chartId, null);
            }
        }
    }
    const data = Wp_Statistics_Exclusions_Object.exclusions_chart_data;
    renderChart('exclusionsChart', data);

}