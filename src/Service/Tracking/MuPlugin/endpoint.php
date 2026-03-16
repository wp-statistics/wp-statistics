<?php
/**
 * WP Statistics Direct File Endpoint (placeholder)
 *
 * This file is copied to mu-plugins/ by MuPluginManager.
 * SHORTINIT-compatible hit recording will be implemented here.
 *
 * @since 15.0.0
 */

// Skip when WordPress auto-loads this as a mu-plugin.
if (defined('ABSPATH')) {
    return;
}

// TODO: Implement SHORTINIT-compatible tracking endpoint.
http_response_code(501);
header('Content-Type: application/json');
echo '{"status":false,"data":"Not implemented"}';
