<?php
/**
 * React SPA mount point.
 *
 * Single entry point for the entire WP Statistics v15 React application.
 * All pages (Dashboard, Settings, Tools, etc.) are rendered by the React
 * SPA using hash-based routing (#/overview, #/settings/general, #/tools/system-info).
 *
 * @since 15.0.0
 */

defined('ABSPATH') || exit;
?>
<div id="wp-statistics-app" class="postbox-container"></div>
