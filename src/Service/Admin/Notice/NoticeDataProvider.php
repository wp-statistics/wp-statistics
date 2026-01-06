<?php

namespace WP_Statistics\Service\Admin\Notice;

use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\Notice\NoticeManager;

/**
 * Notice Data Provider.
 *
 * Provides notice data for React pages via localization.
 *
 * @since 15.0.0
 */
class NoticeDataProvider implements LocalizeDataProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'notices';
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return [
            'items'      => NoticeManager::getDataForReact(),
            'dismissUrl' => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('wp_statistics_dismiss_notice'),
        ];
    }
}
