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

                    const isOrCondition = element.classList.contains('js-wps-show_if_or');

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

                    if (conditions > 0) {
                        if (isOrCondition) {
                            if (satisfied > 0) {
                                this.toggleDisplay(element);
                            } else {
                                element.style.display = 'none';
                                const checkboxInside = element.querySelector('input[type="checkbox"]');
                                if (checkboxInside) {
                                    checkboxInside.checked = false;
                                }
                            }
                        } else {
                            if (satisfied === conditions) {
                                this.toggleDisplay(element);
                            } else {
                                element.style.display = 'none';
                                const checkboxInside = element.querySelector('input[type="checkbox"]');
                                if (checkboxInside) {
                                    checkboxInside.checked = false;
                                }
                            }
                        }
                    } else {
                        this.toggleDisplay(element);
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
                    return className.replace('js-wps-show_if_', '').replace('_enabled', '').replace('_disabled', '').replace('_equal_', '_');
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
        let isProgrammaticChange = false
        const checkbox = $('#wps_settings\\[wps_schedule_dbmaint\\]');
        checkbox.on('change', function () {
            if (this.checked && !isProgrammaticChange) {
                const modalId = 'setting-confirmation';
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('wps-modal--open');

                    const primaryButton = modal.querySelector('button[data-action="enable"]');
                    if (primaryButton) {
                        primaryButton.addEventListener('click', function () {
                            modal.classList.remove('wps-modal--open');
                        }, {once: true});
                    }

                    const closeButton = modal.querySelector('button[data-action="closeModal"]');
                    if (closeButton) {
                        closeButton.addEventListener('click', function () {
                            checkbox.prop('checked', false);
                            modal.classList.remove('wps-modal--open');
                            new ShowIfEnabled();
                        }, {once: true});
                    }
                }
            }
        });


        new ShowIfEnabled();
        const searchConsoleSite = document.getElementById('wps_addon_settings[marketing][site]');
        if (searchConsoleSite) {
            let notice = document.createElement("div");
            notice.className = "notice notice-error wp-statistics-notice";
            const dir = jQuery('body').hasClass('rtl') ? 'rtl' : 'ltr';
            const $select = jQuery('.wps-addon-settings--marketing select').select2({
                ajax: {
                    url: wps_js.global.admin_url + 'admin-ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            wps_nonce: wps_js.global.rest_api_nonce,
                            action: 'wp_statistics_get_gsc_sites',
                            term: params.term
                        };
                    },
                    processResults: function (response) {
                        if (response && response.success && response.data) {
                            const results = response.data.map(item => ({
                                id: item.key,
                                text: item.label
                            }));

                            const selectedItem = results.find(item => response.data.find(d => d.selected && d.key === item.id));
                            if (selectedItem) {
                                $select.append(new Option(selectedItem.text, selectedItem.id, true, true)).trigger('change.select2');
                            }

                            return {results: results};
                        } else {
                            let notice = document.querySelector('.wp-statistics-notice');
                            if (!notice) {
                                notice = document.createElement("div");
                                notice.className = "notice notice-error wp-statistics-notice";
                            }
                            notice.innerHTML = `<p>${response.data || 'Error loading sites'}</p>`;
                            document.querySelector("#marketing-settings").prepend(notice);
                            const topOffset = document.querySelector('#marketing-settings').getBoundingClientRect().top + window.scrollY;
                            window.scrollTo({
                                top: topOffset,
                                behavior: "smooth"
                            });
                            return {results: []};
                        }
                    },
                    error: function (xhr, status, error) {
                        return {results: []};
                    },
                    cache: true
                },
                dir: dir,
                dropdownCssClass: 'wps-site-dropdown-class wps-marketing-select2',
                minimumResultsForSearch: Infinity,
            });

            $select.on('select2:select', function (e) {
                const data = e.params.data;
                $select.val(data.id).trigger('change');
            });
        }
    });
}