jQuery(document).ready(function () {
    const dropdownToggles = document.querySelectorAll('.wps-admin-header__link--has-dropdown');
    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('is-open');
                toggle.classList.toggle('is-open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        dropdownToggles.forEach(function (toggle) {
            const dropdownMenu = toggle.nextElementSibling;
            if (dropdownMenu && !toggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('is-open');
            }
        });
    });

    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
    // Check if both targetElement and noticeElement exist
    if (targetElement && noticeElement) {
        // Move the notice element after the target element
        targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }

        document.addEventListener('click', function (e) {
            const infoBtn = e.target.closest('.wps-info-trigger');
            if (!infoBtn) return; 
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

            const infoKey = infoBtn.getAttribute('data-info');
            if (!infoKey) return;

            if (window.WPSModalManager && typeof window.WPSModalManager.openFromKey === 'function') {
                window.WPSModalManager.openFromKey(infoKey, infoBtn);
            } else {
                const modalContent = document.querySelector('#modalContents [data-modal-id="' + infoKey + '"]');
                if (!modalContent) return;
                const container = document.getElementById('globalModalContainer');
                if (!container) return;
                container.innerHTML = `
                    <div class="wps-modal-overlay" role="dialog" aria-modal="true">
                        <div class="wps-modal" role="document" tabindex="-1">
                            <div class="wps-modal-body">${modalContent.innerHTML}</div>
                        </div>
                    </div>
                `;
                 container.style.display = 'block';

                const overlay = container.querySelector('.wps-modal-overlay');
                const modalEl = container.querySelector('.wps-modal');
                if (!overlay) return;

                function closeInjectedModal() {
                     container.innerHTML = '';
                    container.style.display = 'none';
                    if (infoBtn && typeof infoBtn.focus === 'function') infoBtn.focus();
                }

                overlay.addEventListener('click', function (ev) {
                     if (!modalEl.contains(ev.target)) closeInjectedModal();
                });

                 if (modalEl && typeof modalEl.focus === 'function') modalEl.focus();
            }
        });
     document.addEventListener('pointerdown', function (e) {
        const infoBtn = e.target.closest('.wps-info-trigger');
        if (!infoBtn) return;
        e.preventDefault();
        e.stopPropagation();
    }, { capture: true });

});


