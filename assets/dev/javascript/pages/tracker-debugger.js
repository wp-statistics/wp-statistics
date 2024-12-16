if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "tracker-debugger") {
    // Select all toggle buttons within the audit cards
    const toggleButtons = document.querySelectorAll('.wps-audit-card__toggle');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            // Get the parent card element
            const auditCard = this.closest('.wps-audit-card');

            // Check if the card is expanded
            const isExpanded = auditCard.classList.contains('wps-audit-card--expanded');

            // Toggle classes on the parent card
            if (isExpanded) {
                auditCard.classList.remove('wps-audit-card--expanded');
                auditCard.classList.add('wps-audit-card--collapsed');
                this.setAttribute('aria-expanded', 'false');
            } else {
                auditCard.classList.remove('wps-audit-card--collapsed');
                auditCard.classList.add('wps-audit-card--expanded');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });


}