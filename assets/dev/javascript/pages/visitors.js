if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors") {
    if (document.getElementById('trafficTrendsChart')) {
        const data = Wp_Statistics_Visitors_Object.traffic_chart_data;
        wps_js.new_line_chart(data, 'trafficTrendsChart', null);
    }

    if (document.getElementById('LoggedInUsersChart')) {
        const data = Wp_Statistics_Visitors_Object.logged_in_chart_data;
        wps_js.new_line_chart(data, 'LoggedInUsersChart', null);
    }
}
