<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess;

use WP_STATISTICS\Admin_Assets;
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
     * Initializes migration process if required.
     */
    public function __construct()
    {
        if (! AjaxBackgroundProcessFactory::needsMigration()) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'registerScript']);
        add_filter('wp_statistics_ajax_list', [$this, 'addAjax']);
        add_action('admin_init', [$this, 'handleNotice']);
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
            'class'  => AjaxBackgroundProcessFactory::getCurrentMigrate(),
            'action' => 'background_process',
            'public' => false
        ];

        return $list;
    }

    /**
     * Displays an admin notice to inform users about ongoing migration.
     */
    public function handleNotice()
    {
        $status = Option::getOptionGroup('ajax_background_process', 'status', null);

        if ($status === 'done') {
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
            return;
        }

        if ($status === 'progress') {
            $message = sprintf(
                '<div id="wp-statistics-background-process-notice">
                    <p><strong>%1$s</strong></p>
                    <p>%2$s</p>
                    <p><strong>%3$s: <span class="remain-number">0</span></strong></p>
                </div>',
                esc_html__('WP Statistics: Migration in Progress', 'wp-statistics'),
                __('Your database is currently being migrated. This process runs in the background while you are in the dashboard.', 'wp-statistics'),
                esc_html__('Records Remaining', 'wp-statistics')
            );

            Notice::addNotice($message, 'progress_ajax_background_process', 'info', false);
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
                <p><strong>%1$s</strong><br>%2$s</p>
                <p><a href="%3$s" id="start-migration-btn" class="button-primary">%4$s</a></p>
            </div>',
            esc_html__('WP Statistics: Process Required', 'wp-statistics'),
            __('Database Migration has not started yet. Click the button below to begin.', 'wp-statistics'),
            esc_url($migrationUrl),
            esc_html__('Start Migration', 'wp-statistics')
        );

        Notice::addNotice($message, 'start_ajax_background_process', 'warning', false);
    }

    /**
     * Registers migration-related admin scripts.
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
}
