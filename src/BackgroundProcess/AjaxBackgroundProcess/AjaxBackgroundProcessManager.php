<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess;

use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

/**
 * Manages the background execution of database migrations within WordPress.
 *
 * This class is responsible for handling the migration process, including:
 * - Displaying admin notices to indicate migration progress or completion.
 * - Registering scripts for background AJAX execution.
 * - Managing the AJAX request lifecycle for triggering and running migrations.
 * - Ensuring migrations are executed sequentially and their status is tracked persistently.
 */
class AjaxBackgroundProcessManager
{
    /**
     * The action slug used for manually triggering the background migration.
     *
     * @var string
     */
    private const MIGRATION_ACTION = 'run_ajax_background_process';

    /**
     * The nonce name used to secure the manual migration action.
     *
     * @var string
     */
    private const MIGRATION_NONCE = 'run_ajax_background_process_nonce';

    /**
     * Class constructor.
     * Initializes migration handling and attaches necessary hooks.
     */
    public function __construct()
    {
        add_action('current_screen', [$this, 'handleDoneNotice']);

        if (!AjaxBackgroundProcessFactory::needsMigration()) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'registerScript']);
        add_filter('wp_statistics_ajax_list', [$this, 'addAjax']);
        add_action('current_screen', [$this, 'handleNotice']);
        add_action('admin_post_' . self::MIGRATION_ACTION, [$this, 'handleAjaxMigration']);
    }

    /**
     * Adds the migration process to the AJAX action list.
     *
     * @param array $list List of existing AJAX actions.
     * @return array Updated list including migration.
     */
    public function addAjax($list)
    {
        $list[] = [
            'class'  => !AjaxBackgroundProcessFactory::isDatabaseMigrated() ? null : AjaxBackgroundProcessFactory::getCurrentMigrate(),
            'action' => 'background_process',
            'public' => false
        ];

        return $list;
    }

    /**
     * Displays a success notice when the database migration process is completed.
     *
     * If the migration is marked as "done," it shows a completion message and clears the migration status.
     *
     * @return void
     */
    public function handleDoneNotice()
    {
        if (!$this->shouldShowNotice()) {
            return;
        }

        $status = Option::getOptionGroup('ajax_background_process', 'status', null);

        if ($status !== 'done') {
            return;
        }

        $message = sprintf(
            '
                <p>
                    <strong>%1$s</strong>
                    </br>%2$s
                </p>
            ',
            esc_html__('WP Statistics: Process Complete', 'wp-statistics'),
            esc_html__('The Database Migration process has been completed successfully. Thank you for keeping WP Statistics up-to-date!', 'wp-statistics')
        );

        Notice::addFlashNotice($message, 'success', false);
        Option::saveOptionGroup('status', null, 'ajax_background_process');
    }

    /**
     * Displays an admin notice based on the current migration status.
     *
     * - Shows a progress message if migration is running.
     * - Displays a start button if migration has not started.
     *
     * @return void
     */
    public function handleNotice()
    {
        if (!$this->shouldShowNotice()) {
            return;
        }

        $status = Option::getOptionGroup('ajax_background_process', 'status', null);

        if ($status === 'progress') {
            $message = sprintf(
                '<div id="wp-statistics-background-process-notice">
                    <p><strong>%1$s</strong></p>
                    <p>%2$s (<strong><span class="remain-percentage">0</span>%% %3$s</strong>).</p>
                    <p>%4$s</p>
                </div>',
                esc_html__('WP Statistics: Migration In Progress', 'wp-statistics'),
                esc_html__('Your data is currently migrating in the background', 'wp-statistics'),
                esc_html__('completed', 'wp-statistics'),
                esc_html__('Please keep this page open if possible. If you close, the migration will pause. Don’t worry—simply come back to this page to pick up where it left off.', 'wp-statistics')
            );

            Notice::addNotice($message, 'progress_ajax_background_process', 'info', false);
            return;
        }

        $isMigrated = AjaxBackgroundProcessFactory::isDatabaseMigrated();

        if (!$isMigrated) {
            return;
        }

        $migrationUrl = add_query_arg(
            [
                'action' => self::MIGRATION_ACTION,
                'nonce'  => wp_create_nonce(self::MIGRATION_NONCE),
                'status' => Option::getOptionGroup('ajax_background_process', 'status', null)
            ],
            admin_url('admin-post.php')
        );

        $message = sprintf(
            '<div id="wp-statistics-background-process-notice">
                <p><strong>%1$s</strong></p>
                <p>%2$s <br> %3$s</p>
                <p><a href="%4$s" id="start-migration-btn" class="button-primary">%5$s</a><a href="%6$s" style="margin: 10px" target="_blank">%7$s</a></p>
            </div>',
            esc_html__('WP Statistics: Migration Required', 'wp-statistics'),
            __('A data migration is needed for WP Statistics. Click <strong>Start Migration</strong> below to begin.', 'wp-statistics'),
            __('<strong>Note:</strong> If you leave this page before the migration finishes, the process will pause. You can always return later to resume.', 'wp-statistics'),
            esc_url($migrationUrl),
            esc_html__('Start Migration', 'wp-statistics'),
            'https://wp-statistics.com/resources/database-migration-process-guide/?utm_source=wp-statistics&utm_medium=link&utm_campaign=doc',
            esc_html__('Read More', 'wp-statistics')
        );

        Notice::addNotice($message, 'start_ajax_background_process', 'warning', false);
    }

    /**
     * Registers JavaScript files required for migration execution.
     *
     * @return void
     */
    public function registerScript()
    {
        wp_enqueue_script(
            'wp-statistics-ajax-migrator',
            Admin_Assets::url('background-process.min.js'),
            ['jquery'],
            Admin_Assets::version(),
            ['in_footer' => true]
        );

        wp_localize_script(
            'wp-statistics-ajax-migrator',
            'Wp_Statistics_Background_Process_Data',
            [
                'rest_api_nonce' => wp_create_nonce('wp_rest'),
                'ajax_url'       => admin_url('admin-ajax.php'),
                'status'         => Option::getOptionGroup('ajax_background_process', 'status', null)
            ]
        );
    }

    /**
     * Handles the AJAX request to start the migration process.
     *
     * - Validates the request action.
     * - Verifies the security nonce.
     * - Updates the migration status in the database.
     *
     * @return void
     */
    public function handleAjaxMigration()
    {
        if (!Request::compare('action', self::MIGRATION_ACTION)) {
            return false;
        }

        check_admin_referer(self::MIGRATION_NONCE, 'nonce');

        Option::saveOptionGroup('status', 'progress', 'ajax_background_process');

        $this->handleRedirect();
    }

    /**
     * Handle the redirect after processing ajax migrations.
     *
     * @return void
     */
    private function handleRedirect()
    {
        $referer = wp_get_referer();
        wp_redirect($referer ?: admin_url());
        exit;
    }

    /**
     * Determines whether the background process notice should be displayed.
     *
     * @return bool
     */
    private function shouldShowNotice()
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (Menus::in_plugin_page()) {
            return true;
        }

        if (in_array(\WP_STATISTICS\Helper::get_screen_id(), ['dashboard'], true)) {
            return true;
        }

        return false;
    }
}
