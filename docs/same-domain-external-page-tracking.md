# Track standalone pages on the WordPress domain

Use the existing WP Statistics hit route to record a view from a standalone HTML/PHP page or small tool that shares the WordPress domain:

```text
POST /wp-json/wp-statistics/v2/hit
```

This keeps the request in the normal WP Statistics tracking, privacy, and exclusion pipeline. Do not use intentional 404 requests as counters, and do not add a second public tracking endpoint for this use case.

## Before you start

1. Enable **Cache Compatibility** in WP Statistics. The `/hit` REST route is registered only while that option is enabled.
2. Use `rest_url('wp-statistics/v2/hit')` when PHP can render the endpoint. `/wp-json/` may differ when WordPress is installed in a subdirectory or the REST prefix is customized.
3. Send these fields as form data:

| Field | Value |
| --- | --- |
| `wp_statistics_hit` | `1` |
| `source_type` | The page context, for example `home` |
| `source_id` | The matching numeric ID, or `0` when there is no WordPress object |
| `search_query` | A base64-encoded search value, or an empty string |
| `page_uri` | The base64-encoded standalone path and query string |
| `referred` | The base64-encoded referrer, or an empty string |
| `signature` | A server-generated signature for `[source_type, source_id]` unless a narrowly scoped exception is installed |
| `endpoint` | `hit` |

See the existing [headless tracking guide](https://wp-statistics.com/2024/05/how-to-track-visitor-statistics-in-headless-wordpress-themes-using-wp-statistics/) for the request format and [request-signature guide](https://wp-statistics.com/resources/managing-request-signatures/) for the signature model.

## Preferred: generate the signature on the server

When the standalone page is PHP and can load WordPress, generate the tracking context and signature there. Never copy the WordPress salt into browser JavaScript.

```php
<?php
// Adjust this path for the site. Loading WordPress keeps the salt server-side.
require_once dirname(__DIR__) . '/wp-load.php';

use WP_Statistics\Utils\Signature;

$sourceType = 'home';
$sourceId   = 0;
$pageUri    = '/tools/calculator.php';
$referrer   = isset($_SERVER['HTTP_REFERER']) ? wp_unslash($_SERVER['HTTP_REFERER']) : '';

$trackingRequest = [
    'wp_statistics_hit' => '1',
    'source_type'       => $sourceType,
    'source_id'         => (string) $sourceId,
    'search_query'      => '',
    'page_uri'          => base64_encode($pageUri),
    'referred'          => base64_encode($referrer),
    'signature'         => Signature::generate([$sourceType, $sourceId]),
    'endpoint'          => 'hit',
];
?>
<script>
(function () {
    var endpoint = <?php echo wp_json_encode(rest_url('wp-statistics/v2/hit')); ?>;
    var payload = new URLSearchParams(<?php echo wp_json_encode($trackingRequest); ?>);

    navigator.sendBeacon(endpoint, payload);
}());
</script>
```

Run the browser call only after the site's consent tool allows statistics tracking, and preserve any Do Not Track behavior required by the site's WP Statistics configuration.

## Controlled static HTML: scope a signature exception

A fully static page cannot generate a valid signature without exposing a server secret. WP Statistics provides the `wp_statistics_request_signature_enabled` filter for site-specific exceptions, but a global `__return_false` callback would make every hit request unsigned. Do not use one.

Instead, put a narrowly scoped callback in a small site-specific plugin. The example below disables verification only for:

- `POST /wp-statistics/v2/hit`;
- the exact WordPress origin; and
- two explicitly allowed standalone paths.

```php
<?php
/**
 * Allow unsigned hits only for selected same-domain static pages.
 */
function mysite_standalone_hit_signature_required($enabled)
{
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return $enabled;
    }

    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(wp_unslash($_SERVER['REQUEST_METHOD'])) : '';
    $route  = isset($GLOBALS['wp']->query_vars['rest_route'])
        ? '/' . ltrim($GLOBALS['wp']->query_vars['rest_route'], '/')
        : '';

    // Support sites that use ?rest_route=... instead of pretty permalinks.
    if (!$route && isset($_GET['rest_route']) && is_string($_GET['rest_route'])) {
        $route = '/' . ltrim(wp_unslash($_GET['rest_route']), '/');
    }

    if ('POST' !== $method || '/wp-statistics/v2/hit' !== untrailingslashit($route)) {
        return $enabled;
    }

    $origin = isset($_SERVER['HTTP_ORIGIN']) && is_string($_SERVER['HTTP_ORIGIN'])
        ? wp_unslash($_SERVER['HTTP_ORIGIN'])
        : '';
    $originParts = wp_parse_url($origin);
    $homeParts   = wp_parse_url(home_url('/'));

    if (!is_array($originParts) || !is_array($homeParts)) {
        return $enabled;
    }

    $originScheme = isset($originParts['scheme']) ? strtolower($originParts['scheme']) : '';
    $homeScheme   = isset($homeParts['scheme']) ? strtolower($homeParts['scheme']) : '';
    $originHost   = isset($originParts['host']) ? strtolower($originParts['host']) : '';
    $homeHost     = isset($homeParts['host']) ? strtolower($homeParts['host']) : '';
    $originPort   = isset($originParts['port']) ? (int) $originParts['port'] : ('https' === $originScheme ? 443 : 80);
    $homePort     = isset($homeParts['port']) ? (int) $homeParts['port'] : ('https' === $homeScheme ? 443 : 80);

    if ($originScheme !== $homeScheme || $originHost !== $homeHost || $originPort !== $homePort) {
        return $enabled;
    }

    $encodedPageUri = isset($_POST['page_uri']) && is_string($_POST['page_uri'])
        ? wp_unslash($_POST['page_uri'])
        : '';
    $pageUri = base64_decode($encodedPageUri, true);
    $path    = false !== $pageUri ? wp_parse_url($pageUri, PHP_URL_PATH) : false;

    $allowedPaths = [
        '/tools/calculator.html',
        '/tools/status.html',
    ];

    if (!is_string($path) || !in_array($path, $allowedPaths, true)) {
        return $enabled;
    }

    return false;
}
add_filter(
    'wp_statistics_request_signature_enabled',
    'mysite_standalone_hit_signature_required'
);
```

The corresponding static page can send the unsigned request after consent is granted:

```html
<script>
(function () {
    function base64(value) {
        var bytes = new TextEncoder().encode(value);
        var binary = '';

        bytes.forEach(function (byte) {
            binary += String.fromCharCode(byte);
        });

        return btoa(binary);
    }

    var payload = new URLSearchParams({
        wp_statistics_hit: '1',
        source_type: 'home',
        source_id: '0',
        search_query: '',
        page_uri: base64(window.location.pathname + window.location.search),
        referred: base64(document.referrer),
        endpoint: 'hit'
    });

    navigator.sendBeacon('/wp-json/wp-statistics/v2/hit', payload);
}());
</script>
```

### Security limits of a bypass

The browser-controlled `Origin` header blocks ordinary cross-origin JavaScript, but it can be spoofed by a direct HTTP client and is not authentication. The allowlist prevents the bypass from accepting arbitrary page identities, but unsigned requests can still inflate counts for those listed paths.

Keep the path list as small as possible, retain the signature requirement for every other request, and add web-server or application rate limiting when abuse is a concern. Prefer the server-generated signature flow whenever PHP/WordPress context is available. Never publish a global signature bypass, a WordPress salt, or a reusable secret in static JavaScript; those choices create an open hit-injection endpoint.

## Events instead of page views

Use the existing `/hit` route when the standalone URL should appear as a page-like view. For button clicks, form submissions, tool actions, or other interactions, use [Custom Event Tracking](https://wp-statistics.com/resources/custom-event-tracking/) instead of creating synthetic page views.

For WordPress-rendered single-page applications, follow the [SPA page-view guide](https://wp-statistics.com/resources/tracking-page-views-in-spas-and-the-wordpress-interactivity-api-with-wp-statistics/) and its `wp_statistics_resolve_page_from_uri` filter rather than installing the static-page bypass above.
