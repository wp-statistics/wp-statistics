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

    // Add active class when user clicks on group input
    jQuery('.wp-statistics-settings .wps-settingsPageFlex .input-group').click(function (e) {
        e.preventDefault();
        $(e.currentTarget).addClass('active')
        $(e.currentTarget).children('input').focus();
    });

    // Remove active class when group input loses focus
    jQuery('.wp-statistics-settings .wps-settingsPageFlex .input-group input').blur(function (e) {
        e.preventDefault();
        $(e.target).parent().removeClass('active');
    });
}