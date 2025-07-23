/**
 * Get Parameter value
 *
 * @param name
 * @returns {*}
 */
function wp_statistics_getParameterValue(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results) {
        return results[1];
    }
}

/**
 * Enable Tab
 *
 * @param tab_id
 */
function wp_statistics_enableTab(tab_id) {
    jQuery('.wp-statistics-settings .wps-optionsMenu .wps-optionsMenuItem').removeClass('current');
    jQuery('.wp-statistics-settings .tab-content').removeClass('current');

    jQuery("[data-tab=" + tab_id + "]").addClass('current');
    jQuery("#" + tab_id).addClass('current');

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab_id);
    url.searchParams.delete('save_setting');
    window.history.pushState({}, '', url.toString());

    if (jQuery('#wp-statistics-settings-form').length) {
        var click_url = jQuery(location).attr('href') + '&tab=' + tab_id;
        jQuery('#wp-statistics-settings-form').attr('action', click_url).submit();
    }
}


function createMobileDropdown() {
     if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDropdownWithRetry);
    } else {
        initializeDropdownWithRetry();
    }

    function initializeDropdownWithRetry() {
        let retryCount = 0;
        const maxRetries = 10;
        const retryInterval = 500;
         function tryInitializeDropdown() {
            const menu = document.querySelector('.wps-optionsMenu');
            if (menu) {
                 initializeDropdown();
            } else {
                retryCount++;
                if (retryCount < maxRetries) {
                     setTimeout(tryInitializeDropdown, retryInterval);
                } else {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('wps-setting-select-wrapper');
                    document.body.appendChild(wrapper);
                }
            }
        }

        function initializeDropdown() {
            const menu = document.querySelector('.wps-optionsMenu');
            if (!menu) {
                console.warn('Menu element (.wps-optionsMenu) not found');
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.classList.add('wps-setting-select-wrapper');

            const select = document.createElement('select');
            select.classList.add('wps-options-mobile-menu');
            select.setAttribute('aria-label', 'mobile menu');

            const settingsItems = menu.querySelectorAll('a[data-tab]:not(.premium)');
            const premiumItems = menu.querySelectorAll('a[data-tab].premium');

            const titleElement = menu.querySelector('.wps-settings-side__title');
            const groupLabel = titleElement ? titleElement.textContent : wps_js._('settings');

            const settingsGroup = document.createElement('optgroup');
            settingsGroup.label = groupLabel;

            settingsItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.getAttribute('data-tab');
                option.textContent = item.querySelector('span').textContent;
                settingsGroup.appendChild(option);
            });

            select.appendChild(settingsGroup);

            if (premiumItems.length > 0) {
                const premiumGroup = document.createElement('optgroup');
                premiumGroup.label = wps_js._('premium_addons');

                premiumItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.getAttribute('data-tab');
                    option.textContent = item.querySelector('span').textContent;
                    premiumGroup.appendChild(option);
                });

                select.appendChild(premiumGroup);
            }

            wrapper.appendChild(select);
            menu.parentNode.insertBefore(wrapper, menu);

            const currentItem = menu.querySelector('a[data-tab].current');
            if (currentItem) {
                const currentTab = currentItem.getAttribute('data-tab');
                select.value = currentTab;
            } else if (settingsItems.length > 0) {
                const defaultTab = settingsItems[0].getAttribute('data-tab');
                select.value = defaultTab;
                navigateToTab(defaultTab);
            }

            const dirValue = jQuery('body').hasClass('rtl') ? 'rtl' : 'ltr';
            if (typeof $.fn.select2 === 'function') {
                $(select).select2({
                    dropdownCssClass: 'wps-setting-input__dropdown',
                    minimumResultsForSearch: Infinity,
                    dir: dirValue
                });

                if (currentItem) {
                    const currentTab = currentItem.getAttribute('data-tab');
                    $(select).val(currentTab).trigger('change.select2');
                } else if (settingsItems.length > 0) {
                    const defaultTab = settingsItems[0].getAttribute('data-tab');
                    $(select).val(defaultTab).trigger('change.select2');
                }
            }

            $(select).on('change', function (e) {
                const selectedTab = e.target.value;
                navigateToTab(selectedTab);
            });

            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName === 'class') {
                        const currentItem = menu.querySelector('a[data-tab].current');
                        if (currentItem) {
                            const currentTab = currentItem.getAttribute('data-tab');
                            if ($(select).val() !== currentTab) {
                                $(select).val(currentTab).trigger('change.select2');
                            }
                        }
                    }
                });
            });

            menu.querySelectorAll('a[data-tab]').forEach(item => {
                observer.observe(item, { attributes: true });
            });
        }

        tryInitializeDropdown();
    }
}

function navigateToTab(tab) {
    // Remove 'current' class from all menu items
    document.querySelectorAll('.wps-optionsMenuItem').forEach(item => {
        item.classList.remove('current');
    });

    const selectedItem = document.querySelector(`[data-tab="${tab}"]`);
    if (selectedItem) {
        wp_statistics_enableTab(tab);
        selectedItem.classList.add('current');
    }
}
jQuery(document).ready(function () {
    createMobileDropdown();
});

window.onload = function () {
    const closeButton = document.querySelector('.wps-alert__close');
    if (closeButton) {
        const alert = closeButton.closest('.wps-alert');
        if (alert) {
            closeButton.addEventListener('click', function () {
                alert.remove();
            });
        }
    }

    const goToTopButton = document.querySelector('.wps-gototop');
    if (goToTopButton) {
        goToTopButton.addEventListener('click', function () {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });

        window.addEventListener('scroll', function () {
            const viewportHeight = 100;
            const scrollPosition = window.scrollY;

            if (scrollPosition > viewportHeight) {
                goToTopButton.classList.add('active');
            } else {
                goToTopButton.classList.remove('active');
            }
        });
    }

};
/**
 * Check has setting page
 */
if (jQuery('.wp-statistics-settings').length) {
    var current_tab = wp_statistics_getParameterValue('tab');
    if (current_tab) {
        wp_statistics_enableTab(current_tab);
    }

    document.querySelectorAll('.iris-square-value').forEach(element => {
        if (!element.classList.contains('screen-reader-text')) {
            const span = document.createElement('span');
            span.className = 'screen-reader-text';
            span.textContent = 'square-value';
            element.appendChild(span);
        }
    });

    jQuery('.wp-statistics-settings .wps-optionsMenu .wps-optionsMenuItem').click(function () {
        var tab_id = jQuery(this).attr('data-tab');
        wp_statistics_enableTab(tab_id);
    });

    const triggerInput = document.querySelector('input[name="user_custom_header_ip_method"]');
    const customHeaderRadio = document.getElementById('custom-header');
    if (triggerInput && customHeaderRadio) {
        customHeaderRadio.addEventListener('change', function () {
            if (customHeaderRadio.checked) {
                triggerInput.focus();
            }
        });

        function checkCustomHeader() {
            customHeaderRadio.checked = true;
        }

        triggerInput.addEventListener('click', checkCustomHeader);
        triggerInput.addEventListener('paste', checkCustomHeader);
        triggerInput.addEventListener('input', checkCustomHeader);
        triggerInput.addEventListener('dragover', checkCustomHeader);
        triggerInput.addEventListener('drop', checkCustomHeader);
    }
}


const copyButtons = document.querySelectorAll('.wps-input-group__copy');

copyButtons.forEach(button => {
    button.addEventListener('click', function () {
        const inputField = this.closest('.wps-input-group__action').querySelector('input');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(inputField.value).then(() => {
                const originalText = button.textContent;
                button.textContent = wps_js._('copied');
                button.classList.remove('has-icon');

                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.add('has-icon');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    });
});


const settingSelect2 = jQuery('.wps-wrap__settings select');

if (settingSelect2.length) {
    settingSelect2.select2({
        dropdownCssClass: 'wps-setting-input__dropdown',
        minimumResultsForSearch: Infinity,
    });
}


function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

const row = getUrlParameter('row');
if (row) {
    const targetRow = document.querySelector(`tr[data-id="${row}"]`);

    if (targetRow) {
        setTimeout(() => {
            targetRow.scrollIntoView({behavior: 'smooth', block: 'center'});
            targetRow.classList.add('wps-highlight');
        }, 500);
    } else {
        console.log(`No row found with data-id="${row}"`);
    }
}

