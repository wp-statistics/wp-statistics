if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "settings") {
    // Set Active Tab
    jQuery('#wp-statistics-settings-form ul.tabs li').click(function (e) {
        e.preventDefault();
        let _tab = $(this).attr('data-tab');
        if (typeof (localStorage) != 'undefined') {
            localStorage.setItem("wp-statistics-settings-active-tab", _tab);
        }
    });

    // Set Current Tab
    if (typeof (localStorage) != 'undefined' && wps_js.isset(wps_js.global, 'request_params', 'save_setting') && wps_js.global.request_params.save_setting === "yes") {
        let ActiveTab = localStorage.getItem("wp-statistics-settings-active-tab");
        if (ActiveTab && ActiveTab.length > 0) {
            $('#wp-statistics-settings-form ul.tabs li[data-tab=' + ActiveTab + ']').click();
        }
    }


    const customizationWidgetSelect = document.getElementById('wps_addon_settings[customization][show_wps_about_widget_overview]');
    const widget_title = document.querySelector('label[for="wps_addon_settings[customization][wps_about_widget_title]"]').parentElement.parentElement;
    const widget_content = document.querySelector('label[for="wps_addon_settings[customization][wps_about_widget_content]"]').parentElement.parentElement;

    function toggleCustomizationDivs() {
        if (customizationWidgetSelect.value === 'yes') {
            widget_title.style.display = 'table-row';
            widget_content.style.display = 'table-row';
        } else {
            widget_title.style.display = 'none';
            widget_content.style.display = 'none';
        }
    }

    if (customizationWidgetSelect) {
        toggleCustomizationDivs();
        customizationWidgetSelect.addEventListener('change', toggleCustomizationDivs);
    }

    const searchConsoleSite = document.getElementById('wps_addon_settings[marketing][site]');
    if (searchConsoleSite) {
        let notice = document.createElement("div");
        notice.className = "notice notice-error wp-statistics-notice";

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_get_gsc_sites',
        };


        // Initialize Select2
        if (searchConsoleSite) {
            jQuery(searchConsoleSite).select2({
                placeholder: 'Click to load sites',
                allowClear: true,
                ajax: {
                    url: wps_js.global.admin_url + 'admin-ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            wps_nonce: wps_js.global.rest_api_nonce,
                            action: 'wp_statistics_get_gsc_sites',
                            term: params.term
                        };
                    },
                    processResults: function (response) {
                        if (response && response.success && response.data) {
                            const results = Object.entries(response.data).map(([id, text]) => {
                                return {
                                    id: id,
                                    text: text
                                };
                            });
                            return {results: results};
                        } else {
                            let notice = document.querySelector('.wp-statistics-notice');
                            if (!notice) {
                                notice = document.createElement("div");
                                notice.className = "notice notice-error wp-statistics-notice";
                            }
                            notice.innerHTML = `<p>${response.data || 'Error loading sites'}</p>`;
                            document.querySelector("#marketing-settings").prepend(notice);
                            const topOffset = document.querySelector('#marketing-settings').getBoundingClientRect().top + window.scrollY;
                            window.scrollTo({
                                top: topOffset,
                                behavior: "smooth"
                            });
                            return {results: []};
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        return {results: []};
                    },
                    cache: true
                },
                minimumResultsForSearch: Infinity,
            });

            // Loading states
            jQuery(searchConsoleSite).on('select2:opening', function (e) {
                jQuery(this).data('select2').$dropdown.addClass('wps-loading');
            });

            jQuery(searchConsoleSite).on('select2:open', function (e) {
                jQuery(this).data('select2').$dropdown.removeClass('wps-loading');
            });


        }
    }
}