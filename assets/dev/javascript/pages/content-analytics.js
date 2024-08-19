if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "content-analytics") {

    const wpStatisticsContentAnalytics = {
        data: [],

        init: function () {
            if (typeof Wp_Statistics_Content_Analytics_Object == "undefined") {
                console.log('Variable Wp_Statistics_Content_Analytics_Object not found.');
                return;
            }

            this.data = Wp_Statistics_Content_Analytics_Object;
            this.generateCharts()
        },
        generateCharts: function () {
            if (document.getElementById('performance-chart')) this.generatePerformanceChart();
            if (document.getElementById('performance-chart-single')) this.generatePerformanceChartSingle();
            if (document.getElementById('content_operating_systems')) this.generateOperatingSystemChart();
            if (document.getElementById('content_browsers')) this.generateBrowsersChartData();
            if (document.getElementById('content_device_models')) this.generateDeviceModelsChart();
            if (document.getElementById('content_device_usage')) this.generateDeviceUsageChart();
            if (document.getElementById('search-engines-chart')) this.generateSearchEngineChart();
        },
        generatePerformanceChart: function () {
            const performanceData = this.data.performance_chart_data;
            wps_js.performance_chart(performanceData ,'performance-chart');
        },

        generateOperatingSystemChart: function () {
            const OperatingSystemData = this.data.os_chart_data;
            //Todo chart Add OS images
            const image_urls = [
                'https://via.placeholder.com/30',
                'https://via.placeholder.com/30',
            ];
            if (!OperatingSystemData.data || OperatingSystemData.data.length == 0) {
                jQuery('#content_operating_systems').parent().html(wps_js.no_results());
                return;
            }else{
                wps_js.horizontal_bar( 'content_operating_systems', OperatingSystemData.labels, OperatingSystemData ,image_urls );
            }
        },
        generateBrowsersChartData: function () {
            const browsersData = this.data.browser_chart_data;
            //Todo chart Add browsers images
            const image_urls = [
                'https://via.placeholder.com/30',
                'https://via.placeholder.com/30',
            ];
            if (!browsersData.data || browsersData.data.length == 0) {
                jQuery('#content_browsers').parent().html(wps_js.no_results());
                return;
            }else{
                wps_js.horizontal_bar( 'content_browsers', browsersData.labels, browsersData ,image_urls );
            }
        },
        generateDeviceModelsChart: function () {
            const deviceModelData = this.data.model_chart_data;
            if (!deviceModelData.data || deviceModelData.data.length == 0) {
                jQuery('#content_device_models').parent().html(wps_js.no_results());
                return;
            }else{
                wps_js.horizontal_bar( 'content_device_models', deviceModelData.labels, deviceModelData ,null );
            }
        },
        generateDeviceUsageChart: function () {
            const deviceUsageData = this.data.device_chart_data;
            if (!deviceUsageData.data || deviceUsageData.data.length == 0) {
                jQuery('#content_device_usage').parent().html(wps_js.no_results());
                return;
            }else{
                wps_js.horizontal_bar( 'content_device_usage', deviceUsageData.labels, deviceUsageData ,null );
            }
        },
        generateSearchEngineChart: function () {
            const searchData = this.data.search_engine_chart_data;

            if (searchData.datasets.length == 0) {
                jQuery('#search-engines-chart').parent().html(wps_js.no_results());
                return;
            }

            const searchEngineColors = [
                'rgba(244, 161, 31, 0.3)',
                'rgba(63, 158, 221, 0.3)',
                'rgba(195, 68, 55, 0.3)',
                'rgba(160, 98, 186, 0.3)',
                'rgba(51, 178, 105, 0.3)',
                'rgba(185, 185, 185, 0.3)'
            ];

            searchData.datasets.forEach((dataset, index) => {
                const color = searchEngineColors[index % searchEngineColors.length];
                dataset.backgroundColor = color;
                dataset.borderColor = color.replace('0.3', '1'); // Adjust alpha for borderColor
                dataset.borderWidth = 2;
                dataset.cubicInterpolationMode = 'monotone';
                dataset.pointRadius = 2;
                dataset.pointHoverRadius = 5;
                dataset.pointHoverBackgroundColor = '#fff';
                dataset.pointHoverBorderWidth = 4;
                dataset.fill = true;
            });
            const searchEngineChart = document.getElementById("search-engines-chart").getContext('2d');
            new Chart(searchEngineChart, {
                type: 'line',
                data: searchData,
                options: {
                    interaction: {
                        intersect: false,
                        mode:'index'
                    },
                    plugins: {
                        tooltip: {
                            caretPadding: 5,
                            boxWidth: 5,
                            usePointStyle: true,
                            boxPadding: 3
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            }
                        }
                    }
                }
            });
        },
        generatePerformanceChartSingle: function () {
            const performanceSingleData = this.data.performance_chart_data;
            wps_js.performance_chart(performanceSingleData ,'performance-chart-single' , true);
        }
    }

    jQuery(document).ready(function () {
        wpStatisticsContentAnalytics.init();
    });
}