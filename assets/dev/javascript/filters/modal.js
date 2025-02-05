/**
 * FilterModal Constructor
 * Handles the filter modal UI and functionality.
 * @param {Object} options - Customizable settings for the modal.
 */
function FilterModal(options) {
    const defaults = {
        modalSelector: '#wps-modal-filter',
        formSelector: '#wp_statistics_visitors_filter_form',
        resetSelector: '.wps-modal-reset-filter',
        filterWrapperSelector: '.wps-modal-filter-form',
        filterContainerSelector: '.filter-select, .filter-input',
        width: 430,
        height: 510,
        onSubmit: null,
        onOpen: null,
        onLoadFilter: null,
        fields: {},
    };

    this.settings = { ...defaults, ...options };
    this.formSelector = this.settings.formSelector;
    this.memoryCache = null; 
    this.filterWrapperSelector = this.settings.filterWrapperSelector;
    this.filterContainerSelector = this.settings.filterContainerSelector;

    this.fieldTypes = Object.fromEntries(
        Object.entries(this.settings.fields)
            .map(([key, field]) => 
                field.name && field.name !== 'page' 
                    ? [field.name, { type: field.type, key }] 
                    : null
            )
            .filter(Boolean)
    );

    this.fieldTypes = Object.keys(this.fieldTypes);

    this.initModal();
}

/**
 * Initializes the modal by creating it dynamically if it does not exist.
 */
FilterModal.prototype.initModal = function () {
    let modalWrapper = document.querySelector('#wps-modal-filter-popup');

    if (!modalWrapper) {
        modalWrapper = document.createElement('div');
        modalWrapper.id = 'wps-modal-filter-popup';
        modalWrapper.style.display = 'none';
        modalWrapper.setAttribute('dir', document.documentElement.dir || 'ltr');

        const form = document.createElement('form');
        form.id = this.settings.formSelector.replace('#', '');
        form.method = 'get';
        form.action = wps_js.global.admin_url + 'admin.php';

        // Container div for dynamically generated fields
        const filterContainer = document.createElement('div');
        filterContainer.id = this.settings.filterWrapperSelector.replace('#', '');
        filterContainer.className = 'wps-modal-filter-form';

        // Append elements
        form.appendChild(filterContainer);
        modalWrapper.appendChild(form);
        document.body.appendChild(modalWrapper);
    }

    this.init();
};

/**
 * Loads filter fields and initializes them based on configuration.
 */
FilterModal.prototype.bindOnLoadFilter = function () {
    jQuery(document).ready(() => {
        if (typeof this.settings.onLoadFilter === 'function') {
            this.settings.onLoadFilter(this.filterWrapperSelector);
            return;
        }

       this.generateFields();
    });
};

/**
 * Binds event handlers for modal interactions.
 */
FilterModal.prototype.init = function () {
    jQuery(document).on('click', this.settings.modalSelector, this.onFilterButtonClick.bind(this));
    jQuery(document).on('submit', this.settings.formSelector, this.onFormSubmit.bind(this));
    jQuery(document).on('click', this.settings.resetSelector, this.onResetFilterClick.bind(this));

    this.bindOnLoadFilter();
};

/**
 * Handles the click event to open the filter modal.
 * @param {Event} e - The event object.
 */
FilterModal.prototype.onFilterButtonClick = function (e) {
    e.preventDefault();

    tb_show(
        wps_js._('filters'),
        `#TB_inline?&width=${this.settings.width}&height=${this.settings.height}&inlineId=wps-modal-filter-popup`
    );

    if (typeof this.settings.onOpen === 'function') {
        this.settings.onOpen();
        this.generateFields();

        // Ensure the form exists before proceeding
        setTimeout(() => {
            this.setSelectedValues();
        } , 300);
        return;
    }

    const dropdowns = jQuery(this.filterWrapperSelector).find('.filter-select');
    const spinner = new Spinner({ container: this.filterWrapperSelector });

    if (this.memoryCache) {
        this.populateVisitorsFilters(this.memoryCache, dropdowns);
    } else {
        this.fetchVisitorsFilters(spinner, dropdowns);
    }

    setTimeout(() => {
        this.setSelectedValues();
    } , 300);
};


FilterModal.prototype.setSelectedValues = function () {
    jQuery(this.filterContainerSelector).each((index, element) => {
        const $element = jQuery(element);
        const fieldName = $element.attr('name');
        const currentValue = wps_js.getLinkParams(fieldName);
        if (currentValue !== null) {
            if ($element.is('select')) {
                setTimeout(() => {
                    this.selectOptionWhenAvailable.bind(this)($element, currentValue);
                }, 100);
            } else if ($element.is('input')) {
                $element.val(decodeURIComponent(currentValue));
            }
        }

        if ($element.is('select')) {
            const target_folder = () => {
                if (fieldName.toLowerCase() === 'agent') {
                    return 'browser';
                } else if (fieldName.toLowerCase() === 'platform') {
                    return 'operating-system';
                } else if (fieldName.toLowerCase() === 'location') {
                    return 'flags';
                } else {
                    return null;
                }
            };
            const folder = target_folder();

            if (folder) {
                // Initialize select2 for the current dropdown
                $element.select2({
                    escapeMarkup: function (markup) {
                        return markup; // Disable HTML escaping
                    },

                    templateSelection: function (idioma) {
                        if (!idioma.id) {
                            return idioma.text; // Return plain text for the "All" option
                        }

                        const imageName = idioma.id.toLowerCase().replace(/ /g, '_');
                        let imagePath = `${wps_js.global.assets_url}/images/${folder}/${imageName}.svg`;

                        // Fallback for missing images
                        if (imageName === 'all') {
                            imagePath = `${wps_js.global.assets_url}/images/flags/000.svg`;
                        }

                        return jQuery(`
                            <span class="wps-modal-filter-icon">
                                <img src="${imagePath}" onerror="this.src='${wps_js.global.assets_url}/images/flags/000.svg'; this.onerror=null;" alt="${idioma.text}" />
                                ${idioma.text}
                            </span>
                        `);
                    },
                    templateResult: function (state) {
                        if (!state.id) {
                            return state.text;
                        }
                        const imageName = state.id.toLowerCase().replace(/ /g, '_');
                        let imagePath = `${wps_js.global.assets_url}/images/${folder}/${imageName}.svg`;

                        // Fallback for missing images
                        if (imageName === 'all') {
                            imagePath = `${wps_js.global.assets_url}/images/flags/000.svg`;
                        }

                        return jQuery(`
                            <span class="wps-modal-filter-icon">
                                <img src="${imagePath}" onerror="this.src='${wps_js.global.assets_url}/images/flags/000.svg'; this.onerror=null;" alt="${state.text}"/>
                                ${state.text}
                            </span>
                        `);
                    }
                });

            } else {
                $element.select2();
            }
        }

    });

    this.toggleResetButton();

    // Call custom data load handler if provided
    if (typeof this.settings.onDataLoad === 'function') {
        this.settings.onDataLoad();
    }
};

/**
 * Attempts to select an option in a dropdown when it becomes available.
 * Retries until maxAttempts is reached or the option is found.
 * @param {Object} element - The jQuery element representing the dropdown.
 * @param {string} currentValue - The value to select.
 * @param {number} [maxAttempts=20] - Maximum number of attempts before giving up.
 * @param {number} [intervalDelay=200] - Delay (in milliseconds) between each attempt.
 */
FilterModal.prototype.selectOptionWhenAvailable = function (element, currentValue, maxAttempts = 20, intervalDelay = 200) {
    let attempts = 0;

    const interval = setInterval(() => {
        const option = element.find(`option[value="${CSS.escape(currentValue)}"]`);

        if (option.length > 0) {
            option.prop('selected', true).trigger('change');
            clearInterval(interval);
        }

        attempts++;
        if (attempts >= maxAttempts) {
            clearInterval(interval);
        }
    }, intervalDelay);
};

/**
 * Populates dropdown filters dynamically with received data.
 * @param {Object} data - The filter data.
 * @param {Object} dropdowns - The dropdown elements.
 */
FilterModal.prototype.populateVisitorsFilters = function(data, dropdowns) {
    const generator = new FilterGenerator(this.filterWrapperSelector);

    const self = this;

    dropdowns.each(function () {
        const dropdown = jQuery(this),
            fieldName = dropdown.attr('data-type'),
            options = data[fieldName];

        if (options) {
            dropdown.empty();

            const placeholder = self.settings.fields[fieldName].placeholder;

            generator.createOptions(
                dropdown[0],
                Object.keys(options).map(key => ({
                    value: key,
                    label: options[key],
                })),
                placeholder
            );
        }
    });
}

/**
 * Fetches visitor filter data via AJAX.
 * @param {Object} spinner - The spinner instance.
 * @param {Object} dropdowns - The dropdown elements.
 */
FilterModal.prototype.fetchVisitorsFilters = function(spinner, dropdowns) {
    spinner.show();

    const self = this;

    let params = {
        wps_nonce: wps_js.global.rest_api_nonce,
        action: 'wp_statistics_get_filters',
        filters: Object.keys(self.settings.fields)
            .filter(field => 
                field !== 'pageName' && 
                !(self.settings.fields[field]?.attributes?.['data-searchable']) 
            )
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
                self.populateVisitorsFilters(data, dropdowns);
            }
        },
        error: function () {
            jQuery("span.tb-close-icon").click();
        },
        complete: function () {
            spinner.hide();
        }
    });
}

/**
 * Generates form fields dynamically based on the filter settings.
 */
FilterModal.prototype.generateFields = function () {
    const generator = new FilterGenerator(this.filterWrapperSelector);

    Object.entries(this.settings.fields).forEach(field => {
        switch (field[1].type) {
            case 'select':
                generator.createSelect(field[1]);
                break;
            case 'hidden':
            case 'text':
                generator.createInput(field[1]);
                break;
            case 'button':
                generator.createButton(field[1]);
                break;
            default:
                console.warn(`Unsupported field type: ${field[1].type}`);
        }
    });
};

/**
 * Handles form submission.
 * Disables empty inputs before submission to avoid sending unnecessary data.
 * @param {Event} e - The form submit event.
 */
FilterModal.prototype.onFormSubmit = function (e) {
    this.setLoading();

    if (typeof this.settings.onSubmit === 'function') {
        this.settings.onSubmit(e);
        return;
    }

    const targetForm = jQuery(e.target);

    Object.entries(this.settings.fields).forEach(([name, field]) => {
        const type = field?.type || '';

        if (type === 'button') {
            return;
        }

        const input = targetForm.find(`*[name="${field.name}"]`);

        if (input.val()?.trim() === '') {
            input.prop('disabled', true);
        }
    })

    const order = wps_js.getLinkParams('order');
    if (order) {
        targetForm.append('<input type="hidden" name="order" value="' + order + '">');
    }

    return true;
};

/**
 * Handles reset button click event.
 * Resets all filter fields by removing them from the URL.
 * @param {Event} e - The reset button click event.
 */
FilterModal.prototype.onResetFilterClick = function (e) {
    e.preventDefault();

    this.setLoading('reset');

    if (typeof this.settings.onReset === 'function') {
        this.settings.onReset();
        return;
    }

    const url = new URL(window.location.href);

    this.fieldTypes.forEach((param) => {
        url.searchParams.delete(param);
    });

    window.location.href = url.toString();

    this.toggleResetButton();
};

/**
 * Toggles the reset button based on the presence of active filters.
 */
FilterModal.prototype.toggleResetButton = function () {
    const resetButton = jQuery(this.settings.resetSelector);

    if (!resetButton.length) {
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);

    // Check if any ignored parameter exists and has a non-empty value
    const shouldEnableReset = this.fieldTypes.some(param => {
        return urlParams.has(param) && urlParams.get(param).trim() !== '';
    });
    
    if (shouldEnableReset) {
        resetButton.removeAttr('disabled');
    } else {
        resetButton.attr('disabled', 'disabled');
    }
};

FilterModal.prototype.setLoading = function(type = 'submit') {
    if (type === 'reset') {
        jQuery(`${this.formSelector} .wps-modal-reset-filter`)
            .html(wps_js._('loading'))
            .addClass('loading');
        return;
    }

    jQuery(`${this.formSelector} .button-primary`)
        .html(wps_js._('loading'))
        .addClass('loading');
};