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

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'POST',
            dataType: 'json',
            data: params,
            timeout: 30000,
            beforeSend: function() {
                searchConsoleSite.classList.add('wps-loading');
            },
            success: function ({data, success}) {
                if (success && data) {
                    searchConsoleSite.innerHTML = '';

                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = Object.keys(item)[0];
                        option.textContent = item[Object.keys(item)[0]];
                        searchConsoleSite.appendChild(option);
                    });
                } else {
                    notice.innerHTML = `<p>${data}</p>`;
                    document.querySelector("#marketing-settings").prepend(notice);
                    const topOffset = document.querySelector('#marketing-settings').getBoundingClientRect().top + window.scrollY;
                    window.scrollTo({
                        top: topOffset,
                        behavior: "smooth"
                    });
                 }
             },
            error: function (xhr, status, error) {
                console.log(error);
            },complete : function (){
                searchConsoleSite.classList.remove('wps-loading')
            }
        });
    }

}