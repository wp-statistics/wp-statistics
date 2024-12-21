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
    const data = {
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
                    "label": "Robot",
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
                    "label": "User Role",
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
                    "label": "Host",
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
     };
    renderChart('exclusionsChart', data);

}