function Spinner(options) {
    this.container = options.container || 'body';
    this.spinnerElement = null;
}

Spinner.prototype.show = function () {
    if (!this.spinnerElement) {
        this.spinnerElement = jQuery('<div class="spinner-overlay"></div>');
        jQuery(this.container).append(this.spinnerElement);
    }
    this.spinnerElement.show();
};

Spinner.prototype.hide = function () {
    if (this.spinnerElement) {
        this.spinnerElement.hide();
    }
};
