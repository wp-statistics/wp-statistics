<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\Helper;
use WP_STATISTICS\User;

class UserDecorator
{
    private $user;

    public function __construct($userId)
    {
        $this->user = get_user_by('id', $userId);
    }

    /**
     * Get the visitor's user ID (if logged in).
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->user->ID ?? null;
    }

    /**
     * Retrieves the display name of the visitor.
     *
     * @return string|null The name of the visitor, or null if not available.
     */
    public function getDisplayName()
    {
        return $this->user->display_name ?? null;
    }

    /**
     * Retrieves the email address of the visitor if they are a logged-in user.
     *
     * @return string|null The visitor's email address, or null if not available.
     */
    public function getEmail()
    {
        return $this->user->user_email ?? null;
    }

    /**
     * Retrieves the first role of the visitor.
     *
     * @return string|null The visitor's first role, or null if not available.
     */
    public function getRole()
    {
        return $this->user->roles[0] ?? null;
    }

    /**
     * Retrieves the registered date of the visitor.
     *
     * @return string|null The visitor's registered date, or null if not available.
     */
    public function getRegisteredDate()
    {
        return $this->user->user_registered ? date_i18n(Helper::getDefaultDateFormat(true), strtotime($this->user->user_registered)) : null;
    }

    /**
     * Retrieves the last login date of the visitor.
     *
     * @return string|null The visitor's last login date, or null if not available.
     */
    public function getLastLogin()
    {
        $lastLogin = User::getLastLogin($this->getId());
        return $lastLogin ? date_i18n(Helper::getDefaultDateFormat(true), $lastLogin) : null;
    }
}
