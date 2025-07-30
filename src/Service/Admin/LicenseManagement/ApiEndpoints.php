<?php
namespace WP_Statistics\Service\Admin\LicenseManagement;

class ApiEndpoints
{
    public const BASE_URL = WP_STATISTICS_SITE_URL . '/wp-json/wp-license-manager/v1';
    public const PRODUCT_LIST = self::BASE_URL . '/product/list';
    public const PRODUCT_DOWNLOAD = self::BASE_URL . '/product/download';
    public const LICENSE_STATUS = self::BASE_URL . '/license/status';
}