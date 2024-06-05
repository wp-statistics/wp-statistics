<?php

namespace WP_STATISTICS;

class User
{
    /**
     * Default Manage User Capability
     *
     * @var string
     */
    public static $default_manage_cap = 'manage_options';

    public static $dateFilterMetaKey = 'wp_statistics_date_filter';

    /**
     * Check User is Logged in WordPress
     *
     * @return mixed
     */
    public static function is_login()
    {
        return is_user_logged_in();
    }

    /**
     * Get Current User ID
     *
     * @return int
     */
    public static function get_user_id()
    {
        $user_id = 0;
        if (self::is_login() === true) {
            $user_id = get_current_user_id();
        }

        return apply_filters('wp_statistics_user_id', $user_id);
    }

    /**
     * Get User Data
     *
     * @param bool $user_id
     * @return array
     */
    public static function get($user_id = false)
    {

        # Get User ID
        $user_id = $user_id ? $user_id : get_current_user_id();

        # Get User Data
        $user_data = get_userdata($user_id);
        $user_info = get_object_vars($user_data->data);

        # Get User roles
        $user_info['role'] = $user_data->roles;

        # Get User Caps
        $user_info['cap'] = $user_data->caps;

        # Get User Meta
        $user_info['meta'] = array_map(function ($a) {
            return $a[0];
        }, get_user_meta($user_id));

        return $user_info;
    }

    /**
     * Get Full name of User
     *
     * @param $user_id
     * @return string
     */
    public static function get_name($user_id)
    {

        # Get User Info
        $user_info = self::get($user_id);

        # check display name
        if ($user_info['display_name'] != "") {
            return $user_info['display_name'];
        }

        # Check First and Last name
        if ($user_info['meta']['first_name'] != "") {
            return $user_info['meta']['first_name'] . " " . $user_info['meta']['last_name'];
        }

        # return Username
        return $user_info['user_login'];
    }

    /**
     * Check User Exist By id
     *
     * @param $user_id
     * @return bool
     * We Don`t Use get_userdata or get_user_by function, because We need only count nor UserData object.
     */
    public static function exists($user_id)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE `ID` = %d", $user_id));
        return $count > 0;
    }

    /**
     * Returns WordPress' roles names + an extra "Anonymous user" index.
     *
     * @return  array
     */
    public static function get_role_list()
    {
        global $wp_roles;

        $rolesNames   = $wp_roles->get_names();
        $rolesNames[] = 'Anonymous user';

        return $rolesNames;
    }

    /**
     * Validation User Capability
     *
     * @default manage_options
     * @param string $capability Capability
     * @return string 'manage_options'
     */
    public static function ExistCapability($capability)
    {
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return self::$default_manage_cap;
        }

        foreach ($wp_roles->roles as $role) {
            $cap_list = $role['capabilities'];

            foreach ($cap_list as $key => $cap) {
                if ($capability == $key) {
                    return $capability;
                }
            }
        }

        return self::$default_manage_cap;
    }

    /**
     * Check User Access To WP Statistics Admin
     *
     * @param string $type [manage | read ]
     * @param string|boolean $export
     * @return bool
     */
    public static function Access($type = 'both', $export = false)
    {

        //List Of Default Cap
        $list = array(
            'manage' => array('manage_capability', 'manage_options'),
            'read'   => array('read_capability', 'manage_options')
        );

        //User User Cap
        $cap = 'both';
        if (!empty($type) and array_key_exists($type, $list)) {
            $cap = $type;
        }

        //Check Export Cap name or Validation current_can_user
        if ($export == "cap") {
            return self::ExistCapability(Option::get($list[$cap][0], $list[$cap][1]));
        }

        //Check Access
        switch ($type) {
            case "manage":
            case "read":
                return current_user_can(self::ExistCapability(Option::get($list[$cap][0], $list[$cap][1])));
                break;
            case "both":
                foreach (array('manage', 'read') as $c) {
                    if (self::Access($c) === true) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    /**
     * Get Date Filter
     *
     * @param $metaKey
     * @param $defaultValue
     * @return mixed
     */
    public static function getDefaultDateFilter($metaKey, $defaultValue)
    {
        // get user id
        $userID = self::get_user_id();

        // check user id
        if (empty($userID)) {
            return $defaultValue;
        }

        // get meta
        $meta = get_user_meta($userID, self::$dateFilterMetaKey, true);

        // return
        return !empty($meta[$metaKey]) ? $meta[$metaKey] : $defaultValue;
    }

    /**
     * Save Date Filter
     *
     * @param $metaKey
     * @param $value
     * @return void
     */
    public static function saveDefaultDateFilter($metaKey, $defaults)
    {
        // get user id
        $userID = self::get_user_id();

        // check user id
        if (empty($userID)) {
            return;
        }

        // check defaults
        if (empty($defaults)) {
            return;
        }

        // check if type and filter exists
        if (!isset($defaults['type']) or !isset($defaults['filter'])) {
            return;
        }

        // check type
        if ($defaults['type'] == 'ago') {
            return;;
        }

        // get meta
        $meta = get_user_meta($userID, self::$dateFilterMetaKey, true);

        // check meta
        if (empty($meta)) {
            $meta = array();
        }

        // prepare value
        $value = $defaults['type'] . '|' . $defaults['filter'];
        if ($defaults['filter'] == 'custom') {
            $value .= ':' . $defaults['from'] . ':' . $defaults['to'];
        }

        // update meta value
        $meta[$metaKey] = sanitize_text_field($value);

        // save meta
        update_user_meta($userID, self::$dateFilterMetaKey, $meta);
    }

}