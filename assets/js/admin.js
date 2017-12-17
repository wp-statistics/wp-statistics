jQuery(document).ready(function () {
    // Check setting page
    if (jQuery('.wp-statistics-settings').length) {
        var current_tab = getParameterValue('tab');
        if (current_tab) {
            enableTab(current_tab);
        }

        jQuery('.wp-statistics-settings ul.tabs li').click(function () {
            var tab_id = jQuery(this).attr('data-tab');
            enableTab(tab_id);
        });
    }

    // Check about page
    if (jQuery('.wp-statistics-welcome').length) {
        jQuery('.nav-tab-wrapper a').click(function () {
            jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
            jQuery('.tab-content').removeClass('current');

            var tab_id = jQuery(this).attr('data-tab');
            jQuery("[data-tab=" + tab_id + "]").addClass('nav-tab-active');
            jQuery("[data-content=" + tab_id + "]").addClass('current');

            return false;
        });
    }

    /**
     * Get Parameter value
     * @param name
     * @returns {*}
     */
    function getParameterValue(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results) {
            return results[1];
        }
    }

    /**
     * Enable Tab
     * @param tab_id
     */
    function enableTab(tab_id) {
        jQuery('.wp-statistics-settings ul.tabs li').removeClass('current');
        jQuery('.wp-statistics-settings .tab-content').removeClass('current');

        jQuery("[data-tab=" + tab_id + "]").addClass('current');
        jQuery("#" + tab_id).addClass('current');

        if (jQuery('#wp-statistics-settings-form').length) {
            var click_url = jQuery(location).attr('href') + '&tab=' + tab_id;
            jQuery('#wp-statistics-settings-form').attr('action', click_url).submit();
        }
    }
});