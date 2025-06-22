/**
 * Configuration for horizontal bar charts
 */
const barChartConfigs = [
    {
        elementId: 'visitors-logged-in-users',
        data: {
            data: [6230, 5462],
            labels: ['User Visitors', 'Anonymous Visitors'],
            icons: [
                '/assets/images/user-visitor.svg',
                '/assets/images/anonymous.svg',
            ],
        },
    },
    {
        elementId: 'visitors-device-categories',
        data: {
            data: [1],
            labels: ['Desktop'],
            icons: ['/assets/images/device/desktop.svg'],
        },
    },
    {
        elementId: 'visitors-top-countries',
        data: {
            data: [6230, 5462, 3134, 2642, 2458],
            labels: ['Germany', 'United States', 'Iran', 'France', 'Netherlands'],
            icons: [
                '/assets/images/flags/de.svg',
                '/assets/images/flags/us.svg',
                '/assets/images/flags/ir.svg',
                '/assets/images/flags/fr.svg',
                '/assets/images/flags/nl.svg',
            ],
        },
    },
    {
        elementId: 'visitors-top-browsers',
        data: {
            data: [1],
            labels: ['Chrome'],
            icons: ['/assets/images/browser/chrome.svg'],
        },
    },
];

/**
 * Configuration for line charts
 */

const referredChart = {
    "data": {
        "labels": [
            {
                "formatted_date": "Jun 21",
                "date": "2025-06-21",
                "day": "Sat"
            }
        ],
        "datasets": [
            {
                "label": "Visitors",
                "data": [
                    0
                ],
                "slug": "visitors"
            },
            {
                "label": "Views",
                "data": [
                    0
                ],
                "slug": "views"
            }
        ]
    },
    "previousData": {
        "labels": [
            {
                "formatted_date": "Jun 20",
                "date": "2025-06-20",
                "day": "Fri"
            }
        ],
        "datasets": [
            {
                "label": "Visitors",
                "data": [
                    0
                ],
                "slug": ""
            },
            {
                "label": "Views",
                "data": [
                    0
                ],
                "slug": ""
            }
        ]
    }
}
const lineChartConfigs = [
    {
        elementId: 'trafficTrendsChart',
        dataSource: () => Wp_Statistics_Visitors_Object?.traffic_chart_data,
    },
    {
        elementId: 'referredVisitors',
        dataSource: () => referredChart
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
    data: {
        labels: [
            'Andorra', 'United Arab Emirates', 'Afghanistan', 'Albania', 'Armenia', 'Angola', 'Argentina',
            'American Samoa', 'Austria', 'Australia', 'Aruba', 'Åland Islands', 'Azerbaijan',
            'Bosnia and Herzegovina', 'Barbados', 'Bangladesh', 'Belgium', 'Burkina Faso', 'Bulgaria',
            'Bahrain', 'Benin', 'Brunei Darussalam', 'Bolivia, Plurinational State of', 'British Antarctic Territory',
            'Brazil', 'Botswana', 'Belarus', 'Belize', 'Canada', 'Congo (the Democratic Republic of the)',
            'Congo', 'Switzerland', 'Côte d’Ivoire', 'Chile', 'Cameroon', 'China', 'Colombia', 'Costa Rica',
            'Cuba', 'Cape Verde', 'Curaçao', 'Cyprus', 'Czech Republic', 'Germany', 'Denmark', 'Dominican Republic',
            'Algeria', 'Ecuador', 'Estonia', 'Egypt', 'Spain', 'Ethiopia', 'Finland', 'Fiji', 'Faroe Islands',
            'France', 'Gabon', 'United Kingdom', 'Grenada', 'Georgia', 'French Guiana', 'Ghana', 'Gibraltar',
            'Gambia', 'Guinea', 'Guadeloupe', 'Equatorial Guinea', 'Greece', 'Guatemala', 'Guam',
            'Guinea-Bissau', 'Hong Kong', 'Honduras', 'Croatia', 'Haiti', 'Hungary', 'Indonesia', 'Ireland',
            'Israel', 'India', 'Iraq', 'Iran', 'Iceland', 'Italy', 'Jersey', 'Jamaica', 'Jordan', 'Japan',
            'Kenya', 'Kyrgyzstan', 'Cambodia', 'Saint Kitts and Nevis', 'Korea (the Republic of)', 'Kuwait',
            'Kazakhstan', 'Lao People\'s Democratic Republic', 'Lebanon', 'Saint Lucia', 'Liechtenstein',
            'Sri Lanka', 'Liberia', 'Lithuania', 'Luxembourg', 'Latvia', 'Libya', 'Morocco', 'Monaco',
            'Moldova (the Republic of)', 'Montenegro', 'Madagascar', 'Macedonia (the former Yugoslav Republic of)',
            'Mali', 'Myanmar', 'Mongolia', 'Macao', 'Martinique', 'Malta', 'Mauritius', 'Maldives', 'Malawi',
            'Mexico', 'Malaysia', 'Mozambique', 'Namibia', 'New Caledonia', 'Niger', 'Nigeria', 'Nicaragua',
            'Netherlands', 'Norway', 'Nepal', 'New Zealand', 'Oman', 'Panama', 'Peru', 'French Polynesia',
            'Papua New Guinea', 'Philippines', 'Pakistan', 'Poland', 'Puerto Rico', 'Palestine, State of',
            'Portugal', 'Paraguay', 'Qatar', 'Réunion', 'Romania', 'Serbia', 'Russian Federation', 'Rwanda',
            'Saudi Arabia', 'Solomon Islands', 'Seychelles', 'Sudan', 'Sweden', 'Singapore', 'Slovenia',
            'Slovakia', 'Sierra Leone', 'Senegal', 'Somalia', 'Suriname', 'El Salvador', 'Syrian Arab Republic',
            'Swaziland', 'Turks and Caicos Islands', 'Togo', 'Thailand', 'Tajikistan', 'Tunisia', 'Tonga',
            'Turkey', 'Trinidad and Tobago', 'Taiwan', 'Tanzania, United Republic of', 'Ukraine', 'Uganda',
            'United States', 'Uruguay', 'Uzbekistan', 'Saint Vincent and the Grenadines',
            'Venezuela, Bolivarian Republic of', 'Virgin Islands (U.S.)', 'Viet Nam', 'Vanuatu', 'Kosovo',
            'Yemen', 'South Africa', 'Zambia', 'Zimbabwe',
        ],
        codes: [
            'AD', 'AE', 'AF', 'AL', 'AM', 'AO', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD',
            'BE', 'BF', 'BG', 'BH', 'BJ', 'BN', 'BO', 'BQ', 'BR', 'BW', 'BY', 'BZ', 'CA', 'CD', 'CG', 'CH',
            'CI', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CW', 'CY', 'CZ', 'DE', 'DK', 'DO', 'DZ', 'EC',
            'EE', 'EG', 'ES', 'ET', 'FI', 'FJ', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GH', 'GI', 'GM',
            'GN', 'GP', 'GQ', 'GR', 'GT', 'GU', 'GW', 'HK', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IN',
            'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KN', 'KR', 'KW', 'KZ', 'LA',
            'LB', 'LC', 'LI', 'LK', 'LR', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MG', 'MK', 'ML',
            'MM', 'MN', 'MO', 'MQ', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI',
            'NL', 'NO', 'NP', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PR', 'PS', 'PT', 'PY',
            'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SI', 'SK', 'SL', 'SN',
            'SO', 'SR', 'SV', 'SY', 'SZ', 'TC', 'TG', 'TH', 'TJ', 'TN', 'TO', 'TR', 'TT', 'TW', 'TZ', 'UA',
            'UG', 'US', 'UY', 'UZ', 'VC', 'VE', 'VI', 'VN', 'VU', 'XK', 'YE', 'ZA', 'ZM', 'ZW',
        ],
        flags: [
            '/assets/images/flags/ad.svg', '/assets/images/flags/ae.svg', '/assets/images/flags/af.svg',
            '/assets/images/flags/al.svg', '/assets/images/flags/am.svg', '/assets/images/flags/ao.svg',
            '/assets/images/flags/ar.svg', '/assets/images/flags/as.svg', '/assets/images/flags/at.svg',
            '/assets/images/flags/au.svg', '/assets/images/flags/aw.svg', '/assets/images/flags/ax.svg',
            '/assets/images/flags/az.svg', '/assets/images/flags/ba.svg', '/assets/images/flags/bb.svg',
            '/assets/images/flags/bd.svg', '/assets/images/flags/be.svg', '/assets/images/flags/bf.svg',
            '/assets/images/flags/bg.svg', '/assets/images/flags/bh.svg', '/assets/images/flags/bj.svg',
            '/assets/images/flags/bn.svg', '/assets/images/flags/bo.svg', '/assets/images/flags/bq.svg',
            '/assets/images/flags/br.svg', '/assets/images/flags/bw.svg', '/assets/images/flags/by.svg',
            '/assets/images/flags/bz.svg', '/assets/images/flags/ca.svg', '/assets/images/flags/cd.svg',
            '/assets/images/flags/cg.svg', '/assets/images/flags/ch.svg', '/assets/images/flags/ci.svg',
            '/assets/images/flags/cl.svg', '/assets/images/flags/cm.svg', '/assets/images/flags/cn.svg',
            '/assets/images/flags/co.svg', '/assets/images/flags/cr.svg', '/assets/images/flags/cu.svg',
            '/assets/images/flags/cv.svg', '/assets/images/flags/cw.svg', '/assets/images/flags/cy.svg',
            '/assets/images/flags/cz.svg', '/assets/images/flags/de.svg', '/assets/images/flags/dk.svg',
            '/assets/images/flags/do.svg', '/assets/images/flags/dz.svg', '/assets/images/flags/ec.svg',
            '/assets/images/flags/ee.svg', '/assets/images/flags/eg.svg', '/assets/images/flags/es.svg',
            '/assets/images/flags/et.svg', '/assets/images/flags/fi.svg', '/assets/images/flags/fj.svg',
            '/assets/images/flags/fo.svg', '/assets/images/flags/fr.svg', '/assets/images/flags/ga.svg',
            '/assets/images/flags/gb.svg', '/assets/images/flags/gd.svg', '/assets/images/flags/ge.svg',
            '/assets/images/flags/gf.svg', '/assets/images/flags/gh.svg', '/assets/images/flags/gi.svg',
            '/assets/images/flags/gm.svg', '/assets/images/flags/gn.svg', '/assets/images/flags/gp.svg',
            '/assets/images/flags/gq.svg', '/assets/images/flags/gr.svg', '/assets/images/flags/gt.svg',
            '/assets/images/flags/gu.svg', '/assets/images/flags/gw.svg', '/assets/images/flags/hk.svg',
            '/assets/images/flags/hn.svg', '/assets/images/flags/hr.svg', '/assets/images/flags/ht.svg',
            '/assets/images/flags/hu.svg', '/assets/images/flags/id.svg', '/assets/images/flags/ie.svg',
            '/assets/images/flags/il.svg', '/assets/images/flags/in.svg', '/assets/images/flags/iq.svg',
            '/assets/images/flags/ir.svg', '/assets/images/flags/is.svg', '/assets/images/flags/it.svg',
            '/assets/images/flags/je.svg', '/assets/images/flags/jm.svg', '/assets/images/flags/jo.svg',
            '/assets/images/flags/jp.svg', '/assets/images/flags/ke.svg', '/assets/images/flags/kg.svg',
            '/assets/images/flags/kh.svg', '/assets/images/flags/kn.svg', '/assets/images/flags/kr.svg',
            '/assets/images/flags/kw.svg', '/assets/images/flags/kz.svg', '/assets/images/flags/la.svg',
            '/assets/images/flags/lb.svg', '/assets/images/flags/lc.svg', '/assets/images/flags/li.svg',
            '/assets/images/flags/lk.svg', '/assets/images/flags/lr.svg', '/assets/images/flags/lt.svg',
            '/assets/images/flags/lu.svg', '/assets/images/flags/lv.svg', '/assets/images/flags/ly.svg',
            '/assets/images/flags/ma.svg', '/assets/images/flags/mc.svg', '/assets/images/flags/md.svg',
            '/assets/images/flags/me.svg', '/assets/images/flags/mg.svg', '/assets/images/flags/mk.svg',
            '/assets/images/flags/ml.svg', '/assets/images/flags/mm.svg', '/assets/images/flags/mn.svg',
            '/assets/images/flags/mo.svg', '/assets/images/flags/mq.svg', '/assets/images/flags/mt.svg',
            '/assets/images/flags/mu.svg', '/assets/images/flags/mv.svg', '/assets/images/flags/mw.svg',
            '/assets/images/flags/mx.svg', '/assets/images/flags/my.svg', '/assets/images/flags/mz.svg',
            '/assets/images/flags/na.svg', '/assets/images/flags/nc.svg', '/assets/images/flags/ne.svg',
            '/assets/images/flags/ng.svg', '/assets/images/flags/ni.svg', '/assets/images/flags/nl.svg',
            '/assets/images/flags/no.svg', '/assets/images/flags/np.svg', '/assets/images/flags/nz.svg',
            '/assets/images/flags/om.svg', '/assets/images/flags/pa.svg', '/assets/images/flags/pe.svg',
            '/assets/images/flags/pf.svg', '/assets/images/flags/pg.svg', '/assets/images/flags/ph.svg',
            '/assets/images/flags/pk.svg', '/assets/images/flags/pl.svg', '/assets/images/flags/pr.svg',
            '/assets/images/flags/ps.svg', '/assets/images/flags/pt.svg', '/assets/images/flags/py.svg',
            '/assets/images/flags/qa.svg', '/assets/images/flags/re.svg', '/assets/images/flags/ro.svg',
            '/assets/images/flags/rs.svg', '/assets/images/flags/ru.svg', '/assets/images/flags/rw.svg',
            '/assets/images/flags/sa.svg', '/assets/images/flags/sb.svg', '/assets/images/flags/sc.svg',
            '/assets/images/flags/sd.svg', '/assets/images/flags/se.svg', '/assets/images/flags/sg.svg',
            '/assets/images/flags/si.svg', '/assets/images/flags/sk.svg', '/assets/images/flags/sl.svg',
            '/assets/images/flags/sn.svg', '/assets/images/flags/so.svg', '/assets/images/flags/sr.svg',
            '/assets/images/flags/sv.svg', '/assets/images/flags/sy.svg', '/assets/images/flags/sz.svg',
            '/assets/images/flags/tc.svg', '/assets/images/flags/tg.svg', '/assets/images/flags/th.svg',
            '/assets/images/flags/tj.svg', '/assets/images/flags/tn.svg', '/assets/images/flags/to.svg',
            '/assets/images/flags/tr.svg', '/assets/images/flags/tt.svg', '/assets/images/flags/tw.svg',
            '/assets/images/flags/tz.svg', '/assets/images/flags/ua.svg', '/assets/images/flags/ug.svg',
            '/assets/images/flags/us.svg', '/assets/images/flags/uy.svg', '/assets/images/flags/uz.svg',
            '/assets/images/flags/vc.svg', '/assets/images/flags/ve.svg', '/assets/images/flags/vi.svg',
            '/assets/images/flags/vn.svg', '/assets/images/flags/vu.svg', '/assets/images/flags/xk.svg',
            '/assets/images/flags/ye.svg', '/assets/images/flags/za.svg', '/assets/images/flags/zm.svg',
            '/assets/images/flags/zw.svg',
        ],
        data: [
            '6', '144', '9', '36', '24', '5', '224', '1', '838', '505', '5', '2', '15', '51', '4', '251',
            '394', '19', '172', '7', '26', '2', '17', '1', '579', '2', '44', '4', '893', '13', '3', '1085',
            '14', '176', '15', '727', '232', '33', '23', '4', '1', '41', '466', '6478', '476', '59', '76',
            '49', '144', '263', '1592', '255', '556', '1', '4', '2731', '1', '1695', '4', '22', '5', '33',
            '2', '1', '4', '8', '3', '340', '33', '1', '2', '357', '13', '155', '8', '385', '616', '428',
            '140', '1070', '51', '3250', '15', '1474', '2', '10', '44', '1688', '75', '7', '33', '1', '467',
            '13', '15', '7', '17', '2', '2', '36', '5', '113', '45', '83', '6', '210', '2', '41', '6', '7',
            '36', '10', '16', '6', '3', '2', '18', '10', '2', '2', '338', '212', '9', '8', '3', '1', '224',
            '10', '2546', '219', '68', '110', '18', '31', '93', '6', '3', '181', '329', '1550', '17', '12',
            '258', '23', '8', '16', '357', '113', '291', '17', '104', '3', '6', '7', '724', '323', '87',
            '189', '3', '10', '10', '6', '13', '10', '4', '1', '17', '342', '2', '67', '1', '478', '7',
            '274', '15', '236', '25', '5675', '36', '23', '1', '55', '1', '469', '3', '10', '17', '235', '8',
            '10',
        ],
        raw_data: [
            '6', '144', '9', '36', '24', '5', '224', '1', '838', '505', '5', '2', '15', '51', '4', '251',
            '394', '19', '172', '7', '26', '2', '17', '1', '579', '2', '44', '4', '893', '13', '3', '1085',
            '14', '176', '15', '727', '232', '33', '23', '4', '1', '41', '466', '6478', '476', '59', '76',
            '49', '144', '263', '1592', '255', '556', '1', '4', '2731', '1', '1695', '4', '22', '5', '33',
            '2', '1', '4', '8', '3', '340', '33', '1', '2', '357', '13', '155', '8', '385', '616', '428',
            '140', '1070', '51', '3250', '15', '1474', '2', '10', '44', '1688', '75', '7', '33', '1', '467',
            '13', '15', '7', '17', '2', '2', '36', '5', '113', '45', '83', '6', '210', '2', '41', '6', '7',
            '36', '10', '16', '6', '3', '2', '18', '10', '2', '2', '338', '212', '9', '8', '3', '1', '224',
            '10', '2546', '219', '68', '110', '18', '31', '93', '6', '3', '181', '329', '1550', '17', '12',
            '258', '23', '8', '16', '357', '113', '291', '17', '104', '3', '6', '7', '724', '323', '87',
            '189', '3', '10', '10', '6', '13', '10', '4', '1', '17', '342', '2', '67', '1', '478', '7',
            '274', '15', '236', '25', '5675', '36', '23', '1', '55', '1', '469', '3', '10', '17', '235', '8',
            '10',
        ],
    },
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
        wps_js.horizontal_bar(elementId, data.labels, data.data, null);
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
