<?php

namespace WP_Statistics\Service\Messaging;

use WP_Statistics\Utils\Environment;
use WP_Statistics\Components\Option;

class MessagingHelper
{
    /**
     * Get the admin email for notifications with fallback to site admin email.
     *
     * @return string The notification email address.
     */
    public static function getEmailNotification()
    {
        $email = Option::getValue('email_list');
        if (empty($email) || !is_email($email)) {
            $email = Environment::getAdminEmail();
            if (is_email($email)) {
                Option::updateValue('email_list', $email);
            }
        }
        return $email;
    }
}
