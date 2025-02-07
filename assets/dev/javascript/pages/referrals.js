if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    // Add Income Visitor Chart

    // Helper function to render a chart or display no results
    function renderChart(chartId, searchData) {
        const chartElement = document.getElementById(chartId);

        if (chartElement) {
            const parentElement = jQuery(`#${chartId}`).parent();
            const placeholder = wps_js.rectangle_placeholder();
            parentElement.append(placeholder);

            if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
                parentElement.html(wps_js.no_results());
                jQuery('.wps-ph-item').remove();
            } else {
                jQuery('.wps-ph-item').remove();
                jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
                parentElement.removeClass('c-chart__wps-skeleton');
                wps_js.new_line_chart(searchData, chartId, null);
            }
        }
    }

    if (typeof Wp_Statistics_Referrals_Object !== 'undefined') {
        const sourceCategoriesData = Wp_Statistics_Referrals_Object.source_category_chart_data;
        renderChart('sourceCategoriesChart', sourceCategoriesData);

        const socialMedia = Wp_Statistics_Referrals_Object.social_media_chart_data;
        renderChart('socialMediaChart', socialMedia);

        const incomeVisitorData = Wp_Statistics_Referrals_Object.search_engine_chart_data;
        renderChart('incomeVisitorChart', incomeVisitorData);
    }
}
