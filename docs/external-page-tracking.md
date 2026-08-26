# Tracking standalone pages on the WordPress domain

WP Statistics can record page views from HTML pages and small applications that run outside WordPress but share its exact origin (scheme, host, and port). These are core page views; a premium custom-event integration is not required.

## Endpoint

Send a `POST` request to:

```text
/wp-json/wp-statistics/v2/external-page
```

If WordPress is installed in a subdirectory or uses a customized REST prefix, use the URL returned by `rest_url('wp-statistics/v2/external-page')` instead.

The request accepts:

| Parameter | Required | Description |
| --- | --- | --- |
| `page_uri` | Yes | A same-origin path such as `/tools/meshtastic-lab`. The query string is optional. |
| `referred` | No | The referring URL, when available. |

## Browser snippet

Replace the endpoint if the WordPress REST API does not live at `/wp-json/`. Run this code only after any consent tool used by the site has allowed statistics tracking.

```html
<script>
(function () {
    var dnt = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);
    if (dnt === 1) {
        return;
    }

    var data = new URLSearchParams({
        page_uri: window.location.pathname + window.location.search,
        referred: document.referrer
    });

    navigator.sendBeacon('/wp-json/wp-statistics/v2/external-page', data);
}());
</script>
```

A successful request returns:

```json
{"status": true}
```

The view appears in page reports as an **External Page**, keyed by `page_uri`. It passes through the normal WP Statistics visitor, page, privacy, and exclusion pipeline instead of creating a WordPress 404.

## Security and privacy model

- The endpoint requires the browser-supplied `Origin` header to match the WordPress origin exactly. Browsers prevent scripts on another origin from forging this header, so cross-site page-view injection receives HTTP 403.
- Absolute `page_uri` values are accepted only when they use the same origin; reports store only the path and optional query string.
- No WordPress salt or reusable request signature is exposed to the standalone page. The endpoint is intentionally limited to same-origin pages instead.
- As with any browser analytics endpoint, the origin check is not proof of a human visitor: non-browser clients can synthesize HTTP headers. Existing bot and exclusion rules still apply.
- Server-side Do Not Track handling remains active. The snippet also checks DNT before sending, and must be called only after the site's consent requirements are satisfied.
- Tracking responses include the same no-cache and no-index headers as the standard hit endpoint.

Avoid placing personal data, secrets, or other sensitive values in `page_uri` query strings because the URI is stored in analytics reports.
