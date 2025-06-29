if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    const placeholder = wps_js.rectangle_placeholder();
    function renderChart(chartId, searchData) {
        const chartElement = document.getElementById(chartId);
         if (chartElement) {
             const parentElement = jQuery(`#${chartId}`).parent();
            if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
                parentElement.html(wps_js.no_results());
             } else {
                jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
                parentElement.removeClass('c-chart__wps-skeleton');
                wps_js.new_line_chart(searchData, chartId);
            }
        }
    }

    const renderHorizontalChart=(id,data)=> {
         const chartElement = document.getElementById(id);
        if (chartElement) {
            const parentElement = jQuery(`#${id}`).parent();
             parentElement.find('.wps-ph-item').remove();
            parentElement.append(placeholder);
            if (!data.data || data.data.length === 0) {
                parentElement.html(wps_js.no_results());
            } else {
                jQuery('.wps-ph-item').remove();
                wps_js.horizontal_bar(id, data.labels, data.data, data.icons);
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

        const topCountries = Wp_Statistics_Referrals_Object.countries_chart_data;
        renderHorizontalChart('referral-top-countries', topCountries);

        const topBrowsers = Wp_Statistics_Referrals_Object.browser_chart_data;
        renderHorizontalChart('referral-top-browser', topBrowsers);

        const deviceType = Wp_Statistics_Referrals_Object.device_chart_data;
        renderHorizontalChart('referral-device-type', deviceType);

        const visitorChart = Wp_Statistics_Referrals_Object.traffic_chart_data;
        renderChart('referralVisitorChart', visitorChart);

        const topSocialMedia = Wp_Statistics_Referrals_Object.social_media_chart_data;
        renderChart('referral-social-media-chart', topSocialMedia);

        const topSearchEngine = Wp_Statistics_Referrals_Object.search_engine_chart_data;
        renderChart('referral-search-engines-chart', topSearchEngine);
    }
}
