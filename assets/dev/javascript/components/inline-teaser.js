/**
 * Inline teaser (views/components/lock-sections/inline-teaser.php): the × closes the teaser at once and
 * saves that for the current user (user meta, nonce-checked AJAX). Delegated, so teasers printed later work too.
 */
(function () {
    function dismiss(teaser) {
        var id = teaser.getAttribute('data-teaser-id');
        var nonce = teaser.getAttribute('data-nonce');

        teaser.hidden = true;

        var global = (typeof wps_js !== 'undefined' && wps_js.global) ? wps_js.global : {};
        var ajaxUrl = global.ajax_url || ((global.admin_url || '/wp-admin/') + 'admin-ajax.php');

        if (!id || !nonce || typeof window.fetch !== 'function') return;

        var body = new URLSearchParams();
        body.append('action', 'wp_statistics_dismiss_teaser');
        body.append('teaser_id', id);
        body.append('wps_nonce', nonce);

        window.fetch(ajaxUrl, {method: 'POST', credentials: 'same-origin', body: body}).catch(function () {
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest('.js-wps-inline-teaser-close') : null;
        if (!button) return;

        var teaser = button.closest('.js-wps-inline-teaser');
        if (!teaser) return;

        event.preventDefault();
        dismiss(teaser);
    });

    if (typeof window !== 'undefined') {
        window.wpsInlineTeaserDismiss = dismiss;
    }
})();
