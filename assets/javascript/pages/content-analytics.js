if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "content-analytics") {

    const wpStatisticsContentAnalytics = {
        data: [],

        init: function () {
            if (typeof Wp_Statistics_Content_Analytics_Object == "undefined") {
                console.log('Variable Wp_Statistics_Content_Analytics_Object not found.');
                return;
            }

            this.data = Wp_Statistics_Content_Analytics_Object;
            this.generateCharts();
            const toggleContentButton = document.querySelector('.js-toggle-content-tags');
            if (toggleContentButton) {
                toggleContentButton.addEventListener('click', function (event) {
                    event.preventDefault(); // Prevent the default link behavior

                    const extraTags = document.querySelectorAll('.extra-item');
                    extraTags.forEach(tag => tag.classList.toggle('show'));
                    toggleContentButton.classList.toggle('toggled');
                    if (toggleContentButton.classList.contains('toggled')) {
                        toggleContentButton.textContent = wps_js._('show_less');
                    } else {
                        toggleContentButton.textContent = wps_js._('show_more');
                    }
                });
            }
        },
        generateCharts: function () {
            if (document.getElementById('performance-chart')) this.generatePerformanceChart();
            if (document.getElementById('performance-chart-single')) this.generatePerformanceChartSingle();
            if (document.getElementById('content_operating_systems')) this.generateOperatingSystemChart();
            if (document.getElementById('content_browsers')) this.generateBrowsersChartData();
            if (document.getElementById('content_device_models')) this.generateDeviceModelsChart();
            if (document.getElementById('content_device_usage')) this.generateDeviceUsageChart();
            if (document.getElementById('content-search-engines-chart')) this.generateSearchEngineChart();
        },
        generatePerformanceChart: function () {
            const performanceData = this.data.performance_chart_data;
            if (!performanceData.data.datasets || performanceData.data.datasets.length == 0) {
                jQuery('#performance-chart').parent().html(wps_js.no_results());
            } else {
                wps_js.new_line_chart(performanceData, 'performance-chart', null, 'performance');
            }
        },
        generateOperatingSystemChart: function () {
            const operatingSystemData = this.data.os_chart_data;
            if (!operatingSystemData.data || operatingSystemData.data.length == 0) {
                jQuery('#content_operating_systems').parent().html(wps_js.no_results());
            } else {
                wps_js.horizontal_bar('content_operating_systems', operatingSystemData.labels, operatingSystemData.data, operatingSystemData.icons);
            }
        },
        generateBrowsersChartData: function () {
            const browsersData = this.data.browser_chart_data;
            if (!browsersData.data || browsersData.data.length == 0) {
                jQuery('#content_browsers').parent().html(wps_js.no_results());

            } else {
                wps_js.horizontal_bar('content_browsers', browsersData.labels, browsersData.data, browsersData.icons);
            }
        },
        generateDeviceModelsChart: function () {
            const deviceModelData = this.data.model_chart_data;
            if (!deviceModelData.data || deviceModelData.data.length == 0) {
                jQuery('#content_device_models').parent().html(wps_js.no_results());

            } else {
                wps_js.horizontal_bar('content_device_models', deviceModelData.labels, deviceModelData.data, null);
            }
        },
        generateDeviceUsageChart: function () {
            const deviceUsageData = this.data.device_chart_data;
            if (!deviceUsageData.data || deviceUsageData.data.length == 0) {
                jQuery('#content_device_usage').parent().html(wps_js.no_results());

            } else {
                wps_js.horizontal_bar('content_device_usage', deviceUsageData.labels, deviceUsageData.data, null);
            }
        },
        generateSearchEngineChart: function () {
            const searchData = this.data.search_engine_chart_data;
            if (!searchData.data.datasets || searchData.data.datasets.length == 0) {
                jQuery('#content-search-engines-chart').parent().html(wps_js.no_results());
                jQuery('.wps-postbox-chart--data').remove();
            } else {
                 wps_js.new_line_chart(searchData, 'content-search-engines-chart')
            }
        },
        generatePerformanceChartSingle: function () {
            const performanceSingleData = this.data.performance_chart_data;
            if (!performanceSingleData.data.datasets || performanceSingleData.data.datasets.length == 0) {
                jQuery('#performance-chart-single').parent().html(wps_js.no_results());
            } else {
                wps_js.new_line_chart(performanceSingleData, 'performance-chart-single', null, 'performance');
            }
        }
    }

    jQuery(document).ready(function () {
        wpStatisticsContentAnalytics.init();
    });
}