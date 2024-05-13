if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "privacy-audit") {

    jQuery(document).ready(() => {
        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_getPrivacyStatus'
        };
        params = Object.assign(params, wps_js.global.request_params);
    
        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {

                // If request is not successful, return early
                if (success == false) return console.log(data);

                // Update compliance information
                updateComplianceInfo(data.compliance_status);

                // Append audit items to the page.
                loadAudits(data.audit_list);

                // Append faq items to the page.
                loadFaqs(data.faq_list);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });


    jQuery(document).on('click', '.wps-privacy-list__button[data-action]', (e) => {
        const button        = jQuery(e.currentTarget);
        const auditName     = button.data('audit');
        const auditAction   = button.data('action');
        const auditElement  = jQuery('#' + auditName);

        // Do not proceed if button is in loading state
        if (button.hasClass('loading')) return;

        // Show alert message when user tries to resolve the audit
        if (auditAction === 'resolve') {
            alert(wps_js._('privacy_resolve_alert'));
        }


        // Add loading class
        button.addClass('loading');
        jQuery('.wps-privacy-questions .wps-privacy-list__items').addClass('loading');
        jQuery('.wps-privacy-mode__items .wps-privacy-mode__item').addClass('loading');
        jQuery('.wps-privacy-status').addClass('loading');

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_updatePrivacyStatus',
            'audit_name': auditName,
            'audit_action': auditAction
        };
        params = Object.assign(params, wps_js.global.request_params);

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'POST',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {

                // If request is not successful, return early
                if (success == false) return console.log(data);

                // Remove loading
                button.removeClass('loading');

                // Update compliance data
                updateComplianceInfo(data.compliance_status);

                // Load faq items
                loadFaqs(data.faq_list);

                // If audit item data is not null, update it with new data
                if (data.audit_item) {
                    updateAuditElement(auditElement, data.audit_item);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });


    function updateComplianceInfo(complianceData) {
        const privacyModeWrapper        = jQuery('.wps-privacy-mode__items');
        const headerPrivacyIndicator    = jQuery('.wps-adminHeader .wps-adminHeader__side .privacy');
        const complianceStatusWrapper   = jQuery('.wps-privacy-status');
        
        // Reset previous styles
        privacyModeWrapper.find('.wps-privacy-mode__item').removeClass('loading');
        
        complianceStatusWrapper.removeClass('loading success warning');
        complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('display', 'none');
        complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('display', 'none')
        
        // Update compliance status element with new data
        complianceStatusWrapper.addClass(complianceData.percentage_ready == 100 ? 'success' : 'warning');
        complianceStatusWrapper.find('.wps-privacy-status__percent-value').text(complianceData.percentage_ready);
        complianceStatusWrapper.find('.wps-privacy-status__rules-mapped-value').text(complianceData.rules_mapped);
        complianceStatusWrapper.find('.wps-privacy-status__passed-value').text(complianceData.summary.passed);
        complianceStatusWrapper.find('.wps-privacy-status__need-work-value').text(complianceData.summary.action_required);

        // Update elements that depend on action required audits 
        if (complianceData.summary.action_required > 0) {
            privacyModeWrapper.find('.wps-privacy-mode__item input:checked').next().find('.wps-privacy-mode__content').text(wps_js._('non_privacy_compliant'));
            privacyModeWrapper.find('.wps-privacy-mode__item input:checked').parent().addClass('wps-privacy-mode__item--warning');

            complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('display', 'block');
            complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('width', `${100 - complianceData.percentage_ready}%`);

            headerPrivacyIndicator.addClass('warning');
        } else {
            privacyModeWrapper.find('.wps-privacy-mode__item input:checked').next().find('.wps-privacy-mode__content').text(wps_js._('privacy_compliant'));
            privacyModeWrapper.find('.wps-privacy-mode__item input:checked').parent().removeClass('wps-privacy-mode__item--warning');

            headerPrivacyIndicator.removeClass('warning');
        }

        // Update elements that depend on passed audit
        if (complianceData.summary.passed > 0) {
            complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('display', 'block');
            complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('width', `${complianceData.percentage_ready}%`);
        }
    }


    function updateAuditElement(element, data) {
        // Update content
        element.attr('class', `wps-privacy-list__item wps-privacy-list__item--${data.status}`);
        element.find('.wps-privacy-list__icon').attr('class', `wps-privacy-list__icon wps-privacy-list__icon--${data.status}`);
        element.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${data.status}`);
        element.find('.wps-privacy-list__text').html(data.title);
        element.find('.wps-privacy-list__content').html(data.notes);

        // Update action
        if (data.hasOwnProperty('action')) {
            element.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${data.action.key}`);
            element.find('.wps-privacy-list__button').attr('data-action', data.action.key);
            element.find('.wps-privacy-list__button').data('action', data.action.key);
            element.find('.wps-privacy-list__button').text(data.action.value);
        }
    }


    function loadAudits(auditList) {
        const privacyItemsWrapper = jQuery('.wps-privacy-list .wps-privacy-list__items');
        privacyItemsWrapper.html('');
        privacyItemsWrapper.removeClass('loading');

        auditList.forEach(auditData => {
            const auditElement = generateAuditElement(auditData);
            privacyItemsWrapper.append(auditElement);
        });
    }


    function generateAuditElement(data) {
        let actionData  = '';
        let buttonClass = data.status;
        let buttonTitle = data.compliance.value;

        // If item has action, set proper data attribute
        if (data.hasOwnProperty('action')) {
            actionData += `data-audit="${data.name}" data-action="${data.action.key}"`;
            buttonClass = data.action.key;
            buttonTitle = data.action.value;
        }

        const auditElement = `
            <div id="${data.name}" class="wps-privacy-list__item wps-privacy-list__item--${data.status}">
                <div class="wps-privacy-list__title">
                    <div>
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--${data.status}"></span>
                        <span class="wps-privacy-list__text">${data.title}</span>
                    </div>
                    <a ${actionData} class="wps-privacy-list__button wps-privacy-list__button--${buttonClass}">${buttonTitle}</a>
                </div>
                <div class="wps-privacy-list__content">${data.notes}</div>
            </div>`;

        return auditElement;
    }


    function loadFaqs(faqList) {
        const faqWrapper = jQuery('.wps-privacy-questions .wps-privacy-list__items');
        faqWrapper.html('');
        faqWrapper.removeClass('loading');
                
        faqList.forEach(faqData => {
            const faqElement = generateFaqElement(faqData);
            faqWrapper.append(faqElement);
        });
    }

    
    function generateFaqElement(data) {
        const faqElement = `
            <div class="wps-privacy-list__item wps-privacy-list__item--${data.status}">
                <div class="wps-privacy-list__title">
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--${data.status}"></span>
                    <div>
                        <span>${data.title}</span>
                        <span>${data.summary}</span>
                    </div>
                </div>
                <div class="wps-privacy-list__content">${data.notes}</div>
            </div>`;

        return faqElement;
    }
}