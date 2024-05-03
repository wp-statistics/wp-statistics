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

                const auditList        = data.audit_list;
                const complianceStatus = data.compliance_status;

                // Update compliance information
                updateComplianceData(complianceStatus);

                // Append audit items to the page.
                auditList.forEach(item => {
                    let actionData = '';

                    // If item is not passed and has action, set proper data attribute
                    if (item.compliance.key != 'passed' && item.hasOwnProperty('action')) {
                        actionData += `data-audit="${item.name}" data-action="${item.action.key}"`;

                        // if action needs confirmation, set data attribute.
                        if (item.action.hasOwnProperty('confirm') && item.action.confirm == true) {
                            actionData += ` data-confirm="true"`;

                            if (item.action.hasOwnProperty('confirm_text')) {
                                actionData += ` data-confirm-text="${item.action.confirm_text}"`;
                            }

                            if (item.action.hasOwnProperty('success_text')) {
                                actionData += ` data-success-text="${item.action.success_text}"`;
                            }

                            if (item.action.hasOwnProperty('removable')) {
                                actionData += ` data-removable="${item.action.removable}"`;
                            }
                        }

                    }

                    const auditElement  = `
                        <div id="${item.name}" class="wps-privacy-list__item wps-privacy-list__item--${item.status}">
                            <div class="wps-privacy-list__title">
                                <div>
                                    <span class="wps-privacy-list__icon wps-privacy-list__icon--${item.status}"></span>
                                    <span class="wps-privacy-list__text">${item.title}</span>
                                </div>
                                <a ${actionData}  class="wps-privacy-list__button wps-privacy-list__button--${item.status}">${item.compliance.value}</a>
                            </div>
                            <div class="wps-privacy-list__content">${item.notes}</div>
                        </div>
                    `;

                    const privacyItemsWrapper = jQuery('.wps-privacy-list .wps-privacy-list__items');
                    privacyItemsWrapper.removeClass('loading');
                    privacyItemsWrapper.append(auditElement);
                });
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
        const needsConfirm  = button.data('confirm');
        const isRemovable   = button.data('removable');
        const confirmText   = button.data('confirm-text');
        const successText   = button.data('success-text');
        const auditElement  = jQuery('#' + auditName);

        // Do not proceed if button is in loading state
        if (button.hasClass('loading')) return;

        // if action needs confirmation, show confirmation box.
        if (needsConfirm) {
            const agree = confirm(confirmText || wps_js._('confirm'));
            if (!agree) return false;
        }

        // Add loading class
        button.addClass('loading');
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

                const auditItem        = data.audit_item;
                const complianceStatus = data.compliance_status;

                // Remove loading
                button.removeClass('loading');

                // Update compliance data
                updateComplianceData(complianceStatus);

                // If element is removable, hide it after success response
                if (isRemovable) {
                    alert(successText);
                    auditElement.slideUp();
                    return;
                }

                // If audit item is not null, update it with new data
                if (!auditItem) return;
                    
                auditElement.attr('class', `wps-privacy-list__item wps-privacy-list__item--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__icon').attr('class', `wps-privacy-list__icon wps-privacy-list__icon--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__text').html(auditItem.title);
                auditElement.find('.wps-privacy-list__content').html(auditItem.notes);

                if (auditItem.hasOwnProperty('action')) {
                    auditElement.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${auditItem.action.key}`);
                    auditElement.find('.wps-privacy-list__button').attr('data-action', auditItem.action.key);
                    auditElement.find('.wps-privacy-list__button').data('action', auditItem.action.key);
                    auditElement.find('.wps-privacy-list__button').text(auditItem.action.value);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });

    function updateComplianceData(complianceData) {
        const complianceStatusWrapper = jQuery('.wps-privacy-status');
        
        // Reset previous styles
        complianceStatusWrapper.removeClass('loading success warning');
        complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('display', 'none');
        complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('display', 'none')
        
        // Update compliance status element with new data
        complianceStatusWrapper.addClass(complianceData.percentage_ready == 100 ? 'success' : 'warning');
        complianceStatusWrapper.find('.wps-privacy-status__percent-value').text(complianceData.percentage_ready);
        complianceStatusWrapper.find('.wps-privacy-status__rules-mapped-value').text(complianceData.rules_mapped);
        complianceStatusWrapper.find('.wps-privacy-status__passed-value').text(complianceData.summary.passed);
        complianceStatusWrapper.find('.wps-privacy-status__need-work-value').text(complianceData.summary.action_required);

        // Update action required audits percentage bar
        if (complianceData.summary.action_required > 0) {
            complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('display', 'block');
            complianceStatusWrapper.find('.wps-privacy-status__bar-need-work').css('width', `${100 - complianceData.percentage_ready}%`);
        }

        // Update passed audits percentage bar
        if (complianceData.summary.passed > 0) {
            complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('display', 'block');
            complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('width', `${complianceData.percentage_ready}%`);
        }
    }
}