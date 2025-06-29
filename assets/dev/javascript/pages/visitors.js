/**
 * Configuration for horizontal bar charts
 */
const data = Wp_Statistics_Visitors_Object;

const barChartConfigs = [
    {
        elementId: 'visitors-logged-in-users',
        data: data.logged_in_users,
    },
    {
        elementId: 'visitors-device-categories',
        data: data.devices,
    },
    {
        elementId: 'visitors-top-countries',
        data: data.countries,
    },
    {
        elementId: 'visitors-top-browsers',
        data: data.browsers,
    },
];

/**
 * Configuration for line charts
 */

const trafficChart = data.traffic;

const lineChartConfigs = [
    {
        elementId: 'trafficTrendsChart',
        dataSource: () => Wp_Statistics_Visitors_Object?.traffic_chart_data,
    },
    {
        elementId: 'trafficChart',
        dataSource: () => trafficChart
    },
    {
        elementId: 'LoggedInUsersChart',
        dataSource: () => Wp_Statistics_Visitors_Object?.logged_in_chart_data,
    },
];

/**
 * Configuration for traffic hour chart
 */
const trafficHourConfig = {
    elementId: 'hourly-usage-chart',
    data: {
        data: {
            labels: Array.from({length: 24}, (_, i) => ({
                hour: `${i % 12 === 0 ? 12 : i % 12} ${i < 12 ? 'AM' : 'PM'}`,
                date: '2025-06-22',
                formatted_date: 'June 22',
            })),
            datasets: [
                {
                    label: 'Visitors',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    slug: 'visitors',
                },
                {
                    label: 'Views',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    slug: 'views',
                },
            ],
        },
        previousData: {
            labels: Array.from({length: 24}, (_, i) => ({
                hour: `${i % 12 === 0 ? 12 : i % 12} ${i < 12 ? 'AM' : 'PM'}`,
                date: '2025-06-21',
                formatted_date: 'June 21',
            })),
            datasets: [
                {
                    label: 'Visitors',
                    data: Array(24).fill(0),
                    slug: '',
                },
                {
                    label: 'Views',
                    data: Array(24).fill(0),
                    slug: '',
                },
            ],
        },
    },
};

/**
 * Configuration for vector map
 */
const vectorMapConfig = {
    elementId: 'wp-statistics-visitors-map',
    data: data.map
};

/**
 * Utility to check if a nested property exists
 * @param {Object} obj - The object to check
 * @param {...string} keys - The nested keys to check
 * @returns {boolean} - Whether the property exists
 */
const isPropertySet = (obj, ...keys) => {
    return keys.reduce((current, key) => current && typeof current === 'object' && key in current, obj);
};

/**
 * Render a horizontal bar chart
 * @param {Object} config - Configuration object with elementId and data
 */
const renderBarChart = ({elementId, data}) => {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn(`Element with ID ${elementId} not found`);
        return;
    }

    if (!data?.data?.length) {
        jQuery(element).parent().html(wps_js.no_results());
        return;
    }

    try {
        wps_js.horizontal_bar(elementId, data.labels, data.data, data.icons);
    } catch (error) {
        console.error(`Error rendering bar chart ${elementId}:`, error);
        jQuery(element).parent().html(wps_js.no_results());
    }
};

/**
 * Render a line chart
 * @param {Object} config - Configuration object with elementId and dataSource
 */
const renderLineChart = ({elementId, dataSource}) => {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    const data = dataSource();
    if (!data) {
        jQuery(element).parent().html(wps_js.no_results());
        return;
    }

    try {
        wps_js.new_line_chart(data, elementId, null);
    } catch (error) {
        jQuery(element).parent().html(wps_js.no_results());
    }
};

/**
 * Render the traffic hour chart
 * @param {Object} config - Configuration object with elementId and data
 */
const renderTrafficHourChart = ({elementId, data}) => {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    try {
        wps_js.TrafficHourCharts(data);
    } catch (error) {
        jQuery(element).parent().html(wps_js.no_results());
    }
};

/**
 * Render the vector map
 * @param {Object} config - Configuration object with elementId and data
 */
const renderVectorMap = ({elementId, data}) => {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    if (!data?.labels?.length || !data?.data?.length) {
        jQuery(element).parent().html(wps_js.no_results());
        return;
    }

    try {
        wps_js.vectorMap(data);
    } catch (error) {
        jQuery(element).parent().html(wps_js.no_results());
    }
};

/**
 * Initialize all visualizations
 */
const initializeVisualizations = () => {
    if (!wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors") {
        return;
    }
    barChartConfigs.forEach(renderBarChart);
    lineChartConfigs.forEach(renderLineChart);
    renderTrafficHourChart(trafficHourConfig);
    renderVectorMap(vectorMapConfig);
};

initializeVisualizations()
