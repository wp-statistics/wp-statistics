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
            success: function (data) {
                const auditList        = data.audit_list;
                const complianceStatus = data.compliance_status;

                // Fill out compliance status information
                jQuery('.wps-privacy-status').removeClass('loading success warning');
                jQuery('.wps-privacy-status').addClass(complianceStatus.percentage_ready == 100 ? 'success' : 'warning');

                jQuery('.wps-privacy-status__percent-value').text(complianceStatus.percentage_ready);
                jQuery('.wps-privacy-status__rules-mapped-value').text(complianceStatus.rules_mapped);
                jQuery('.wps-privacy-status__passed-value').text(complianceStatus.summary.passed);
                jQuery('.wps-privacy-status__need-work-value').text(complianceStatus.summary.action_required);

                // Append audit items to the page.
                auditList.forEach(item => {
                    // If item is not passed and has action, set proper data attribute
                    let actionData = '';
                    if (item.compliance.key != 'passed' && item.hasOwnProperty('action')) {
                        actionData = `data-action-name="${item.name}" data-action-type="${item.action.key}"`;
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

                    jQuery('.wps-privacy-list .wps-privacy-list__items').append(auditElement);
                });
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });


    jQuery(document).on('click', '.wps-privacy-list__button[data-action-type]', (e) => {
        const actionName    = jQuery(e.currentTarget).data('action-name');
        const actionType    = jQuery(e.currentTarget).data('action-type');
        const elementID     = '#' + actionName;
        const auditElement  = jQuery(elementID);

        // Fill out compliance status information
        auditElement.find('.wps-privacy-list__button').addClass('loading');
        jQuery('.wps-privacy-status').removeClass('loading success warning');
        jQuery('.wps-privacy-status').addClass('loading');

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_updatePrivacyStatus',
            'action_name': actionName,
            'action_type': actionType
        };
        params = Object.assign(params, wps_js.global.request_params);

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'POST',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function (data) {
                const auditItem        = data.audit_item;
                const complianceStatus = data.compliance_status;

                auditElement.find('.wps-privacy-list__button').removeClass('loading');
                jQuery('.wps-privacy-status').removeClass('loading');
                jQuery('.wps-privacy-status').addClass(complianceStatus.percentage_ready == 100 ? 'success' : 'warning');

                jQuery('.wps-privacy-status__percent-value').text(complianceStatus.percentage_ready);
                jQuery('.wps-privacy-status__rules-mapped-value').text(complianceStatus.rules_mapped);
                jQuery('.wps-privacy-status__passed-value').text(complianceStatus.summary.passed);
                jQuery('.wps-privacy-status__need-work-value').text(complianceStatus.summary.action_required);

                auditElement.attr('class', `wps-privacy-list__item wps-privacy-list__item--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__icon').attr('class', `wps-privacy-list__icon wps-privacy-list__icon--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${auditItem.status}`);
                auditElement.find('.wps-privacy-list__text').html(auditItem.title);
                auditElement.find('.wps-privacy-list__content').html(auditItem.notes);

                if (auditItem.hasOwnProperty('action')) {
                    auditElement.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${auditItem.action.key}`);
                    auditElement.find('.wps-privacy-list__button').attr('data-action-type', auditItem.action.key);
                    auditElement.find('.wps-privacy-list__button').data('action-type', auditItem.action.key);
                    auditElement.find('.wps-privacy-list__button').text(auditItem.action.value);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}