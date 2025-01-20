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

    this.settings = {...defaults, ...options};
    this.formSelector = this.settings.formSelector;
    this.filterContainerSelector = `${this.formSelector} ${this.settings.filterContainerSelector}`;

    this.init();
}

FilterModal.prototype.init = function () {
    jQuery(document).on('click', this.settings.modalSelector, this.onFilterButtonClick.bind(this));
    jQuery(document).on('submit', this.formSelector, this.onFormSubmit.bind(this));
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

FilterModal.prototype.setSelectedValues = function () {
    jQuery(this.filterContainerSelector).each(function () {
        const element = jQuery(this);
        const fieldName = element.attr('name');
        const currentValue = wps_js.getLinkParams(fieldName);

        if (currentValue !== null) {
            if (element.is('select')) {
                element.find(`option[value="${currentValue}"]`).prop('selected', true);
            } else if (element.is('input')) {
                element.val(decodeURIComponent(currentValue));
            }
        }
    });

    wps_js.select2();

    this.toggleResetButton();

    // Call custom data load handler if provided
    if (typeof this.settings.onDataLoad === 'function') {
        this.settings.onDataLoad();
    }
};

FilterModal.prototype.onFormSubmit = function (e) {
    if (typeof this.settings.onSubmit === 'function') {
        this.settings.onSubmit(targetForm);
    }

    return true;
};

FilterModal.prototype.onResetFilterClick = function (e) {
    e.preventDefault();

    if (typeof this.settings.onReset === 'function') {
        this.settings.onReset();
    } else {
        const url = new URL(window.location.href);
        const paramsToRemove = ['referrer', 'author_id', 'url'];
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
    const ignoredParams = ['referrer', 'author_id', 'url'];
    const result = ignoredParams.some(param => urlParams.has(param));

    if (result) {
        resetButton.removeAttr('disabled');
    } else {
        resetButton.attr('disabled', 'disabled');
    }
};