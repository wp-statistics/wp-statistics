/**
 * Configuration for horizontal bar charts
 */
let data;
let skipConfigs = false;

try {
    if (typeof Wp_Statistics_Visitors_Object !== 'undefined' && Wp_Statistics_Visitors_Object) {
        data = Wp_Statistics_Visitors_Object;
    } else {
        skipConfigs = true;
    }
} catch (error) {
    skipConfigs = true;
}

const barChartConfigs = skipConfigs ? [] : [
    {
        elementId: 'visitors-logged-in-users',
        data: data?.logged_in_users,
    },
    {
        elementId: 'visitors-device-categories',
        data: data?.devices,
    },
    {
        elementId: 'visitors-top-countries',
        data: data?.countries,
    },
    {
        elementId: 'visitors-top-browsers',
        data: data?.browsers,
    },
];

/**
 * Configuration for line charts
 */
const trafficChart = skipConfigs ? null : data?.traffic;

const lineChartConfigs = skipConfigs ? [] : [
    {
        elementId: 'trafficTrendsChart',
        dataSource: () => Wp_Statistics_Visitors_Object?.traffic_chart_data,
    },
    {
        elementId: 'trafficChart',
        dataSource: () => trafficChart,
    },
    {
        elementId: 'LoggedInUsersChart',
        dataSource: () => Wp_Statistics_Visitors_Object?.logged_in_chart_data,
    },
];

/**
 * Configuration for vector map
 */
const vectorMapConfig = skipConfigs ? {} : {
    elementId: 'wp-statistics-visitors-map',
    data: data?.map,
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
        return;
    }

    if (!data?.data?.length) {
        jQuery(element).parent().html(wps_js.no_results());
        return;
    }

    try {
        wps_js.horizontal_bar(elementId, data.labels, data.data, data.icons);
    } catch (error) {
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
    if (typeof Wp_Statistics_Visitors_Object === 'undefined' || !Wp_Statistics_Visitors_Object) {
        return;
    }

    if (!wps_js.isset(wps_js.global, 'request_params', 'page') || wps_js.global.request_params.page !== "visitors") {
        return;
    }

    barChartConfigs.forEach(renderBarChart);
    lineChartConfigs.forEach(renderLineChart);
    renderVectorMap(vectorMapConfig);
};

initializeVisualizations();