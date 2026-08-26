<?php

namespace WP_STATISTICS\Api\v2;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_Statistics\Components\TrackingResponse;

class ExternalPage extends \WP_STATISTICS\RestAPI
{
    /**
     * External page endpoint.
     *
     * @var string
     */
    public static $endpoint = 'external-page';

    public function __construct()
    {
        parent::__construct();
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register the same-origin external page tracking endpoint.
     */
    public function register_routes()
    {
        register_rest_route(self::$namespace, '/' . self::$endpoint, array(
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'hit_callback'),
                'permission_callback' => array($this, 'check_origin'),
                'args'                => array(
                    'page_uri' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => array(__CLASS__, 'sanitize_page_uri'),
                        'validate_callback' => function ($value) {
                            return self::sanitize_page_uri($value) !== '';
                        },
                    ),
                    'referred' => array(
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_url',
                    ),
                ),
            ),
        ));
    }

    /**
     * Require the browser to be running on the same origin as WordPress.
     *
     * Browsers do not allow scripts to forge the Origin header, which prevents
     * another website from using this public endpoint to inject page views.
     *
     * @param \WP_REST_Request $request
     * @return true|\WP_Error
     */
    public function check_origin($request)
    {
        $origin = $request->get_header('origin');

        if ($origin && self::get_origin($origin) === self::get_origin(home_url())) {
            return true;
        }

        return new \WP_Error(
            'rest_forbidden',
            __('External page tracking is limited to the WordPress site origin.', 'wp-statistics'),
            array('status' => 403)
        );
    }

    /**
     * Normalize a same-domain page URL to the path stored in reports.
     *
     * @param mixed $pageUri
     * @return string
     */
    public static function sanitize_page_uri($pageUri)
    {
        if (!is_string($pageUri)) {
            return '';
        }

        $pageUri = trim(wp_unslash($pageUri));
        $parts   = wp_parse_url($pageUri);

        if ($parts === false) {
            return '';
        }

        if (isset($parts['host'])) {
            if (self::get_origin($pageUri) !== self::get_origin(home_url())) {
                return '';
            }

            $pageUri = isset($parts['path']) ? $parts['path'] : '/';
            if (!empty($parts['query'])) {
                $pageUri .= '?' . $parts['query'];
            }
        } else {
            $pageUri = preg_replace('/#.*$/', '', $pageUri);
        }

        if ($pageUri === '' || $pageUri[0] !== '/') {
            return '';
        }

        return sanitize_url($pageUri);
    }

    /**
     * Record a same-domain external page as a page-like view.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function hit_callback($request)
    {
        if (Helper::dntEnabled()) {
            return $this->response_with_headers(array(
                'status' => false,
                'data'   => __('Do Not Track is enabled.', 'wp-statistics'),
            ));
        }

        $pageUri       = self::sanitize_page_uri($request->get_param('page_uri'));
        $referred      = sanitize_url($request->get_param('referred'));
        $originalInput = $_REQUEST;

        $_REQUEST = array_merge($_REQUEST, array(
            'page_uri'     => base64_encode($pageUri),
            'referred'     => base64_encode($referred),
            'search_query' => '',
            'source_id'    => 0,
            'source_type'  => 'external',
        ));

        $setCurrentPage = function () {
            return array(
                'type'         => 'external',
                'id'           => 0,
                'search_query' => '',
            );
        };
        $setPageUri = function () use ($pageUri) {
            return $pageUri;
        };

        add_filter('wp_statistics_current_page', $setCurrentPage, PHP_INT_MAX);
        add_filter('wp_statistics_page_uri', $setPageUri, PHP_INT_MAX);

        try {
            Helper::validateHitRequest();
            Hits::record();
            $response = array('status' => true);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'data'   => $e->getMessage(),
            );
        } finally {
            remove_filter('wp_statistics_current_page', $setCurrentPage, PHP_INT_MAX);
            remove_filter('wp_statistics_page_uri', $setPageUri, PHP_INT_MAX);
            $_REQUEST = $originalInput;
        }

        return $this->response_with_headers($response);
    }

    /**
     * Add the standard no-index/no-cache headers to a tracking response.
     *
     * @param array $data
     * @return \WP_REST_Response
     */
    private function response_with_headers($data)
    {
        $response = rest_ensure_response($data);
        $response->set_headers(TrackingResponse::getHeaders());

        return $response;
    }

    /**
     * Return a URL's origin (scheme, host, and non-default port).
     *
     * @param string $url
     * @return string
     */
    private static function get_origin($url)
    {
        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $origin = strtolower($parts['scheme']) . '://' . strtolower($parts['host']);
        if (!empty($parts['port'])) {
            $origin .= ':' . (int)$parts['port'];
        }

        return $origin;
    }
}

new ExternalPage();
