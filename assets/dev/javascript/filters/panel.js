/**
 * FilterPanel Constructor
 * Handles the UI and logic for standalone filters (not in modal).
 * @param {Object} options - Custom settings for the panel.
 */
function FilterPanel(options) {
    const defaults = {
        fields: {}, // List of separated filters
    };

    this.settings = { ...defaults, ...options };
    this.sourceCache = {};
    this.memoryCache = null;
    this.init();
}

/**
 * Initializes the standalone filter panel.
 */
FilterPanel.prototype.init = function () {
    if (!this.settings.fields || Object.keys(this.settings.fields).length === 0) {
        console.warn('No filters available for the panel.');
        return;
    }

    setTimeout(() => {
        this.createContainers();
        this.fetchFilterOptions();
    }, 0);
};

/**
 * Dynamically creates containers for each separated filter based on its type.
 */
FilterPanel.prototype.createContainers = function () {
    Object.entries(this.settings.fields).forEach(([key, filter]) => {
        const attributes = filter?.attributes || {},
            type = attributes['data-type'],
            source = attributes['data-source'],
            isSearchable = attributes['data-searchable'];

        if (!type) {
            console.warn(`Skipping filter ${key} - Missing data-type.`);
            return;
        }

        const containerId = `wps-filter-${type}`;
        let container = document.querySelector(`#${containerId}`);

        if (!container) {
            return;
        }

        container.classList.add('loading');

        filter.containerSelector = `#${containerId}`;

        if (source && !this.sourceCache[source]&& !isSearchable) {
            this.sourceCache[source] = true;
        }
    });
};

/**
 * Fetches filter options via AJAX for standalone filters.
 */
FilterPanel.prototype.fetchFilterOptions = function () {
    if (this.memoryCache) {
        this.renderFilters(this.memoryCache);
        return;
    }

    const self = this;

    const queryString = window.location.search;

    let params = {
        wps_nonce: wps_js.global.rest_api_nonce,
        action: 'wp_statistics_get_filters',
        filters: Object.keys(this.sourceCache),
        queryString: queryString,
    };

    params = Object.assign(params, wps_js.global.request_params);

    jQuery.ajax({
        url: wps_js.global.admin_url + 'admin-ajax.php',
        type: 'POST',
        dataType: 'json',
        data: params,
        timeout: 30000,
        success: function (data) {
            if (data) {
                self.memoryCache = data;
                this.renderFilters(data);
            }
        }.bind(this),
        error: function () {
            console.error("Error fetching filter data.");
        },
        complete: function () {
            Object.values(this.settings.fields).forEach(function (filter) {
                if (filter.containerSelector) {
                    var container = document.querySelector(filter.containerSelector);
                    if (container) {
                        container.classList.remove('loading');
                    }
                }
            });
        }.bind(this)
    });
}

/**
 * Renders the standalone filters dynamically based on AJAX response.
 * @param {Object} data - The filter data from AJAX.
 */
FilterPanel.prototype.renderFilters = function (data) {
    Object.entries(this.settings.fields).forEach(function ([key, filter]) {
        const attributes = filter?.attributes || {};
        const type = attributes['data-type'];
        const source = attributes['data-source'];
        const containerSelector = filter.containerSelector;

        if (!type || !containerSelector) {
            return;
        }

        const filterData = data[source] || {};
        const generator = new FilterGenerator(containerSelector);

        switch (filter.type) {
            case 'select':
                generator.createSelect(filter, 'dropdown-content');
                break;
            case 'dropdown':
                generator.createDropdown(filter, filterData);
                break;
        }
    });
};