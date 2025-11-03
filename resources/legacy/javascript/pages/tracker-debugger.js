document.addEventListener('click', function (e) {
    if (wps_js.isset(wps_js.global, 'request_params', 'page') &&
        (wps_js.global.request_params.page === "privacy-audit" || wps_js.global.request_params.page === "tracker-debugger")) {

         if (e.target.closest('.js-openModal-wps-modal-privacy-audit-confirmation') || (e.target.closest('a[data-action]'))) {
                return;
            }

         const header = e.target.closest('.wps-audit-card__header');
        if (header) {
            e.preventDefault();
            const auditCard = header.closest('.wps-audit-card');

            if (auditCard) {
                const allAuditCards = document.querySelectorAll('.wps-audit-card');

                const isExpanded = auditCard.classList.contains('wps-audit-card--expanded');

                allAuditCards.forEach(card => {
                    if (card !== auditCard) {
                        card.classList.remove('wps-audit-card--expanded');
                        card.classList.add('wps-audit-card--collapsed');
                        const toggleButton = card.querySelector('.wps-audit-card__toggle');
                        if (toggleButton) {
                            toggleButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                if (isExpanded) {
                    auditCard.classList.remove('wps-audit-card--expanded');
                    auditCard.classList.add('wps-audit-card--collapsed');
                    const toggleButton = auditCard.querySelector('.wps-audit-card__toggle');
                    if (toggleButton) {
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }
                } else {
                    auditCard.classList.remove('wps-audit-card--collapsed');
                    auditCard.classList.add('wps-audit-card--expanded');
                    const toggleButton = auditCard.querySelector('.wps-audit-card__toggle');
                    if (toggleButton) {
                        toggleButton.setAttribute('aria-expanded', 'true');
                    }
                }
            }
        }
    }
});
