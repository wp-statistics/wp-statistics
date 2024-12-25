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
    const sourceCategoriesData = {
        "data": {
            "labels": [
                {
                    "formatted_date": "Jun 17",
                    "date": "2024-06-17",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jun 18",
                    "date": "2024-06-18",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jun 19",
                    "date": "2024-06-19",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jun 20",
                    "date": "2024-06-20",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jun 21",
                    "date": "2024-06-21",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jun 22",
                    "date": "2024-06-22",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jun 23",
                    "date": "2024-06-23",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jun 24",
                    "date": "2024-06-24",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jun 25",
                    "date": "2024-06-25",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jun 26",
                    "date": "2024-06-26",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jun 27",
                    "date": "2024-06-27",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jun 28",
                    "date": "2024-06-28",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jun 29",
                    "date": "2024-06-29",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jun 30",
                    "date": "2024-06-30",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jul 1",
                    "date": "2024-07-01",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jul 2",
                    "date": "2024-07-02",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jul 3",
                    "date": "2024-07-03",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jul 4",
                    "date": "2024-07-04",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jul 5",
                    "date": "2024-07-05",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jul 6",
                    "date": "2024-07-06",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jul 7",
                    "date": "2024-07-07",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jul 8",
                    "date": "2024-07-08",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jul 9",
                    "date": "2024-07-09",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jul 10",
                    "date": "2024-07-10",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jul 11",
                    "date": "2024-07-11",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jul 12",
                    "date": "2024-07-12",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jul 13",
                    "date": "2024-07-13",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jul 14",
                    "date": "2024-07-14",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jul 15",
                    "date": "2024-07-15",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jul 16",
                    "date": "2024-07-16",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jul 17",
                    "date": "2024-07-17",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jul 18",
                    "date": "2024-07-18",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jul 19",
                    "date": "2024-07-19",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jul 20",
                    "date": "2024-07-20",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jul 21",
                    "date": "2024-07-21",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jul 22",
                    "date": "2024-07-22",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jul 23",
                    "date": "2024-07-23",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jul 24",
                    "date": "2024-07-24",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jul 25",
                    "date": "2024-07-25",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jul 26",
                    "date": "2024-07-26",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jul 27",
                    "date": "2024-07-27",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jul 28",
                    "date": "2024-07-28",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jul 29",
                    "date": "2024-07-29",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jul 30",
                    "date": "2024-07-30",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jul 31",
                    "date": "2024-07-31",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Aug 1",
                    "date": "2024-08-01",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Aug 2",
                    "date": "2024-08-02",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Aug 3",
                    "date": "2024-08-03",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Aug 4",
                    "date": "2024-08-04",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Aug 5",
                    "date": "2024-08-05",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Aug 6",
                    "date": "2024-08-06",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Aug 7",
                    "date": "2024-08-07",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Aug 8",
                    "date": "2024-08-08",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Aug 9",
                    "date": "2024-08-09",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Aug 10",
                    "date": "2024-08-10",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Aug 11",
                    "date": "2024-08-11",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Aug 12",
                    "date": "2024-08-12",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Aug 13",
                    "date": "2024-08-13",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Aug 14",
                    "date": "2024-08-14",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Aug 15",
                    "date": "2024-08-15",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Aug 16",
                    "date": "2024-08-16",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Aug 17",
                    "date": "2024-08-17",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Aug 18",
                    "date": "2024-08-18",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Aug 19",
                    "date": "2024-08-19",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Aug 20",
                    "date": "2024-08-20",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Aug 21",
                    "date": "2024-08-21",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Aug 22",
                    "date": "2024-08-22",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Aug 23",
                    "date": "2024-08-23",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Aug 24",
                    "date": "2024-08-24",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Aug 25",
                    "date": "2024-08-25",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Aug 26",
                    "date": "2024-08-26",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Aug 27",
                    "date": "2024-08-27",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Aug 28",
                    "date": "2024-08-28",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Aug 29",
                    "date": "2024-08-29",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Aug 30",
                    "date": "2024-08-30",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Aug 31",
                    "date": "2024-08-31",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Sep 1",
                    "date": "2024-09-01",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Sep 2",
                    "date": "2024-09-02",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Sep 3",
                    "date": "2024-09-03",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Sep 4",
                    "date": "2024-09-04",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Sep 5",
                    "date": "2024-09-05",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Sep 6",
                    "date": "2024-09-06",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Sep 7",
                    "date": "2024-09-07",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Sep 8",
                    "date": "2024-09-08",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Sep 9",
                    "date": "2024-09-09",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Sep 10",
                    "date": "2024-09-10",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Sep 11",
                    "date": "2024-09-11",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Sep 12",
                    "date": "2024-09-12",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Sep 13",
                    "date": "2024-09-13",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Sep 14",
                    "date": "2024-09-14",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Sep 15",
                    "date": "2024-09-15",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Sep 16",
                    "date": "2024-09-16",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Sep 17",
                    "date": "2024-09-17",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Sep 18",
                    "date": "2024-09-18",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Sep 19",
                    "date": "2024-09-19",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Sep 20",
                    "date": "2024-09-20",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Sep 21",
                    "date": "2024-09-21",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Sep 22",
                    "date": "2024-09-22",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Sep 23",
                    "date": "2024-09-23",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Sep 24",
                    "date": "2024-09-24",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Sep 25",
                    "date": "2024-09-25",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Sep 26",
                    "date": "2024-09-26",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Sep 27",
                    "date": "2024-09-27",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Sep 28",
                    "date": "2024-09-28",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Sep 29",
                    "date": "2024-09-29",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Sep 30",
                    "date": "2024-09-30",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Oct 1",
                    "date": "2024-10-01",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Oct 2",
                    "date": "2024-10-02",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Oct 3",
                    "date": "2024-10-03",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Oct 4",
                    "date": "2024-10-04",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Oct 5",
                    "date": "2024-10-05",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Oct 6",
                    "date": "2024-10-06",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Oct 7",
                    "date": "2024-10-07",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Oct 8",
                    "date": "2024-10-08",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Oct 9",
                    "date": "2024-10-09",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Oct 10",
                    "date": "2024-10-10",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Oct 11",
                    "date": "2024-10-11",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Oct 12",
                    "date": "2024-10-12",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Oct 13",
                    "date": "2024-10-13",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Oct 14",
                    "date": "2024-10-14",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Oct 15",
                    "date": "2024-10-15",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Oct 16",
                    "date": "2024-10-16",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Oct 17",
                    "date": "2024-10-17",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Oct 18",
                    "date": "2024-10-18",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Oct 19",
                    "date": "2024-10-19",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Oct 20",
                    "date": "2024-10-20",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Oct 21",
                    "date": "2024-10-21",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Oct 22",
                    "date": "2024-10-22",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Oct 23",
                    "date": "2024-10-23",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Oct 24",
                    "date": "2024-10-24",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Oct 25",
                    "date": "2024-10-25",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Oct 26",
                    "date": "2024-10-26",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Oct 27",
                    "date": "2024-10-27",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Oct 28",
                    "date": "2024-10-28",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Oct 29",
                    "date": "2024-10-29",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Oct 30",
                    "date": "2024-10-30",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Oct 31",
                    "date": "2024-10-31",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Nov 1",
                    "date": "2024-11-01",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Nov 2",
                    "date": "2024-11-02",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Nov 3",
                    "date": "2024-11-03",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Nov 4",
                    "date": "2024-11-04",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Nov 5",
                    "date": "2024-11-05",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Nov 6",
                    "date": "2024-11-06",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Nov 7",
                    "date": "2024-11-07",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Nov 8",
                    "date": "2024-11-08",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Nov 9",
                    "date": "2024-11-09",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Nov 10",
                    "date": "2024-11-10",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Nov 11",
                    "date": "2024-11-11",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Nov 12",
                    "date": "2024-11-12",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Nov 13",
                    "date": "2024-11-13",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Nov 14",
                    "date": "2024-11-14",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Nov 15",
                    "date": "2024-11-15",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Nov 16",
                    "date": "2024-11-16",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Nov 17",
                    "date": "2024-11-17",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Nov 18",
                    "date": "2024-11-18",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Nov 19",
                    "date": "2024-11-19",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Nov 20",
                    "date": "2024-11-20",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Nov 21",
                    "date": "2024-11-21",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Nov 22",
                    "date": "2024-11-22",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Nov 23",
                    "date": "2024-11-23",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Nov 24",
                    "date": "2024-11-24",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Nov 25",
                    "date": "2024-11-25",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Nov 26",
                    "date": "2024-11-26",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Nov 27",
                    "date": "2024-11-27",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Nov 28",
                    "date": "2024-11-28",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Nov 29",
                    "date": "2024-11-29",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Nov 30",
                    "date": "2024-11-30",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Dec 1",
                    "date": "2024-12-01",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Dec 2",
                    "date": "2024-12-02",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Dec 3",
                    "date": "2024-12-03",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Dec 4",
                    "date": "2024-12-04",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Dec 5",
                    "date": "2024-12-05",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Dec 6",
                    "date": "2024-12-06",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Dec 7",
                    "date": "2024-12-07",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Dec 8",
                    "date": "2024-12-08",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Dec 9",
                    "date": "2024-12-09",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Dec 10",
                    "date": "2024-12-10",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Dec 11",
                    "date": "2024-12-11",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Dec 12",
                    "date": "2024-12-12",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Dec 13",
                    "date": "2024-12-13",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Dec 14",
                    "date": "2024-12-14",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Dec 15",
                    "date": "2024-12-15",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Dec 16",
                    "date": "2024-12-16",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Dec 17",
                    "date": "2024-12-17",
                    "day": "Tuesday"
                }
            ],
            "datasets": [
                {
                    "label": "Organic Search",
                    "data": [
                        1015,
                        1669,
                        0,
                        0,
                        0,
                        0,
                        0
                    ]
                },
                {
                    "label": "Paid Search",
                    "data": [
                        47,
                        80,
                        0,
                        0,
                        0,
                        0,
                        0
                    ]
                },
                {
                    "label": "Organic Social",
                    "data": [
                        22,
                        48,
                        0,
                        0,
                        0,
                        0,
                        0
                    ]
                },
                {
                    "label": "Total",
                    "data": [
                        1126,
                        1855,
                        0,
                        0,
                        0,
                        0,
                        0
                    ]
                }
            ]
        },
        "previousData": {
            "labels": [
                {
                    "formatted_date": "Dec 17",
                    "date": "2023-12-17",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Dec 18",
                    "date": "2023-12-18",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Dec 19",
                    "date": "2023-12-19",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Dec 20",
                    "date": "2023-12-20",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Dec 21",
                    "date": "2023-12-21",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Dec 22",
                    "date": "2023-12-22",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Dec 23",
                    "date": "2023-12-23",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Dec 24",
                    "date": "2023-12-24",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Dec 25",
                    "date": "2023-12-25",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Dec 26",
                    "date": "2023-12-26",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Dec 27",
                    "date": "2023-12-27",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Dec 28",
                    "date": "2023-12-28",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Dec 29",
                    "date": "2023-12-29",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Dec 30",
                    "date": "2023-12-30",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Dec 31",
                    "date": "2023-12-31",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jan 1",
                    "date": "2024-01-01",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jan 2",
                    "date": "2024-01-02",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jan 3",
                    "date": "2024-01-03",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jan 4",
                    "date": "2024-01-04",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jan 5",
                    "date": "2024-01-05",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jan 6",
                    "date": "2024-01-06",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jan 7",
                    "date": "2024-01-07",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jan 8",
                    "date": "2024-01-08",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jan 9",
                    "date": "2024-01-09",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jan 10",
                    "date": "2024-01-10",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jan 11",
                    "date": "2024-01-11",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jan 12",
                    "date": "2024-01-12",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jan 13",
                    "date": "2024-01-13",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jan 14",
                    "date": "2024-01-14",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jan 15",
                    "date": "2024-01-15",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jan 16",
                    "date": "2024-01-16",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jan 17",
                    "date": "2024-01-17",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jan 18",
                    "date": "2024-01-18",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jan 19",
                    "date": "2024-01-19",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jan 20",
                    "date": "2024-01-20",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jan 21",
                    "date": "2024-01-21",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jan 22",
                    "date": "2024-01-22",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jan 23",
                    "date": "2024-01-23",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jan 24",
                    "date": "2024-01-24",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jan 25",
                    "date": "2024-01-25",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jan 26",
                    "date": "2024-01-26",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jan 27",
                    "date": "2024-01-27",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jan 28",
                    "date": "2024-01-28",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jan 29",
                    "date": "2024-01-29",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jan 30",
                    "date": "2024-01-30",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jan 31",
                    "date": "2024-01-31",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Feb 1",
                    "date": "2024-02-01",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Feb 2",
                    "date": "2024-02-02",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Feb 3",
                    "date": "2024-02-03",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Feb 4",
                    "date": "2024-02-04",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Feb 5",
                    "date": "2024-02-05",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Feb 6",
                    "date": "2024-02-06",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Feb 7",
                    "date": "2024-02-07",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Feb 8",
                    "date": "2024-02-08",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Feb 9",
                    "date": "2024-02-09",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Feb 10",
                    "date": "2024-02-10",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Feb 11",
                    "date": "2024-02-11",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Feb 12",
                    "date": "2024-02-12",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Feb 13",
                    "date": "2024-02-13",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Feb 14",
                    "date": "2024-02-14",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Feb 15",
                    "date": "2024-02-15",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Feb 16",
                    "date": "2024-02-16",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Feb 17",
                    "date": "2024-02-17",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Feb 18",
                    "date": "2024-02-18",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Feb 19",
                    "date": "2024-02-19",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Feb 20",
                    "date": "2024-02-20",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Feb 21",
                    "date": "2024-02-21",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Feb 22",
                    "date": "2024-02-22",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Feb 23",
                    "date": "2024-02-23",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Feb 24",
                    "date": "2024-02-24",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Feb 25",
                    "date": "2024-02-25",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Feb 26",
                    "date": "2024-02-26",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Feb 27",
                    "date": "2024-02-27",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Feb 28",
                    "date": "2024-02-28",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Feb 29",
                    "date": "2024-02-29",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Mar 1",
                    "date": "2024-03-01",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Mar 2",
                    "date": "2024-03-02",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 3",
                    "date": "2024-03-03",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 4",
                    "date": "2024-03-04",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 5",
                    "date": "2024-03-05",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 6",
                    "date": "2024-03-06",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Mar 7",
                    "date": "2024-03-07",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Mar 8",
                    "date": "2024-03-08",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Mar 9",
                    "date": "2024-03-09",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 10",
                    "date": "2024-03-10",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 11",
                    "date": "2024-03-11",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 12",
                    "date": "2024-03-12",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 13",
                    "date": "2024-03-13",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Mar 14",
                    "date": "2024-03-14",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Mar 15",
                    "date": "2024-03-15",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Mar 16",
                    "date": "2024-03-16",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 17",
                    "date": "2024-03-17",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 18",
                    "date": "2024-03-18",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 19",
                    "date": "2024-03-19",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 20",
                    "date": "2024-03-20",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Mar 21",
                    "date": "2024-03-21",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Mar 22",
                    "date": "2024-03-22",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Mar 23",
                    "date": "2024-03-23",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 24",
                    "date": "2024-03-24",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 25",
                    "date": "2024-03-25",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 26",
                    "date": "2024-03-26",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 27",
                    "date": "2024-03-27",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Mar 28",
                    "date": "2024-03-28",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Mar 29",
                    "date": "2024-03-29",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Mar 30",
                    "date": "2024-03-30",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 31",
                    "date": "2024-03-31",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Apr 1",
                    "date": "2024-04-01",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Apr 2",
                    "date": "2024-04-02",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 3",
                    "date": "2024-04-03",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 4",
                    "date": "2024-04-04",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 5",
                    "date": "2024-04-05",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 6",
                    "date": "2024-04-06",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Apr 7",
                    "date": "2024-04-07",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Apr 8",
                    "date": "2024-04-08",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Apr 9",
                    "date": "2024-04-09",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 10",
                    "date": "2024-04-10",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 11",
                    "date": "2024-04-11",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 12",
                    "date": "2024-04-12",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 13",
                    "date": "2024-04-13",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Apr 14",
                    "date": "2024-04-14",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Apr 15",
                    "date": "2024-04-15",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Apr 16",
                    "date": "2024-04-16",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 17",
                    "date": "2024-04-17",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 18",
                    "date": "2024-04-18",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 19",
                    "date": "2024-04-19",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 20",
                    "date": "2024-04-20",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Apr 21",
                    "date": "2024-04-21",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Apr 22",
                    "date": "2024-04-22",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Apr 23",
                    "date": "2024-04-23",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 24",
                    "date": "2024-04-24",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 25",
                    "date": "2024-04-25",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 26",
                    "date": "2024-04-26",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 27",
                    "date": "2024-04-27",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Apr 28",
                    "date": "2024-04-28",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Apr 29",
                    "date": "2024-04-29",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Apr 30",
                    "date": "2024-04-30",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "May 1",
                    "date": "2024-05-01",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "May 2",
                    "date": "2024-05-02",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "May 3",
                    "date": "2024-05-03",
                    "day": "Friday"
                },
                {
                    "formatted_date": "May 4",
                    "date": "2024-05-04",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "May 5",
                    "date": "2024-05-05",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "May 6",
                    "date": "2024-05-06",
                    "day": "Monday"
                },
                {
                    "formatted_date": "May 7",
                    "date": "2024-05-07",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "May 8",
                    "date": "2024-05-08",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "May 9",
                    "date": "2024-05-09",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "May 10",
                    "date": "2024-05-10",
                    "day": "Friday"
                },
                {
                    "formatted_date": "May 11",
                    "date": "2024-05-11",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "May 12",
                    "date": "2024-05-12",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "May 13",
                    "date": "2024-05-13",
                    "day": "Monday"
                },
                {
                    "formatted_date": "May 14",
                    "date": "2024-05-14",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "May 15",
                    "date": "2024-05-15",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "May 16",
                    "date": "2024-05-16",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "May 17",
                    "date": "2024-05-17",
                    "day": "Friday"
                },
                {
                    "formatted_date": "May 18",
                    "date": "2024-05-18",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "May 19",
                    "date": "2024-05-19",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "May 20",
                    "date": "2024-05-20",
                    "day": "Monday"
                },
                {
                    "formatted_date": "May 21",
                    "date": "2024-05-21",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "May 22",
                    "date": "2024-05-22",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "May 23",
                    "date": "2024-05-23",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "May 24",
                    "date": "2024-05-24",
                    "day": "Friday"
                },
                {
                    "formatted_date": "May 25",
                    "date": "2024-05-25",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "May 26",
                    "date": "2024-05-26",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "May 27",
                    "date": "2024-05-27",
                    "day": "Monday"
                },
                {
                    "formatted_date": "May 28",
                    "date": "2024-05-28",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "May 29",
                    "date": "2024-05-29",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "May 30",
                    "date": "2024-05-30",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "May 31",
                    "date": "2024-05-31",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jun 1",
                    "date": "2024-06-01",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jun 2",
                    "date": "2024-06-02",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jun 3",
                    "date": "2024-06-03",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jun 4",
                    "date": "2024-06-04",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jun 5",
                    "date": "2024-06-05",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jun 6",
                    "date": "2024-06-06",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jun 7",
                    "date": "2024-06-07",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jun 8",
                    "date": "2024-06-08",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jun 9",
                    "date": "2024-06-09",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jun 10",
                    "date": "2024-06-10",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Jun 11",
                    "date": "2024-06-11",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Jun 12",
                    "date": "2024-06-12",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Jun 13",
                    "date": "2024-06-13",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Jun 14",
                    "date": "2024-06-14",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Jun 15",
                    "date": "2024-06-15",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Jun 16",
                    "date": "2024-06-16",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Jun 17",
                    "date": "2024-06-17",
                    "day": "Monday"
                }
            ],
            "datasets": [
                {
                    "label": "Total",
                    "data": [
                        0,
                        0,
                        0,
                        0,
                        0,
                        1069,
                        877
                    ]
                }
            ]
        }
    };
    renderChart('sourceCategoriesChart', sourceCategoriesData);

    if (typeof Wp_Statistics_Referrals_Object !== 'undefined') {
        const incomeVisitorData = Wp_Statistics_Referrals_Object.search_engine_chart_data;
        renderChart('incomeVisitorChart', incomeVisitorData);
    }



    // TickBox
    jQuery(document).on('click', "div#referral-filter", function (e) {
        e.preventDefault();

        // Show
        tb_show('', '#TB_inline?&width=430&height=193&inlineId=referral-filter-popup');

        // Add Content
        setTimeout(function () {

            var tickBox_DIV = "#wps-referral-filter-div";
            if (!wps_js.exist_tag(tickBox_DIV + " input[type=submit]")) {

                // Set PlaceHolder
                jQuery(tickBox_DIV).html('<div style="height: 50px;"></div>' + wps_js.line_placeholder(1));
                wps_show_referrals_filter(tickBox_DIV);

            }
        }, 500);

    });

    // submit and disable empty value
    var FORM_ID = '#wps-referrals-filter-form';
    jQuery(document).on('submit', FORM_ID, function () {
        // Remove Empty Parameter
        let forms = {
            'select': ['referrer']
        };
        Object.keys(forms).forEach(function (type) {
            forms[type].forEach((name) => {
                let input = jQuery(FORM_ID + " " + type + "[name=" + name + "]");
                if (input.val().length < 1) {
                    input.prop('disabled', true);
                }
            });
        });

        // Show Loading
        jQuery("span.filter-loading").html(wps_js._('please_wait'));
        // return true;
    });

    // Show Filter form
    function wps_show_referrals_filter(tickBox_DIV) {

        // Create Table
        let html = '<table class="o-table wps-referrals-filter">';

        // Show List Select

        html += `<tr><td class="wps-referrals-filter-title">${wps_js._('search_by_referrer')}</td></tr>`;
        html += `<tr><td><select name="referrer" class="wps-select2   wps-width-100">`;
        html += `<option value=''>${wps_js._('all')}</option>`;
        html += `<option value='test'>test</option>`;
        let current_value = wps_js.getLinkParams('referrer');
        if (current_value != null) {
            html += `<option value='${current_value}'  selected>${current_value}</option>`;
        }

        html += `</select></td></tr>`;
        // Submit Button
        html += `<tr><td></td></tr>`;
        html += `<tr><td><input type="submit" value="${wps_js._('filter')}" class="button-primary"> &nbsp; <span class="filter-loading"></span></td></tr>`;
        html += `</table>`;
        jQuery(tickBox_DIV).html(html);
        jQuery('.wps-select2').select2({
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term,
                        action: 'wp_statistics_search_referrers',
                        paged: params.page || 1
                    };

                    if (wps_js.isset(wps_js.global, 'request_params')) {
                        const requestParams = wps_js.global.request_params;
                        if (requestParams.page) query.page = requestParams.page;
                    }
                    return query;
                },
                error: function (xhr, status, error) {
                    console.error('AJAX request error:', status, error);
                }
            }
        });
    }
}
