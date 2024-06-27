<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class log_page extends Singleton
{
    /**
     * OverView Page Action
     */
    public function __construct()
    {

        // Load Meta Box List
        add_action('load-' . Menus::get_action_menu_slug('overview'), array($this, 'meta_box_init'));

        // Set default hidden Meta Box
        add_filter('default_hidden_meta_boxes', array($this, 'default_hidden_meta_boxes'), 10, 2);
    }

    /**
     * Define Meta Box
     */
    public function meta_box_init()
    {

        foreach (Meta_Box::getList() as $meta_key => $meta_box) {
            if (Option::check_option_require($meta_box) === true and ((isset($meta_box['disable_overview']) and $meta_box['disable_overview'] === false) || !isset($meta_box['disable_overview']))) {
                add_meta_box(Meta_Box::getMetaBoxKey($meta_key), $meta_box['name'], Meta_Box::LoadMetaBox($meta_key), Menus::get_action_menu_slug('overview'), $meta_box['place'], $control_callback = null, array('widget' => $meta_key));
            }
        }

    }

    /**
     * Display Html Page
     */
    public static function view()
    {
        $args['overview_page_slug'] = Menus::get_action_menu_slug('overview');
        $args['tooltip'] = __('Quickly view your website’s traffic and visitor analytics.', 'wp-statistics');
        $args['real_time_button'] = true;
        $args['title'] =  __('Overview', 'wp-statistics');;
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/overview', 'layout/footer'), $args);
    }

    /**
     * OverView Default Hidden Meta Box
     *
     * @param $hidden | array list of default hidden meta box
     * @param $screen | WordPress `global $current_screen`
     * @return mixed
     */
    public function default_hidden_meta_boxes($hidden, $screen)
    {
        if ($screen->id == Menus::get_action_menu_slug('overview')) {
            foreach (Meta_Box::getList() as $meta_key => $meta_box) {
                if (isset($meta_box['hidden_overview']) and $meta_box['hidden_overview'] === true) {
                    $hidden[] = Meta_Box::getMetaBoxKey($meta_key);
                }
            }
        }
        return $hidden;
    }
}

log_page::instance();