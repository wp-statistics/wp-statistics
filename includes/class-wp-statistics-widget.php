<?php

/**
 * WP Statistics Widget
 */
class WP_Statistics_Widget extends \WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        parent::__construct(
            'WP_Statistics_Widget', // Base ID
            __('Statistics', 'wp-statistics'), // Name
            array('description' => __('Show site stats in sidebar.', 'wp-statistics')) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        extract($args);
        $widget_options = WP_STATISTICS\Option::get('widget');

        if (!is_array($widget_options)) {
            return;
        }

        echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $before_title . esc_attr($widget_options['name_widget']) . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<ul>';

        if ($widget_options['useronline_widget']) {
            echo '<li>';
            echo '<label>' . __('Online Users', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_useronline()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Today\'s Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('today')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tvisitor_widget']) {
            echo '<li>';
            echo '<label>' . __('Today\'s Visitors', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visitor('today', null, true)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['yvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Yesterday\'s Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('yesterday')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['yvisitor_widget']) {
            echo '<li>';
            echo '<label>' . __('Yesterday\'s Visitors', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visitor('yesterday', null, true)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['wvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Last 7 Days Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('week')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['mvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Last 30 Days Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('month')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ysvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Last 365 Days Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('year')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ttvisit_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Visits', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visit('total')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ttvisitor_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Visitors', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_visitor('total', null, true)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tpviews_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Page Views', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_pages('total', null, get_queried_object_ID())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ser_widget']) {
            echo '<li>';
            echo '<label>' . __('Search Engine Referrals', 'wp-statistics') . ': </label>';
            echo number_format_i18n(wp_statistics_searchengine($widget_options['select_se'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tp_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Posts', 'wp-statistics') . ': </label>';
            echo number_format_i18n(WP_STATISTICS\Helper::getCountPosts()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tpg_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Pages', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getCountPages()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tc_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Comments', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getCountComment()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ts_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Spams', 'wp-statistics') . ': </label>';
            echo \WP_STATISTICS\Helper::getCountSpam(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['tu_widget']) {
            echo '<li>';
            echo '<label>' . __('Total Users', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getCountUsers()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ap_widget']) {
            echo '<li>';
            echo '<label>' . __('Post Average', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getAveragePost()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['ac_widget']) {
            echo '<li>';
            echo '<label>' . __('Comment Average', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getAverageComment()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['au_widget']) {
            echo '<li>';
            echo '<label>' . __('User Average', 'wp-statistics') . ': </label>';
            echo number_format_i18n(\WP_STATISTICS\Helper::getAverageRegisterUser()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        if ($widget_options['lpd_widget']) {
            echo '<li>';
            echo '<label>' . __('Last Post Date', 'wp-statistics') . ': </label>';
            echo \WP_STATISTICS\Helper::getLastPostDate(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</li>';
        }

        echo '</ul>';
        echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        if (array_key_exists('wp_statistics_control_widget_submit', $new_instance)) {
            $keys = array(
                'name_widget'       => 'name_widget',
                'useronline_widget' => 'useronline_widget',
                'tvisit_widget'     => 'tvisit_widget',
                'tvisitor_widget'   => 'tvisitor_widget',
                'yvisit_widget'     => 'yvisit_widget',
                'yvisitor_widget'   => 'yvisitor_widget',
                'wvisit_widget'     => 'wvisit_widget',
                'mvisit_widget'     => 'mvisit_widget',
                'ysvisit_widget'    => 'ysvisit_widget',
                'ttvisit_widget'    => 'ttvisit_widget',
                'ttvisitor_widget'  => 'ttvisitor_widget',
                'tpviews_widget'    => 'tpviews_widget',
                'ser_widget'        => 'ser_widget',
                'select_se'         => 'select_se',
                'tp_widget'         => 'tp_widget',
                'tpg_widget'        => 'tpg_widget',
                'tc_widget'         => 'tc_widget',
                'ts_widget'         => 'ts_widget',
                'tu_widget'         => 'tu_widget',
                'ap_widget'         => 'ap_widget',
                'ac_widget'         => 'ac_widget',
                'au_widget'         => 'au_widget',
                'lpd_widget'        => 'lpd_widget',
                'select_lps'        => 'select_lps',
            );

            foreach ($keys as $key => $post) {
                if (array_key_exists($post, $new_instance)) {
                    $widget_options[$key] = $new_instance[$post];
                } else {
                    $widget_options[$key] = '';
                }
            }

            WP_STATISTICS\Option::update('widget', $widget_options);
        }

        return array();
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     *
     * @return string|void
     */
    public function form($instance)
    {
        $widget_options = WP_STATISTICS\Option::get('widget');

        ?>
        <p>
            <label for="name_widget"><?php _e('Name', 'wp-statistics'); ?>:
                <input id="name_widget" name="<?php echo $this->get_field_name('name_widget'); ?>" type="text" value="<?php if (isset($widget_options['name_widget'])) echo esc_attr($widget_options['name_widget']); ?>"/>
            </label>
        </p>

        <?php _e('Items', 'wp-statistics'); ?>:<br/>
        <ul>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('useronline_widget')); ?>" name="<?php echo $this->get_field_name('useronline_widget'); ?>" <?php if (isset($widget_options['useronline_widget'])) checked('on', $widget_options['useronline_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('useronline_widget')); ?>"><?php _e('Online Users', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tvisit_widget')); ?>" name="<?php echo $this->get_field_name('tvisit_widget'); ?>" <?php if (isset($widget_options['tvisit_widget'])) checked('on', $widget_options['tvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tvisit_widget')); ?>"><?php _e('Today\'s Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tvisitor_widget')); ?>" name="<?php echo $this->get_field_name('tvisitor_widget'); ?>" <?php if (isset($widget_options['tvisitor_widget'])) checked('on', $widget_options['tvisitor_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tvisitor_widget')); ?>"><?php _e('Today\'s Visitors', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('yvisit_widget')); ?>" name="<?php echo $this->get_field_name('yvisit_widget'); ?>" <?php if (isset($widget_options['yvisit_widget'])) checked('on', $widget_options['yvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('yvisit_widget')); ?>"><?php _e('Yesterday\'s Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('yvisitor_widget')); ?>" name="<?php echo $this->get_field_name('yvisitor_widget'); ?>" <?php if(isset($widget_options['yvisitor_widget'])) checked('on', $widget_options['yvisitor_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('yvisitor_widget')); ?>"><?php _e('Yesterday\'s Visitors', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('wvisit_widget')); ?>" name="<?php echo $this->get_field_name('wvisit_widget'); ?>" <?php if(isset($widget_options['wvisit_widget'])) checked('on', $widget_options['wvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('wvisit_widget')); ?>"><?php _e('Last 7 Days Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('mvisit_widget')); ?>" name="<?php echo $this->get_field_name('mvisit_widget'); ?>" <?php if(isset($widget_options['mvisit_widget'])) checked('on', $widget_options['mvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('mvisit_widget')); ?>"><?php _e('Last 30 Days Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ysvisit_widget')); ?>" name="<?php echo $this->get_field_name('ysvisit_widget'); ?>" <?php if(isset($widget_options['ysvisit_widget'])) checked('on', $widget_options['ysvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ysvisit_widget')); ?>"><?php _e('Last 365 Days Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ttvisit_widget')); ?>" name="<?php echo $this->get_field_name('ttvisit_widget'); ?>" <?php if(isset($widget_options['ttvisit_widget'])) checked('on', $widget_options['ttvisit_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ttvisit_widget')); ?>"><?php _e('Total Visits', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ttvisitor_widget')); ?>" name="<?php echo $this->get_field_name('ttvisitor_widget'); ?>" <?php if(isset($widget_options['ttvisitor_widget'])) checked('on', $widget_options['ttvisitor_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ttvisitor_widget')); ?>"><?php _e('Total Visitors', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tpviews_widget')); ?>" name="<?php echo $this->get_field_name('tpviews_widget'); ?>" <?php if(isset($widget_options['tpviews_widget'])) checked('on', $widget_options['tpviews_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tpviews_widget')); ?>"><?php _e('Total Page Views', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ser_widget')); ?>" class="ser_widget" name="<?php echo $this->get_field_name('ser_widget'); ?>" <?php if(isset($widget_options['ser_widget'])) checked('on', $widget_options['ser_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ser_widget')); ?>"><?php _e('Search Engine Referrals', 'wp-statistics'); ?></label>

                <p id="ser_option" style="<?php if (isset($widget_options['ser_widget']) and !$widget_options['ser_widget']) {
                    echo "display: none;";
                } ?>">
                    <?php _e('Select type of search engine', 'wp-statistics'); ?>:<br/>
                    <?php
                    $search_engines = WP_STATISTICS\SearchEngine::getList();

                    foreach ($search_engines as $se) {
                        echo '<input type="radio" id="select_' . esc_html($se['tag']) . '" name="select_se" value="' . esc_html($se['tag']) . '" ';
                        if (isset($widget_options['select_se'])) checked($se['tag'], $widget_options['select_se']);
                        echo "/>\n";
                        echo '<label for="' . esc_html($se['name']) . '">' . esc_html($se['translated']) . "</label>\n";
                        echo "\n";
                    }
                    ?>
                    <input type="radio" id="select_all" name="select_se" value="all" <?php if (isset($widget_options['select_se'])) checked('all', $widget_options['select_se']); ?>/>
                    <label for="select_all"><?php _e('All', 'wp-statistics'); ?></label>
                </p>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tp_widget')); ?>" name="<?php echo $this->get_field_name('tp_widget'); ?>" <?php if (isset($widget_options['tp_widget'])) checked('on', $widget_options['tp_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tp_widget')); ?>"><?php _e('Total Posts', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tpg_widget')); ?>" name="<?php echo $this->get_field_name('tpg_widget'); ?>" <?php if (isset($widget_options['tpg_widget'])) checked('on', $widget_options['tpg_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tpg_widget')); ?>"><?php _e('Total Pages', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tc_widget')); ?>" name="<?php echo $this->get_field_name('tc_widget'); ?>" <?php if (isset($widget_options['tc_widget'])) checked('on', $widget_options['tc_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tc_widget')); ?>"><?php _e('Total Comments', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ts_widget')); ?>" name="<?php echo $this->get_field_name('ts_widget'); ?>" <?php if (isset($widget_options['ts_widget'])) checked('on', $widget_options['ts_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ts_widget')); ?>"><?php _e('Total Spams', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('tu_widget')); ?>" name="<?php echo $this->get_field_name('tu_widget'); ?>" <?php if (isset($widget_options['tu_widget'])) checked('on', $widget_options['tu_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('tu_widget')); ?>"><?php _e('Total Users', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ap_widget')); ?>" name="<?php echo $this->get_field_name('ap_widget'); ?>" <?php if (isset($widget_options['ap_widget'])) checked('on', $widget_options['ap_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ap_widget')); ?>"><?php _e('Post Average', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('ac_widget')); ?>" name="<?php echo $this->get_field_name('ac_widget'); ?>" <?php if (isset($widget_options['ac_widget'])) checked('on', $widget_options['ac_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('ac_widget')); ?>"><?php _e('Comment Average', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('au_widget')); ?>" name="<?php echo $this->get_field_name('au_widget'); ?>" <?php if (isset($widget_options['au_widget'])) checked('on', $widget_options['au_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('au_widget')); ?>"><?php _e('User Average', 'wp-statistics'); ?></label>
            </li>
            <li>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('lpd_widget')); ?>" class="lpd_widget" name="<?php echo $this->get_field_name('lpd_widget'); ?>" <?php if (isset($widget_options['lpd_widget'])) checked('on', $widget_options['lpd_widget']); ?>/>
                <label for="<?php echo esc_attr($this->get_field_id('lpd_widget')); ?>"><?php _e('Last Post Date', 'wp-statistics'); ?></label>
            </li>
        </ul>

        <input type="hidden" id="<?php echo esc_attr($this->get_field_id('wp_statistics_control_widget_submit')); ?>" name="<?php echo $this->get_field_name('wp_statistics_control_widget_submit'); ?>" value="1"/>
        <?php
    }
}

/**
 * Register WP_Statistics_Widget widget
 *
 * @return void
 */
add_action('widgets_init', 'register_wp_statistics_widget');
function register_wp_statistics_widget()
{
    register_widget('WP_Statistics_Widget');
}

