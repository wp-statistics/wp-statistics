(function () {
    if (!window.wpStatisticsFilters || Object.keys(window.wpStatisticsFilters).length === 0) {
        return;
    }

    let modalFilters = {},
        panelFilters = {};

    Object.entries(window.wpStatisticsFilters).forEach(([key, filter]) => {
        if (filter.panel) {
            panelFilters[key] = filter;
        } else {
            modalFilters[key] = filter;
        }
    });

    new FilterModal({
        modalSelector: '#wps-modal-filter',
        formSelector: '#wp_statistics_visitors_filter_form',
        filterWrapperSelector: '#wps-visitors-filter-form',
        fields: modalFilters,
    });

    if (Object.keys(panelFilters).length > 0) {
        new FilterPanel({
            fields: panelFilters,
        });
    }
})();