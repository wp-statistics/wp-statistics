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
                if (data.unpassed_audits) LoadUnPassed(data.unpassed_audits);
                if (data.passed_audits) loadPassed(data.passed_audits);
                if (data.recommended_audits) loadRecommended(data.recommended_audits);

                // Append faq items to the page.
                loadFaqs(data.faq_list);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
    document.addEventListener('click', (e) => {
        const actionButton = e.target.closest('.wps-privacy-list__button[data-action]');
        if (actionButton) {
            const button = jQuery(actionButton);
            const auditName = button.data('audit');
            const auditAction = button.data('action');
            const auditElement = jQuery('#' + auditName);

            // Do not proceed if button is in loading state
            if (button.hasClass('loading')) return;

            button.addClass('loading');
            jQuery('.wps-privacy-questions .wps-privacy-list__items').addClass('loading');
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
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        }
    });


    function updateComplianceInfo(complianceData) {
        const headerPrivacyIndicator = jQuery('.wps-adminHeader .wps-adminHeader__side .privacy');
        const complianceStatusWrapper = jQuery('.wps-privacy-status');

        complianceStatusWrapper.removeClass('loading success warning');
        complianceStatusWrapper.find('.wps-privacy-status__bar-passed').css('display', 'none');

        // Update compliance status element with new data
        complianceStatusWrapper.addClass(complianceData.percentage_ready == 100 ? 'success' : 'warning');
        complianceStatusWrapper.find('.wps-privacy-status__percent-value').text(complianceData.percentage_ready);
        complianceStatusWrapper.find('.wps-privacy-status__rules-mapped-value').text(complianceData.rules_mapped);
        complianceStatusWrapper.find('.wps-privacy-status__passed-value').text(complianceData.summary.passed);
        complianceStatusWrapper.find('.wps-privacy-status__final-value').text(complianceData.rules_mapped);

        // Update elements that depend on action required audits 
        if (complianceData.summary.action_required > 0) {
            headerPrivacyIndicator.addClass('warning');
        } else {
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
        element.attr('class', `wps-audit-card wps-audit-card--${data.status}`);
        element.find('.wps-privacy-list__icon').attr('class', `wps-privacy-list__icon wps-privacy-list__icon--${data.status}`);
        element.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${data.status}`);
        element.find('.wps-privacy-list__text').html(data.title);
        element.find('.wps-privacy-list__content').html(data.notes);

        // Update action
        if (data.hasOwnProperty('action')) {
            let buttonClass = '';
            if (data.action.key === 'resolve') {
                buttonClass = data.action.key + ' ' + 'js-openModal-privacy-audit-confirmation';
            } else {
                buttonClass = data.action.key;
            }
            element.find('.wps-privacy-list__button').attr('class', `wps-privacy-list__button wps-privacy-list__button--${buttonClass}`);
            if (data.action.key === 'resolve') {
                element.find('.wps-privacy-list__button').removeAttr('data-action');
                element.find('.wps-privacy-list__button').attr('data-action-modal', data.action.key);
                document.querySelector('#privacy-audit-confirmation .wps-privacy-list__button').setAttribute('data-audit', data.name);

            } else {
                element.find('.wps-privacy-list__button').attr('data-action', data.action.key);
            }
            element.find('.wps-privacy-list__button').data('action', data.action.key);
            element.find('.wps-privacy-list__button').text(data.action.value);
        }
    }


    function loadAuditItems(auditList, selector) {
        const privacyItemsWrapper = jQuery(selector);
        privacyItemsWrapper.html('');
        privacyItemsWrapper.parent().removeClass('loading');

        // Convert object to an array
        const auditArray = Array.isArray(auditList) ? auditList : Object.values(auditList);

        auditArray.forEach(auditData => {
            const auditElement = generateAuditElement(auditData);
            privacyItemsWrapper.append(auditElement);
        });

        if (auditArray.length === 0) {
            jQuery(selector).parent().css('display', 'none');
        } else {
            jQuery(selector).parent().css('display', 'block');
        }

    }

    function loadPassed(auditList) {
        loadAuditItems(auditList, '.wps-privacy-passed .wps-audit-cards__container');
    }

    function LoadUnPassed(auditList) {
        loadAuditItems(auditList, '.wps-privacy-unpassed .wps-audit-cards__container');
    }

    function loadRecommended(auditList) {
        loadAuditItems(auditList, '.wps-privacy-recommended .wps-audit-cards__container');
        if(Object.keys(auditList).length > 0){
            document.querySelector('.wps-privacy-recommended').style.display = 'block';
        }

    }

    const generateIcon = (svg) => {
        if (!svg) return '';
        return `
        <div class="wps-audit-card__icon">${svg}</div>`;
    };

    function generateAuditElement(data) {
        let actionData = '';
        let buttonClass = data.status;
        let buttonTitle = data.compliance.value;

        // If item has action, set proper data attribute
        if (data.hasOwnProperty('action')) {

            buttonTitle = data.action.value;

            if (data.action.key === 'resolve') {
                actionData += `data-audit="${data.name}"  data-action-modal="${data.action.key}"`;
                buttonClass = data.action.key + ' ' + 'js-openModal-privacy-audit-confirmation';
                document.querySelector('#privacy-audit-confirmation .wps-privacy-list__button').setAttribute('data-audit', data.name);

            } else {
                actionData += `data-audit="${data.name}" data-action="${data.action.key}"`;
                buttonClass = data.action.key;
            }
        }

        let auditElement = `
        <div id="${data.name}"  class="wps-audit-card wps-audit-card--${data.status}">
            <div class="wps-audit-card__header">
                <div class="wps-audit-card__top">
                    <div class="wps-audit-card__details">
                         ${generateIcon(data?.icon)}
                          <div>
                            <h3 class="wps-audit-card__title">${data.title}</h3>
                          </div>
                    </div>
                    <div class="wps-audit-card__status">`;
        if (!data.hasOwnProperty('action')) {
            auditElement += ` <span class="wps-audit-card__status-indicator"></span>`;
        }
        auditElement += ` 
                         <a ${actionData} class="wps-privacy-list__button wps-privacy-list__button--${buttonClass}">${buttonTitle}</a>
                        <button class="wps-audit-card__toggle" aria-expanded="false"></button>
                    </div>
                </div>
            </div>
            <div class="wps-audit-card__body">
                <div class="wps-audit-card__content-text">${data.notes}</div>
            </div>
        </div>`;
        return auditElement;
    }


    function loadFaqs(faqList) {
        const faqWrapper = jQuery('.wps-privacy-questions .wps-audit-cards__container');
        faqWrapper.html('');
        faqWrapper.removeClass('loading');

        faqList.forEach(faqData => {
            const faqElement = generateFaqElement(faqData);
            faqWrapper.append(faqElement);
        });
    }

    function generateFaqElement(data) {
        let faqElement = `
        <div class="wps-audit-card wps-audit-card--${data.status}">
            <div class="wps-audit-card__header">
                <div class="wps-audit-card__top">
                    <div class="wps-audit-card__details">
                        ${generateIcon(data.icon)}
                        <div>
                            <h3 class="wps-audit-card__title">${data.title}</h3>
                            <p class="wps-audit-card__summary">${data.summary}</p>
                        </div>
                    </div>
                    <div class="wps-audit-card__status">
                        <span class="wps-audit-card__status-indicator"></span>`;

        if (data.status === 'warning') {
            faqElement += `<span class="wps-audit-card__status-text">${wps_js._('action_required')}</span>`;
        }

        faqElement += `
                        <button class="wps-audit-card__toggle" aria-expanded="false"></button>
                    </div>
                </div>
            </div>
            <div class="wps-audit-card__body">
                <div class="wps-audit-card__content-text">${data.notes}</div> 
            </div>
        </div>`;

        return faqElement;
    }
}