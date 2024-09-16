if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    // Add Income Visitor Chart

    if (document.getElementById('incomeVisitorChart')) {
        const parentElement = jQuery('#incomeVisitorChart').parent();
        const placeholder = wps_js.rectangle_placeholder();
        parentElement.append(placeholder);

        const searchData = Wp_Statistics_Referrals_Object.search_engine_chart_data;

        if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
            parentElement.html(wps_js.no_results());
            jQuery('.wps-ph-item').remove();

        } else {
            jQuery('.wps-ph-item').remove();
            jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
            parentElement.removeClass('c-chart__wps-skeleton');
            wps_js.new_line_chart(searchData, 'incomeVisitorChart', null)
        }
    }
}
