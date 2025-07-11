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

    class ShowIfEnabled {
        constructor() {
            this.initialize();
        }

        initialize() {
            const elements = document.querySelectorAll('[class^="js-wps-show_if_"]');
            elements.forEach(element => {
                const classListArray = [...element.className.split(' ')];

                const toggleElement = () => {
                    let conditions = 0;
                    let satisfied = 0;

                    classListArray.forEach(className => {
                        if (className.includes('_enabled') || className.includes('_disabled')) {
                            conditions++;
                            const id = this.extractId(element);
                            const checkbox = document.querySelector(`#${id}`) || document.querySelector(`#wps_settings\\[${id}\\]`);
                            if (checkbox) {
                                if (checkbox.type === 'checkbox') {
                                    if (checkbox.checked && className.includes('_enabled')) {
                                        satisfied++;
                                    } else if (!checkbox.checked && className.includes('_disabled')) {
                                        satisfied++;
                                    }
                                }
                            }
                        } else if (className.includes('_equal_')) {
                            conditions++;
                            const {id, value} = this.extractIdAndValue(className);
                            if (id && value) {
                                const item = document.querySelector(`#wps_settings\\[${id}\\]`);
                                if (item && item.type === 'select-one') {
                                    if (item.value === value) {
                                        satisfied++;
                                    }
                                }
                            }
                        }
                    });

                    if (conditions > 0 && satisfied === conditions) {
                        this.toggleDisplay(element);
                    } else {
                        element.style.display = 'none';
                        const checkboxInside = element.querySelector('input[type="checkbox"]');
                        if (checkboxInside) {
                            checkboxInside.checked = false;
                        }
                    }
                };

                toggleElement();

                classListArray.forEach(className => {
                    if (className.includes('_enabled') || className.includes('_disabled')) {
                        const id = this.extractId(element);
                        const checkbox = document.querySelector(`#wps_settings\\[${id}\\]`);
                        if (checkbox) {
                            checkbox.addEventListener('change', toggleElement);
                        }
                    } else if (className.includes('_equal_')) {
                        const {id} = this.extractIdAndValue(className);
                        if (id) {
                            const item = document.querySelector(`#wps_settings\\[${id}\\]`);
                            if (item) {
                                if ($(item).hasClass('select2-hidden-accessible')) {
                                    $(item).on('select2:select', toggleElement);
                                } else if (item.type === 'select-one') {
                                    item.addEventListener('change', toggleElement);
                                }
                            }
                        }
                    }
                });
            });
        }

        toggleDisplay(element) {
            const displayType = element.tagName.toLowerCase() === 'tr' ? 'table-row' : 'table-cell';
            element.style.display = displayType;
        }

        extractId(element) {
            const classes = element.className.split(' ');
            for (const className of classes) {
                if (className.startsWith('js-wps-show_if_')) {
                    return className.replace('js-wps-show_if_', '').replace('_enabled', '').replace('_disabled', '');
                }
            }
            return null;
        }

        extractIdAndValue(className) {
            let id, value;
            if (className.startsWith('js-wps-show_if_')) {
                const parts = className.split('_');
                const indexOfEqual = parts.indexOf('equal');
                if (indexOfEqual !== -1 && indexOfEqual > 2 && indexOfEqual < parts.length - 1) {
                    id = parts.slice(2, indexOfEqual).join('_');
                    value = parts.slice(indexOfEqual + 1).join('_');
                }
            }
            return {id, value};
        }
    }

    $(document).ready(function () {
        // Handle retention period change
        const retentionSelect = $('#wps_settings\\[wps_schedule_dbmaint_days_select\\]');
        const customRetentionInput = $('#wps_schedule_dbmaint_days_custom');
        const hiddenRetentionInput = $('#wps_schedule_dbmaint_days');
        const presets = [0, 30, 60, 90, 180, 365, 730];
        let initialRetention = parseInt(hiddenRetentionInput.val()) || 0;
        let isProcessingChange = false;

        function getNewValue() {
            const selectValue = retentionSelect.val();
            let newValue;

            if (selectValue === 'custom') {
                newValue = parseInt(customRetentionInput.val());
                if (isNaN(newValue) || newValue < 30 || newValue > 3650) {
                    newValue = initialRetention;
                    customRetentionInput.val(newValue);
                }
            } else {
                newValue = parseInt(selectValue);
            }
            return newValue;
        }

        function updateHiddenInput(newValue) {
            hiddenRetentionInput.val(newValue);
        }

        function revertToInitialState() {
            updateHiddenInput(initialRetention);
            if (presets.includes(initialRetention)) {
                retentionSelect.val(initialRetention.toString()).trigger('change.select2');
                customRetentionInput.val('');
            } else {
                retentionSelect.val('custom').trigger('change.select2');
                customRetentionInput.val(initialRetention);
            }
            new ShowIfEnabled();
        }

        function showRetentionConfirmationModal(newValue, callback) {
            const modalId = 'enable-automatic-data-deletion';
            const modal = document.getElementById(modalId);
            if (!modal) {
                callback(true);
                return;
            }

            const description = modal.querySelector('.wps-alert__danger span');
            if (description) {
                description.textContent = newValue === 0 ? 'Forever' : newValue;
            }

            modal.classList.add('wps-modal--open');

            const primaryButton = modal.querySelector('button[data-action="enable"]');
            if (primaryButton) {
                primaryButton.addEventListener('click', function handler() {
                    modal.classList.remove('wps-modal--open');
                    updateHiddenInput(newValue);
                    initialRetention = newValue;
                    callback(true);
                }, { once: true });
            }

            const closeButton = modal.querySelector('button[data-action="closeModal"]');
            if (closeButton) {
                closeButton.addEventListener('click', function handler() {
                    modal.classList.remove('wps-modal--open');
                    revertToInitialState();
                    callback(false);
                }, { once: true });
            }

            const overlay = modal.querySelector('.wps-modal__overlay');
            if (overlay) {
                overlay.addEventListener('click', function handler() {
                    modal.classList.remove('wps-modal--open');
                    revertToInitialState();
                    callback(false);
                }, { once: true });
            }
        }

        retentionSelect.on('change', function () {
            if (isProcessingChange) {
                return;
            }
            isProcessingChange = true;

            if (retentionSelect.val() === 'custom' && !customRetentionInput.val()) {
                customRetentionInput.val(initialRetention || 30);
            }

            const newValue = getNewValue();
            if ((initialRetention === 0 && newValue !== 0) || (newValue !== 0 && initialRetention !== 0 && newValue < initialRetention)) {
                showRetentionConfirmationModal(newValue, function (confirmed) {
                    if (!confirmed) {
                        revertToInitialState();
                    }
                    isProcessingChange = false;
                });
            } else {
                updateHiddenInput(newValue);
                initialRetention = newValue;
                isProcessingChange = false;
            }
        });

        customRetentionInput.on('change', function () {
            if (isProcessingChange || retentionSelect.val() !== 'custom') {
                isProcessingChange = false;
                return;
            }
            isProcessingChange = true;

            const newValue = getNewValue();
            if ((initialRetention === 0 && newValue !== 0) || (newValue !== 0 && initialRetention !== 0 && newValue < initialRetention)) {
                showRetentionConfirmationModal(newValue, function (confirmed) {
                    if (!confirmed) {
                        revertToInitialState();
                    }
                    isProcessingChange = false;
                });
            } else {
                updateHiddenInput(newValue);
                initialRetention = newValue;
                isProcessingChange = false;
            }
        });

        retentionSelect.on('select2:select', function () {
            if (!isProcessingChange) {
                retentionSelect.trigger('change');
            }
        });

        if (presets.includes(initialRetention)) {
            retentionSelect.val(initialRetention.toString()).trigger('change.select2');
            customRetentionInput.val('');
        } else {
            retentionSelect.val('custom').trigger('change.select2');
            customRetentionInput.val(initialRetention);
        }

        new ShowIfEnabled();
    });
}