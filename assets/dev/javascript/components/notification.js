jQuery(document).ready(function () {
    const wpsNotificationButtons = document.querySelectorAll('.js-wps-open-notification');
    const wpsSidebar = document.querySelector('.wps-notification-sidebar');
    const wpsOverlay = document.querySelector('.wps-notification-sidebar__overlay');
    const body = document.body;
    const tabs = document.querySelectorAll('.wps-notification-sidebar__tab');
    const wpsCloseNotificationMenu = document.querySelector('.wps-notification-sidebar__close');
    const tabPanes = document.querySelectorAll('.wps-notification-sidebar__tab-pane');
    const dismissAllBtn = document.querySelector(".wps-notification-sidebar__dismiss-all");


    // Promo notices: countdown and copy-code button.
    const wpsCountdowns = document.querySelectorAll('.wps-notification-sidebar__countdown');
    if (wpsCountdowns.length > 0) {
        const renderCountdown = function (el) {
            const left = Math.max(0, new Date(el.getAttribute('data-ends-at')).getTime() - Date.now());
            const d = Math.floor(left / 86400000);
            const h = Math.floor(left % 86400000 / 3600000);
            const m = Math.floor(left % 3600000 / 60000);
            el.innerHTML = el.getAttribute('data-prefix') + ' <strong>' + d + el.getAttribute('data-d') + ' ' + h + el.getAttribute('data-h') + ' ' + m + el.getAttribute('data-m') + '</strong>';
        };
        wpsCountdowns.forEach(renderCountdown);
        setInterval(function () { wpsCountdowns.forEach(renderCountdown); }, 30000);
    }

    document.querySelectorAll('.wps-notification-sidebar__copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const original = btn.textContent;
            const done = function () {
                btn.textContent = btn.getAttribute('data-copied');
                setTimeout(function () { btn.textContent = original; }, 1500);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(btn.getAttribute('data-code')).then(done);
            }
        });
    });

    // Toggle notification menu
    if (tabs.length > 0 && tabPanes.length > 0) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    t.classList.remove('wps-notification-sidebar__tab--active');
                });
                tabPanes.forEach(function (pane) {
                    pane.classList.remove('wps-notification-sidebar__tab-pane--active');
                });

                const targetTab = tab.getAttribute('data-tab');
                tab.classList.add('wps-notification-sidebar__tab--active');
                document.getElementById(targetTab).classList.add('wps-notification-sidebar__tab-pane--active');
            });
        });
    }

    if (wpsNotificationButtons.length > 0 && wpsSidebar && wpsOverlay) {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);

        wpsNotificationButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                wpsSidebar.classList.toggle('is-active');
                wpsOverlay.classList.toggle('is-active');
                setTimeout(() => {
                    body.classList.toggle('wps-no-scroll');
                }, 250);
            });
        });

        wpsOverlay.addEventListener('click', function () {
            wpsSidebar.classList.remove('is-active');
            wpsOverlay.classList.remove('is-active');
            setTimeout(() => {
                body.classList.remove('wps-no-scroll');
            }, 250);
        });
        if (wpsCloseNotificationMenu) {
            wpsCloseNotificationMenu.addEventListener('click', function () {
                wpsSidebar.classList.remove('is-active');
                wpsOverlay.classList.remove('is-active');
                body.classList.remove('wps-no-scroll');
            });
        }

    }

    const updateDismissAllVisibility = () => {
        const activeTab = document.querySelector(".wps-notification-sidebar__tab--active");
        if (!activeTab) {
            return;
        }

        if (activeTab.dataset.tab === "tab-2") {
            if (dismissAllBtn) dismissAllBtn.style.display = "none";
        } else {
            const activeCards = document.querySelectorAll(
                ".wps-notification-sidebar__cards--active .wps-notification-sidebar__card:not(.wps-notification-sidebar__no-card)"
            );
            const hasNotifications = activeCards.length > 0;
            if (dismissAllBtn) dismissAllBtn.style.display = hasNotifications ? "inline-flex" : "none";
        }
    };

    const checkEmptyNotifications = () => {
        let notificationsHasItems = jQuery('.wps-notifications--has-items');
        let helpNotification = jQuery('.wps-help__notification');
        let activeCards = jQuery('.wps-notification-sidebar__tab-pane--active .wps-notification-sidebar__card:not(.wps-notification-sidebar__no-card)');
        let noCardMessages = jQuery('.wps-notification-sidebar__tab-pane--active .wps-notification-sidebar__no-card');
        let noCardMessage = noCardMessages.first();

        const activeTab = document.querySelector(".wps-notification-sidebar__tab--active");
        const isInboxTab = activeTab && activeTab.dataset.tab === "tab-1";

        if (activeCards.length === 0) {
            noCardMessage.css('display', 'flex');
            if (isInboxTab) {
                helpNotification.hide();
                notificationsHasItems.removeClass('wps-notifications--has-items');
            }
        } else {
            noCardMessage.hide();
        }
        if (noCardMessages.length > 1) {
            noCardMessages.last().hide();
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("wps-notification-sidebar__tab--active"));
            this.classList.add("wps-notification-sidebar__tab--active");
            updateDismissAllVisibility();
            checkEmptyNotifications();
        });
    });

    updateDismissAllVisibility();
    checkEmptyNotifications();

    jQuery(document).on('click', "a.wps-notification-sidebar__dismiss, a.wps-notification-sidebar__dismiss-all", function (e) {
        e.preventDefault();
        let $this = jQuery(this);
        let notificationId = '';

        if ($this.hasClass('wps-notification-sidebar__dismiss')) {
            notificationId = $this.data('notification-id');
        }

        if ($this.hasClass('wps-notification-sidebar__dismiss-all')) {
            notificationId = 'all';
        }


        if (notificationId === 'all') {
            jQuery('.wps-notification-sidebar__cards--active .wps-notification-sidebar__card:not(.wps-notification-sidebar__no-card)').each(function () {
                let $card = jQuery(this);

                jQuery('.wps-notification-sidebar__cards--dismissed').prepend($card.clone().hide().fadeIn(300));

                $card.fadeOut(300, function () {
                    jQuery(this).remove();
                    checkEmptyNotifications();
                });
            });
        } else {
            let $card = $this.closest('.wps-notification-sidebar__card');

            jQuery('.wps-notification-sidebar__cards--dismissed').prepend($card.clone().hide().fadeIn(300));

            $card.fadeOut(300, function () {
                jQuery(this).remove();
                checkEmptyNotifications();
            });

        }
        updateDismissAllVisibility();

        jQuery('.wps-notification-sidebar__cards--dismissed .wps-notification-sidebar__no-card').remove();

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_dismiss_notification',
            'notification_id': notificationId
        }

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {
                if (!success) {
                    console.log(data);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
});