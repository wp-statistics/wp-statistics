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
    const menu = document.querySelector('.wps-optionsMenu');
    if (!menu) return;

    // Create a wrapper div for the select
    const wrapper = document.createElement('div');
    wrapper.classList.add('wps-setting-select-wrapper');

    // Create the select element
    const select = document.createElement('select');
    select.classList.add('wps-options-mobile-menu');

    // Get menu items
    const settingsItems = menu.querySelectorAll('a[data-tab]:not(.premium)');
    const premiumItems = menu.querySelectorAll('a[data-tab].premium');

    // Create Settings optgroup
    const settingsGroup = document.createElement('optgroup');
    settingsGroup.label = 'SETTINGS';

    settingsItems.forEach(item => {
        const option = document.createElement('option');
        option.value = item.getAttribute('data-tab');
        option.textContent = item.querySelector('span').textContent;

        if (item.classList.contains('active')) {
            option.selected = true;
        }
        settingsGroup.appendChild(option);
    });

    select.appendChild(settingsGroup);

    // Create Premium Add-Ons optgroup
    if (premiumItems.length > 0) {
        const premiumGroup = document.createElement('optgroup');
        premiumGroup.label = 'PREMIUM ADD-ONS';

        premiumItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.getAttribute('data-tab');
            option.textContent = item.querySelector('span').textContent;

            if (item.classList.contains('active')) {
                option.selected = true;
            }
            premiumGroup.appendChild(option);
        });

        select.appendChild(premiumGroup);
    }

    // Add event listener to navigate on change
    select.addEventListener('change', (e) => {
        navigateToTab(e.target.value);
    });

    // Append the select to the wrapper
    wrapper.appendChild(select);
    // Insert the wrapper before the menu
    menu.parentNode.insertBefore(wrapper, menu);

    $(select).select2({
        dropdownCssClass: 'wps-setting-input__dropdown',
        minimumResultsForSearch: Infinity,
    });

    // Add event listener for navigation
    $(select).on('change', function(e) {
        navigateToTab(e.target.value);
    });
}

function navigateToTab(tab) {
    // Remove 'current' class from all menu items
    document.querySelectorAll('.wps-optionsMenuItem').forEach(item => {
        item.classList.remove('current');
    });

    const selectedItem = document.querySelector(`[data-tab="${tab}"]`);
    if (selectedItem) {
         wp_statistics_enableTab(tab);
    }
}

window.onload = function() {
    createMobileDropdown();
};
/**
 * Check has setting page
 */
if (jQuery('.wp-statistics-settings').length) {
    var current_tab = wp_statistics_getParameterValue('tab');
    if (current_tab) {
        wp_statistics_enableTab(current_tab);
    }

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
    button.addEventListener('click', function() {
        const inputField = this.closest('.wps-input-group__action').querySelector('input');
        navigator.clipboard.writeText(inputField.value).then(() => {
            const originalText = button.textContent;
            button.textContent = wps_js._('copied');
            button.classList.remove('has-icon')  ;

            setTimeout(() => {
                button.textContent = originalText;
                button.classList.add('has-icon')  ;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
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
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetRow.classList.add('wps-highlight');
        }, 500);
    } else {
        console.log(`No row found with data-id="${row}"`);
    }
}

