/* Start Wp-statistics Admin Js */
var wps_js = {};

var _htmlEscapeMap = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
wps_js.escapeHtml = function (str) {
    if (typeof str !== 'string') return str == null ? '' : String(str);
    return str.replace(/[&<>"']/g, function (c) { return _htmlEscapeMap[c]; });
};

/* Get WP Statistics global Data From Frontend */
wps_js.global = (typeof wps_global != 'undefined') ? wps_global : [];

/* WordPress Global Lang */
wps_js._ = function (key) {
    return (key in this.global.i18n ? this.global.i18n[key] : '');
};

/* Check Active Option */
wps_js.is_active = function (option) {
    return wps_js.global.options[option] === 1;
};