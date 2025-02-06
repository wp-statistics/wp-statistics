function FilterGenerator(containerSelector) {
    this.container = document.querySelector(containerSelector);

    if (!this.container) {
        throw new Error(`Container ${containerSelector} not found`);
    }
}

/**
 * Checks if an element with the specified name already exists in the container.
 * @param {string} name - The name attribute to check.
 * @returns {boolean} - True if the element exists, false otherwise.
 */
FilterGenerator.prototype.elementExists = function (name) {
    return !!this.container.querySelector(`[name="${name}"]`);
};

/**
 * Creates a wrapper div with global and unique classes.
 * @param {string} name - Field name for unique class.
 * @returns {HTMLElement} - The wrapper `<div>`.
 */
FilterGenerator.prototype.createWrapper = function (name, reset = false) {
    const wrapper = document.createElement('div');

    if (reset) {
        wrapper.classList.add(name);
        return wrapper;
    }

    wrapper.classList.add('wps-filter-item', `wps-filter-${name}`);
    return wrapper;
};

/**
 * Creates a `<span>` label for a field.
 * @param {string} label - Label text.
 * @returns {HTMLElement} - The label `<span>`.
 */
FilterGenerator.prototype.createLabel = function (label) {
    const labelSpan = document.createElement('span');
    labelSpan.className = 'wps-filter-label';
    labelSpan.textContent = label;
    return labelSpan;
};

/**
 * Creates a `<select>` element and appends it to the container.
 * @param {Object} config - Configuration for the select element.
 * @param {string} config.name - Name attribute of the select.
 * @param {string} config.label - Label of the select.
 * @param {string} config.classes - CSS classes for the select.
 * @param {Object} config.attributes - Additional attributes for the select.
 * @param {string} panel - Additional panel condition for the select.
 * @returns {HTMLElement} - The created `<select>` element.
 */
FilterGenerator.prototype.createSelect = function ({ name, label, classes = '', attributes = {}, placeholder = '' }, panel = null) {
    if (this.elementExists(name)) {
        return null;
    }

    let panelId = null;

    if (panel) {
        panelId = attributes['data-type'];
        this.container = document.querySelector(`#wps-filter-${panelId} .wps-dropdown`);
    }

    const wrapper = this.createWrapper(name);
    const labelSpan = this.createLabel(label);
    const select = document.createElement('select');
    select.name = name;
    select.className = classes;

    for (const [key, value] of Object.entries(attributes)) {
        select.setAttribute(key, value);
    }

    wrapper.appendChild(labelSpan);
    wrapper.appendChild(select);

    if (panel) {
        this.createOptions(select, {}, placeholder);
    }

    this.container.appendChild(wrapper);
    this.enableSearchableSelect(select, name, attributes, panel, panelId);

    if (typeof jQuery !== 'undefined' && jQuery.fn.select2 && !panel && !attributes['data-searchable']) {
        jQuery(select).select2({
            width: '100%',
        });
    }

    // Return the select element for further manipulation
    return select;
};

/**
 * Initializes Select2 on a given select element if applicable.
 * Ensures that AJAX-based filtering is enabled and handles errors gracefully.
 * 
 * @param {HTMLElement} select - The select element to enhance with Select2.
 * @param {string} name - The name attribute of the select element.
 * @param {Object} attributes - Additional attributes, including `data-searchable`.
 * @param {boolean} panel - (Optional) Flag indicating if the select element is part of a filter panel.
 * @param {string} panelId - (Optional) The ID of the filter panel container used to scope the dropdown UI.
 */
FilterGenerator.prototype.enableSearchableSelect = function (select, name, attributes, panel, panelId) {
    if (!attributes['data-searchable'] || typeof jQuery === 'undefined' || !jQuery.fn.select2) {
        return;
    }

    let source = name;

    source = attributes['data-type'] || '';

    if (attributes['data-source']) {
        source = attributes['data-source'];
    }

    let panelParams = {};

    if (panel) {
        panelParams = {
            dropdownParent: jQuery(`#wps-filter-${panelId}`),
            dropdownAutoWidth: true,
            dropdownCssClass: 'wps-select2-filter-dropdown'
        }
    }

    const initialize = jQuery(select).select2({
        ajax: {
            delay: 500,
            url: wps_js.global.ajax_url,
            dataType: 'json',
            data: function (params) {
                const query = {
                    wps_nonce: wps_js.global.rest_api_nonce,
                    search: params.term,
                    source: source,
                    action: 'wp_statistics_search_filter',
                    paged: params.page || 1,
                };

                if (wps_js.isset(wps_js.global, 'request_params')) {
                    const requestParams = wps_js.global.request_params;

                    if (requestParams.page) {
                        query.page = requestParams.page;
                    }

                    if (requestParams.author_id) {
                        query.author_id = requestParams.author_id;
                    }

                    if (requestParams.pt) {
                        query.post_type = requestParams.pt;
                    }

                    if (requestParams.pt) {
                        query.post_id = requestParams.pid;
                    }
                }

                return query;
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.text,
                    })),
                };
            },
            error: function (xhr, status, error) {
                console.error('AJAX request error:', status, error);
            },
        },
        minimumInputLength: 1,
        ...panelParams
    });

    if (panel) {
        // Event listeners
        initialize.on('select2:open', () => jQuery(`#wps-filter-${panelId} .wps-dropdown`).addClass('active'));
        initialize.on('select2:close', () => jQuery(`#wps-filter-${panelId} .wps-dropdown`).removeClass('active'));
        jQuery(`#wps-filter-${panelId}`).on('click', () => initialize.select2('open'));

        initialize.on('change', function () {
            const selectedOption = jQuery(`#wps-filter-${panelId}`).find('option:selected');
            const url = selectedOption.val();
            if (url) {
                window.location.href = url;
            }
        });
    }

    const currentReferrer = wps_js.getLinkParams('url');
    if (currentReferrer) {
        const decodedValue = decodeURIComponent(currentReferrer);
        const option = new Option(decodedValue, decodedValue, true, true);
        jQuery(select).append(option).trigger('change');
    }
};

/**
 * Appends `<option>` elements to a given `<select>` element.
 * @param {HTMLElement} select - The `<select>` element.
 * @param {Array} options - Array of option objects `{ value, label, selected }`.
 * @param {string|null} [placeholder=null] - Optional placeholder text to be used as the default option.
 */
FilterGenerator.prototype.createOptions = function (select, options = [], placeholder = null) {
    if (!(select instanceof HTMLSelectElement)) {
        throw new Error('Invalid <select> element provided.');
    }

    if (placeholder) {
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        defaultOption.selected = true;
        select.appendChild(defaultOption);
    }

    if (options.length > 0) {
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.value || '';
            opt.textContent = option.label || '';

            if (placeholder !== null && option.value === placeholder) {
                opt.selected = true;
            }

            select.appendChild(opt);
        });
    }

    // Initialize Select2 for the updated select (if applicable)
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2 && options.length > 0) {
        jQuery(select).select2({
            width: '100%',
        });
    }
};

/**
 * Generates an `<input>` element and appends it to the container.
 * @param {Object} config - Configuration for the input element.
 * @param {string} config.name - Name attribute of the input.
 * @param {string} config.label - Label of the input.
 * @param {string} config.type - Type of the input (e.g., text, number).
 * @param {string} config.classes - CSS classes for the input.
 * @param {string} config.placeholder - Placeholder text for the input.
 * @param {Object} config.attributes - Additional attributes for the input.
 */
FilterGenerator.prototype.createInput = function ({ name, label, type = 'text', classes = '', placeholder = '', attributes = {} }) {
    if (this.elementExists(name)) {
        return null;
    }

    const wrapper = this.createWrapper(name);
    const labelSpan = this.createLabel(label);
    const input = document.createElement('input');
    input.name = name;
    input.type = type;
    input.className = classes;
    input.placeholder = placeholder;

    for (const [key, value] of Object.entries(attributes)) {
        input.setAttribute(key, value);
    }

    wrapper.appendChild(labelSpan);
    wrapper.appendChild(input);
    this.container.appendChild(wrapper);
};

/**
 * Generates a `<button>` element and appends it to the container.
 * @param {Object} config - Configuration for the button element.
 * @param {string} config.label - Button text.
 * @param {string} config.classes - CSS classes for the button.
 * @param {Function} config.onClick - Click event handler for the button.
 * @param {Object} config.attributes - Additional attributes for the button.
 */
FilterGenerator.prototype.createButton = function ({ label, classes = '', onClick, attributes = {} }) {
    const buttonId = `button-${label.replace(/\s+/g, '-').toLowerCase()}`;

    if (this.container.querySelector(`#${buttonId}`)) {
        return;
    }

    const wrapper = this.createWrapper(label.replace(/\s+/g, '-').toLowerCase());
    const button = document.createElement('button');
    button.textContent = label;
    button.className = classes;
    button.id = buttonId;

    for (const [key, value] of Object.entries(attributes)) {
        button.setAttribute(key, value);
    }

    if (typeof onClick === 'function') {
        button.addEventListener('click', onClick);
    }

    wrapper.appendChild(button);
    this.container.appendChild(wrapper);
};

/**
 * Generates a dropdown menu dynamically from the AJAX response.
 * @param {Object} filterConfig - The filter configuration object.
 * @param {Array} filterData - The structured response from PHP (list of post types).
 */
FilterGenerator.prototype.createDropdown = function (filterConfig, filterData) {
    if (!filterConfig || !filterConfig.containerSelector) {
        console.warn("Invalid filter configuration provided for dropdown.");
        return;
    }

    const container = document.querySelector(filterConfig.containerSelector);
    if (!container) {
        console.warn(`Container ${filterConfig.containerSelector} not found.`);
        return;
    }

    const { baseUrl, selectedOptions, lockCustomPostTypes, args } = filterData;

    let dropdownHTML = `
        <div class="wps-dropdown">
            <label class="selectedItemLabel">${filterConfig.label || "Post Type"}: </label>
            <button type="button" class="dropbtn">
                <span>${selectedOptions ? this.getFilterName(filterData) : "All"}</span>
            </button>
            <div class="dropdown-content">
    `;

    if (filterConfig.searchable) {
        dropdownHTML += `<input type="text" class="wps-search-dropdown">`;
    }

    if (!filterConfig.selected) {
        dropdownHTML += `<a href="${baseUrl}" data-index="0" class="${!selectedOptions ? 'selected' : ''}">All</a>`;
    }

    args.forEach((item, key) => {
        let classList = [];
        if (selectedOptions === item.slug) classList.push("selected");
        if (lockCustomPostTypes && item.slug !== "post" && item.slug !== "page") classList.push("disabled");

        if (item.premium) {
            dropdownHTML += `
                <a data-target="wp-statistics-data-plus" title="${item.name}" 
                   class="js-wps-openPremiumModal ${classList.join(" ")}">
                    ${item.name}
                </a>`;
        } else {
            dropdownHTML += `
                <a href="${item.url}" data-index="${key}" title="${item.name}" class="${classList.join(" ")}">
                    ${item.name}
                </a>`;
        }
    });

    dropdownHTML += '</div></div>';
    container.innerHTML = dropdownHTML;

    if (filterConfig.searchable) {
        this.enableSearchableDropdown();
    }
};

/**
 * Utility function to get the selected filter name from filterData.
 * @param {Array} filterData - The structured response from PHP.
 * @returns {string} - The name of the selected post type.
 */
FilterGenerator.prototype.getFilterName = function (filterData) {
    const { selectedOptions, args } = filterData;

    const selectedItem = args.find(item => item.slug === selectedOptions);
    return selectedItem ? selectedItem.name : "All";
};

/**
 * Enables search functionality inside dropdowns when `searchable` is true.
 */
FilterGenerator.prototype.enableSearchableDropdown = function () {
    const searchInputs = document.querySelectorAll(".wps-search-dropdown");

    searchInputs.forEach(input => {
        input.addEventListener("click", (event) => {
            event.stopPropagation();
        });

        input.addEventListener("input", function () {
            const filter = this.value.toLowerCase();
            const items = this.parentElement.querySelectorAll("a");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? "block" : "none";
            });
        });
    });
};