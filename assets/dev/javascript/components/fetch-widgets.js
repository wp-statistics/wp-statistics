if (wps_js.isset(wps_js.global, 'request_params', 'page') && (wps_js.global.request_params.page === "referrals" || wps_js.global.request_params.page === "content-analytics")) {

    function fetchWidgetData(widgetName, containerSelector) {
        let action = `wp_statistics_get_gsc_${widgetName}`;
        let data = {
            'action': action,
            'wps_nonce': wps_js.global.rest_api_nonce,
            'current_page': wps_js.global.page
        };

        // Success handler
        function successHandler(response) {
            if (response.status === 'success') {
                const container = document.querySelector(containerSelector);
                if (container) {
                    const loadingElement = container.querySelector('.wps-loading');
                    if (loadingElement) {
                        const classesToRemove = [
                            'wps-loading',
                            'wps-skeleton-container__skeleton',
                            'wps-skeleton-container__skeleton--full',
                            'wps-skeleton-container__skeleton--h-150'
                        ];
                        loadingElement.classList.remove(...classesToRemove);
                        loadingElement.innerHTML = response.html;
                    }
                }
            }
        }

        // Error handler
        function errorHandler(xhr, status, error) {
            console.error(`Error fetching ${widgetName} data: ${error}`);
            const container = document.querySelector(containerSelector);
            if (container) {
                const loadingElement = container.querySelector('.wps-loading');
                if (loadingElement) {
                    const classesToRemove = [
                        'wps-loading',
                        'wps-skeleton-container__skeleton',
                        'wps-skeleton-container__skeleton--full',
                        'wps-skeleton-container__skeleton--h-150'
                    ];
                    loadingElement.classList.remove(...classesToRemove);
                    loadingElement.innerHTML = '<div class="wps-error">Failed to load data. Please try again.</div>';
                }
            }
        }

         jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'POST',
            dataType: 'json',
            data: data,
            timeout: 30000,
            success: function ({data, success}) {
                successHandler()
            },
            error: function (xhr, status, error) {
                errorHandler()
            }
        });
    }

    if (wps_js.global.request_params.page === "referrals" && wps_js.global.request_params.tab === "google-search") {

        const googleSearchWidgets = [
            {name: 'tab_last_update', selector: '.wps-last-updated'},
            {name: 'clicks', selector: '#wps-clicks-widget'},
            {name: 'search_traffic', selector: '#wps-search-traffic-widget'},
            {name: 'impressions', selector: '#wps-impressions-widget'},
            {name: 'search_query_data', selector: '#wps-top-search-queries-widget'},
            {name: 'google-visitors', selector: '#wps-google-visitors-widget'},
            {name: 'top_content', selector: '#wps-top-contents-widget'},
            {name: 'top_countries', selector: '#wps-top-countries-widget'},
            {name: 'latest_visitors', selector: '#wps-latest-visitors-widget'},
        ];
        googleSearchWidgets.forEach(widget => {
            fetchWidgetData(widget.name, widget.selector);
        });
    }
    if (wps_js.global.request_params.page === "content-analytics" && wps_js.global.request_params.type === "single") {
        const widgets = [
            {name: 'search_traffic', selector: '#wps-search-traffic-widget'},
            {name: 'search_query_data', selector: '#wps-top-search-queries-widget'},
        ];
        widgets.forEach(widget => {
            fetchWidgetData(widget.name, widget.selector);
        });
    }
}