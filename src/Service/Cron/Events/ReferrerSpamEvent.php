<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Globals\Option;

/**
 * Referrer Spam Update Cron Event.
 *
 * Updates the referrer spam list.
 *
 * @since 15.0.0
 */
class ReferrerSpamEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_referrerspam_hook';

    /**
     * @var string
     */
    protected $recurrence = 'weekly';

    /**
     * Check if referrer spam update should be scheduled.
     *
     * @return bool
     */
    protected function shouldSchedule()
    {
        return (bool) Option::getValue('schedule_referrerspam');
    }

    /**
     * Execute the referrer spam update.
     *
     * @return void
     */
    public function execute()
    {
        // The referrer spam list update is handled by the ReferralsDatabase service
        // This hook allows addons/extensions to hook into the update process
        do_action('wp_statistics_update_referrer_spam_list');
    }
}
