function FilterModal(options) {
    const defaults = {
        modalSelector: '#wps-modal-filter',
        formSelector: '.wps-modal-filter-form',
        filterContainerSelector: '.filter-select, .filter-input',
        resetSelector: '.wps-modal-reset-filter',
        width: 430,
        height: 510,
        onSubmit: null,
        onOpen: null,
        onDataLoad: null,
        onReset: null,
    };

    this.settings = { ...defaults, ...options };
    this.formSelector = this.settings.formSelector;
    this.filterContainerSelector = `${this.formSelector} ${this.settings.filterContainerSelector}`;
    this.init();
}

FilterModal.prototype.init = function () {
    jQuery(document).on('click', this.settings.modalSelector, this.onFilterButtonClick.bind(this));
    jQuery(document).on('submit', this.settings.formSelector, this.onFormSubmit.bind(this));
    jQuery(document).on('click', this.settings.resetSelector, this.onResetFilterClick.bind(this));
};

FilterModal.prototype.onFilterButtonClick = function (e) {
    e.preventDefault();

    // Show modal
    tb_show(
        wps_js._('filters'),
        `#TB_inline?&width=${this.settings.width}&height=${this.settings.height}&inlineId=wps-modal-filter-popup`
    );

    // Call custom onOpen handler if provided
    if (typeof this.settings.onOpen === 'function') {
        this.settings.onOpen();
        setTimeout(() => this.setSelectedValues(), 300);
    }

    this.toggleResetButton();
};

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

FilterModal.prototype.onFormSubmit = function (e) {
    if (typeof this.settings.onSubmit === 'function') {
        jQuery(".wps-tb-window-footer .button-primary")
            .html(wps_js._('loading'))
            .addClass('loading');
        this.settings.onSubmit(e);
    }

    return true;
};

FilterModal.prototype.onResetFilterClick = function (e) {
    e.preventDefault();

    if (typeof this.settings.onReset === 'function') {
        this.settings.onReset();
        console.log('a')
    } else {
        const url = new URL(window.location.href);
        const paramsToRemove = ['referrer', 'author_id', 'url', 'ip', 'agent', 'Browser', 'location', 'platform', 'user_id'];
        paramsToRemove.forEach((param) => {
            url.searchParams.delete(param);
        });

        window.location.href = url.toString();
    }
    this.toggleResetButton();
};


FilterModal.prototype.toggleResetButton = function () {
    const resetButton = jQuery(this.settings.resetSelector);

    if (!resetButton.length) {
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const ignoredParams = ['referrer', 'author_id', 'url', 'ip', 'agent', 'Browser', 'location', 'platform', 'user_id'];

    // Check if any ignored parameter exists and has a non-empty value
    const shouldEnableReset = ignoredParams.some(param => {
        return urlParams.has(param) && urlParams.get(param).trim() !== '';
    });

    if (shouldEnableReset) {
        resetButton.removeAttr('disabled');
    } else {
        resetButton.attr('disabled', 'disabled');
    }
};

// Initialize FilterModal if on the visitors page
if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors") {
    new FilterModal({
        formSelector: '#wp_statistics_visitors_filter_form',

    });
}