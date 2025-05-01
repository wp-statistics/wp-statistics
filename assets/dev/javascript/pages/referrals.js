if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    const placeholder = wps_js.rectangle_placeholder();
    function renderChart(chartId, searchData) {
        const chartElement = document.getElementById(chartId);
         if (chartElement) {
            const parentElement = jQuery(`#${chartId}`).parent();
            parentElement.append(placeholder);

            if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
                parentElement.html(wps_js.no_results());
                jQuery('.wps-ph-item').remove();
            } else {
                const chart = wps_js.new_line_chart(searchData, chartId, {
                    animation: {
                        onComplete: () => {
                            jQuery('.wps-ph-item').remove();
                            jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
                            parentElement.removeClass('c-chart__wps-skeleton');
                        }
                    }
                });
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
    }

    // Charts with sample data

    const topSearchEngine = {
        "data": {
            "labels": [
                {
                    "formatted_date": "Apr 1",
                    "date": "2025-04-01",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 2",
                    "date": "2025-04-02",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 3",
                    "date": "2025-04-03",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 4",
                    "date": "2025-04-04",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 5",
                    "date": "2025-04-05",
                    "day": "Saturday"
                }
            ],
            "datasets": [
                {
                    "label": "Total",
                    "data": [
                        6,
                        8,
                        12,
                        0,
                        0
                    ],
                    "slug": "total"
                },
                {
                    "label": "Google",
                    "data": [
                        6,
                        0,
                        0,
                        0,
                        0,

                    ],
                    "slug": null
                }
            ]
        },
        "previousData": {
            "labels": [
                {
                    "formatted_date": "Mar 1",
                    "date": "2025-03-01",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 2",
                    "date": "2025-03-02",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 3",
                    "date": "2025-03-03",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 4",
                    "date": "2025-03-04",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 5",
                    "date": "2025-03-05",
                    "day": "Wednesday"
                }
            ],
            "datasets": [
                {
                    "label": "Total",
                    "data": [
                        34,
                        40,
                        71,
                        75,
                        77
                    ]
                }
            ]
        }
    };
    renderChart('referral-search-engines-chart', topSearchEngine);

    const visitorChart = {
        "data": {
            "labels": [
                {
                    "formatted_date": "Apr 1",
                    "date": "2025-04-01",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 2",
                    "date": "2025-04-02",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 3",
                    "date": "2025-04-03",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 4",
                    "date": "2025-04-04",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 5",
                    "date": "2025-04-05",
                    "day": "Saturday"
                }
            ],
            "datasets": [
                {
                    "label": "Visitors",
                    "data": [
                        6,
                        0,
                        0,
                        0,
                        0
                    ],
                    "slug": "visitors"
                },
                {
                    "label": "Views",
                    "data": [
                        6,
                        0,
                        0,
                        0,
                        0,

                    ],
                    "slug": "views"
                }
            ]
        },
        "previousData": {
            "labels": [
                {
                    "formatted_date": "Mar 1",
                    "date": "2025-03-01",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 2",
                    "date": "2025-03-02",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 3",
                    "date": "2025-03-03",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 4",
                    "date": "2025-03-04",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 5",
                    "date": "2025-03-05",
                    "day": "Wednesday"
                }
            ],
            "datasets": [
                {
                    "label": "Visitors",
                    "data": [
                        34,
                        40,
                        71,
                        75,
                        77
                    ]
                },
                {
                    "label": "Views",
                    "data": [
                        14,
                        20,
                        81,
                        85,
                        87
                    ]
                }
            ]
        }
    };
    renderChart('referralVisitorChart', visitorChart);

    const topSocialMedia = {
        "data": {
            "labels": [
                {
                    "formatted_date": "Apr 1",
                    "date": "2025-04-01",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Apr 2",
                    "date": "2025-04-02",
                    "day": "Wednesday"
                },
                {
                    "formatted_date": "Apr 3",
                    "date": "2025-04-03",
                    "day": "Thursday"
                },
                {
                    "formatted_date": "Apr 4",
                    "date": "2025-04-04",
                    "day": "Friday"
                },
                {
                    "formatted_date": "Apr 5",
                    "date": "2025-04-05",
                    "day": "Saturday"
                }
            ],
            "datasets": [
                {
                    "label": "Total",
                    "data": [
                        6,
                        0,
                        0,
                        0,
                        0
                    ],
                    "slug": "total"
                },
                {
                    "label": "Google",
                    "data": [
                        6,
                        0,
                        0,
                        0,
                        0,

                    ],
                    "slug": null
                }
            ]
        },
        "previousData": {
            "labels": [
                {
                    "formatted_date": "Mar 1",
                    "date": "2025-03-01",
                    "day": "Saturday"
                },
                {
                    "formatted_date": "Mar 2",
                    "date": "2025-03-02",
                    "day": "Sunday"
                },
                {
                    "formatted_date": "Mar 3",
                    "date": "2025-03-03",
                    "day": "Monday"
                },
                {
                    "formatted_date": "Mar 4",
                    "date": "2025-03-04",
                    "day": "Tuesday"
                },
                {
                    "formatted_date": "Mar 5",
                    "date": "2025-03-05",
                    "day": "Wednesday"
                }
            ],
            "datasets": [
                {
                    "label": "Total",
                    "data": [
                        34,
                        40,
                        71,
                        75,
                        77
                    ]
                }
            ]
        }
    };
    renderChart('referral-social-media-chart', topSocialMedia);

    const topCountries = {
        "data": [
            19
        ],
        "labels": [
            "Germany",
            "Croatia",
        ],
        "icons": [
            "assets/images/flags/de.svg",
            "assets/images/flags/hr.svg"
        ]
    };
    renderHorizontalChart('referral-top-countries', topCountries);

    const topBrowsers = {
        "data": [
            11,
            5,
            2,
            1
        ],
        "labels": [
            "Chrome",
            "Microsoft Edge",
            "Opera",
            "Firefox"
        ],
        "icons": [
            "assets/images/browser/chrome.svg",
            "assets/images/browser/microsoft_edge.svg",
            "assets/images/browser/opera.svg",
            "assets/images/browser/firefox.svg"
        ]
    };
    renderHorizontalChart('referral-top-browser', topBrowsers);

    const deviceType = {
        "data": [
            19
        ],
        "labels": [
            "Desktop"
        ],
        "icons": []
    };
    renderHorizontalChart('referral-device-type', deviceType);
}
