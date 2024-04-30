<?php

namespace WP_STATISTICS;

/**
 * Class WP_Statistics_TinyMCE
 */
class TinyMCE
{

    /**
     * Setup an TinyMCE action to close the notice on the overview page.
     */
    public function __construct()
    {

        // Add Filter TinyMce Editor
        add_action('admin_head', array($this, 'wp_statistic_add_my_tc_button'));

        // Add TextLang
        add_action('admin_footer-widgets.php', array($this, 'my_post_edit_page_footer'), 999);
    }

    /*
     * Language List Text Domain
     */
    static public function lang()
    {
        if (!class_exists('_WP_Editors')) {
            require(ABSPATH . WPINC . '/class-wp-editor.php');
        }

        $strings = array(
            'insert'         => __('WP Statistics Shortcodes', 'wp-statistics'),
            'stat'           => __('Statistics', 'wp-statistics'),
            'usersonline'    => __('Online Users', 'wp-statistics'),
            'visits'         => __('Views', 'wp-statistics'),
            'visitors'       => __('Visitors', 'wp-statistics'),
            'pagevisits'     => __('Number of Page Views', 'wp-statistics'),
            'searches'       => __('Searches', 'wp-statistics'),
            'postcount'      => __('Total Number of Posts', 'wp-statistics'),
            'pagecount'      => __('Total Number of Pages', 'wp-statistics'),
            'commentcount'   => __('Total Number of Comments', 'wp-statistics'),
            'spamcount'      => __('Total Count of Spam Comments', 'wp-statistics'),
            'usercount'      => __('Total Number of Users', 'wp-statistics'),
            'postaverage'    => __('Average Number of Posts', 'wp-statistics'),
            'commentaverage' => __('Average Number of Comments', 'wp-statistics'),
            'useraverage'    => __('Average Number of Users', 'wp-statistics'),
            'lpd'            => __('Date of the Latest Post', 'wp-statistics'),
            'referrer'       => __('Referrer', 'wp-statistics'),
            'help_stat'      => __('Choose the Desired Statistics from the Following Options.', 'wp-statistics'),
            'time'           => __('Time', 'wp-statistics'),
            'se'             => __('Select item ...', 'wp-statistics'),
            'today'          => __('Today', 'wp-statistics'),
            'yesterday'      => __('Yesterday', 'wp-statistics'),
            'week'           => __('Week', 'wp-statistics'),
            'month'          => __('Month', 'wp-statistics'),
            'year'           => __('Year', 'wp-statistics'),
            'total'          => __('Total', 'wp-statistics'),
            'help_time'      => __('Select the Time Frame for the Statistics', 'wp-statistics'),
            'provider'       => __('Provider', 'wp-statistics'),
            'help_provider'  => __('Select a Search Provider for Detailed Statistics.', 'wp-statistics'),
            'format'         => __('Display Format', 'wp-statistics'),
            'help_format'    => __('Choose Number Format: International (i18n), English, or None.', 'wp-statistics'),
            'id'             => __('ID', 'wp-statistics'),
            'help_id'        => __('Specify Post/Page ID for Detailed Page Statistics.', 'wp-statistics'),
        );

        $locale     = \_WP_Editors::$mce_locale;
        $translated = 'tinyMCE.addI18n("' . $locale . '.wp_statistic_tinymce_plugin", ' . wp_json_encode($strings) . ");\n";

        return array('locale' => $locale, 'translate' => $translated);
    }

    /*
     * Add Filter TinyMCE
     */
    public function wp_statistic_add_my_tc_button()
    {
        global $typenow;

        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // verify the post type
        if (!in_array($typenow, array('post', 'page'))) {
            return;
        }

        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') == 'true') {
            add_filter("mce_external_plugins", array($this, 'wp_statistic_add_tinymce_plugin'));
            add_filter('mce_buttons', array($this, 'wp_statistic_register_my_tc_button'));
            add_filter('mce_external_languages', array($this, 'wp_statistic_tinymce_plugin_add_locale'));
        }
    }

    /*
     * Add Js Bottun to Editor
     */
    public function wp_statistic_add_tinymce_plugin($plugin_array)
    {
        $plugin_array['wp_statistic_tc_button'] = Admin_Assets::url('tinymce.min.js');

        return $plugin_array;
    }

    /*
     * Push Button to TinyMCE Advance
     */
    public function wp_statistic_register_my_tc_button($buttons)
    {
        array_push($buttons, "wp_statistic_tc_button");

        return $buttons;
    }

    /*
     * Add Lang Text Domain
     */
    public function wp_statistic_tinymce_plugin_add_locale($locales)
    {
        $locales ['wp-statistic-tinymce-plugin'] = WP_STATISTICS_DIR . 'includes/admin/TinyMCE/locale.php';

        return $locales;
    }

    /*
     * Add Lang for Text Widget
     */
    public function my_post_edit_page_footer()
    {
        echo '
        <script type="text/javascript">
        jQuery( document ).on( \'tinymce-editor-setup\', function( event, editor ) {
                editor.settings.toolbar1 += \',wp_statistic_tc_button\';
        });
        ';
        $lang = TinyMCE::lang();
        echo $lang['translate']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo 'tinyMCEPreInit.load_ext("' . rtrim(WP_STATISTICS_URL, "/") . '", "' . esc_html($lang['locale']) . '"); </script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
    }
}

new TinyMCE;