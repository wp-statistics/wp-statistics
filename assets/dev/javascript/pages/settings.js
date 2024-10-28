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
        if(customizationWidgetSelect){
            toggleCustomizationDivs();
            customizationWidgetSelect.addEventListener('change', toggleCustomizationDivs);
        }

 }