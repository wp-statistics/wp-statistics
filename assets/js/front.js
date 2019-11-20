var parser = new UAParser();
var getParser = parser.getResult();
var WP_Statistics_http = new XMLHttpRequest();

WP_Statistics_http.open('GET', wps_statistics_object.rest_url + 'wpstatistics/v1/hit?_=' + wps_statistics_object.time + '&_wpnonce=' + wps_statistics_object.wpnonce + '&wp_statistics_hit_rest=yes&browser=' + getParser.browser.name + '&platform=' + getParser.os.name + '&version=' + getParser.os.version + '&referred=' + document.referrer, true);
WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
WP_Statistics_http.send(null);