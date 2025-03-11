/**
 * Get Parameter value
 *
 * @param name
 * @returns {*}
 */
function wp_statistics_getParameterValue(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results) {
        return results[1];
    }
}

/**
 * Enable Tab
 *
 * @param tab_id
 */
function wp_statistics_enableTab(tab_id) {
    jQuery('.wp-statistics-settings .wps-optionsMenu .wps-optionsMenuItem').removeClass('current');
    jQuery('.wp-statistics-settings .tab-content').removeClass('current');

    jQuery("[data-tab=" + tab_id + "]").addClass('current');
    jQuery("#" + tab_id).addClass('current');

    if (jQuery('#wp-statistics-settings-form').length) {
        var click_url = jQuery(location).attr('href') + '&tab=' + tab_id;
        jQuery('#wp-statistics-settings-form').attr('action', click_url).submit();
    }
}

/**
 * Check has setting page
 */
if (jQuery('.wp-statistics-settings').length) {
    var current_tab = wp_statistics_getParameterValue('tab');
    if (current_tab) {
        wp_statistics_enableTab(current_tab);
    }

    jQuery('.wp-statistics-settings .wps-optionsMenu .wps-optionsMenuItem').click(function () {
        var tab_id = jQuery(this).attr('data-tab');
        wp_statistics_enableTab(tab_id);
    });

    const triggerInput = document.querySelector('input[name="user_custom_header_ip_method"]');
    const customHeaderRadio = document.getElementById('custom-header');
    if (triggerInput && customHeaderRadio) {
        customHeaderRadio.addEventListener('change', function() {
            if (customHeaderRadio.checked) {
                triggerInput.focus();
            }
        });

        function checkCustomHeader() {
          customHeaderRadio.checked = true;
        }
        triggerInput.addEventListener('click', checkCustomHeader);
        triggerInput.addEventListener('paste', checkCustomHeader);
        triggerInput.addEventListener('input', checkCustomHeader);
        triggerInput.addEventListener('dragover', checkCustomHeader);
        triggerInput.addEventListener('drop', checkCustomHeader);
    }
}

