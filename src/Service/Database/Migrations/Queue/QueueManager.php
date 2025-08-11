<?php

namespace WP_Statistics\Service\Database\Migrations\Queue;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Database\DatabaseHelper;
use WP_Statistics\Utils\Request;

/**
 * Queue Migration Manager
 *
 * Manages the background execution of database migrations using WordPress queue system.
 * This class provides a comprehensive queue migration system with automatic execution,
 * user notifications, and proper security handling.
 */
class QueueManager
{
    /**
     * The action slug used for manually triggering the queue migration.
     *
     * This constant defines the WordPress admin action that users can trigger
     * to manually start the queue-based migration process.
     *
     * @var string
     */
    private const MIGRATION_ACTION = 'run_queue_background_process';

    /**
     * The nonce name used to secure the manual migration action.
     *
     * This constant defines the nonce field name used to secure the manual
     * migration action against CSRF attacks.
     *
     * @var string
     */
    private const MIGRATION_NONCE = 'run_queue_background_process_nonce';

    /**
     * Class constructor.
     *
     * Initializes the migration handling system and attaches necessary WordPress hooks.
     * Sets up notices for both completed migrations and pending migration requirements.
     * Only attaches migration-related hooks if migrations are needed or completed.
     */
    public function __construct()
    {
        add_action('current_screen', [$this, 'handleDoneNotice']);

        if (!QueueFactory::isMigrationCompleted() && !QueueFactory::needsMigration()) {
            return;
        }

        add_action('current_screen', [$this, 'handleNotice']);
        add_action('admin_post_' . self::MIGRATION_ACTION, [$this, 'handleQueueMigration']);
    }

    /**
     * Displays a success notice when the database migration process is completed.
     *
     * This method checks if the migration process has been completed and displays
     * a success notice to inform the user. It also cleans up the completion status
     * from the options to prevent the notice from showing repeatedly.
     *
     * @return void
     */
    public function handleDoneNotice()
    {
        if (!$this->isValidMigrationContext() || QueueFactory::isMigrationCompleted()) {
            return;
        }

        $status = Option::getOptionGroup('queue_background_process', 'status', null);

        if ($status !== 'done') {
            return;
        }

        $message = sprintf(
            '
                <p>
                    <strong>%1$s</strong>
                    —
                    %2$s
                </p>
            ',
            esc_html__('Update complete', 'wp-statistics'),
            esc_html__('thanks for staying up to date!', 'wp-statistics')
        );

        Notice::addFlashNotice($message, 'success', false);
        Option::saveOptionGroup('status', null, 'queue_background_process');
    }

    /**
     * Displays an admin notice with a start button for queue migration.
     *
     * This method checks if queue migration is needed and displays an admin notice
     * with a start button to allow users to manually trigger the migration process.
     * The notice includes information about the number of steps to be processed.
     *
     * @return void
     */
    public function handleNotice()
    {
        if (!$this->isValidMigrationContext() || QueueFactory::isMigrationCompleted()) {
            return;
        }

        $isMigrated = QueueFactory::isDatabaseMigrated();

        if (!$isMigrated) {
            return;
        }

        $migrationUrl = add_query_arg(
            [
                'action'       => self::MIGRATION_ACTION,
                'nonce'        => wp_create_nonce(self::MIGRATION_NONCE),
                'current_page' => DatabaseHelper::getCurrentAdminUrl()
            ],
            admin_url('admin-post.php')
        );

        $message = sprintf(
            '<div id="wp-statistics-queue-process-notice">
                <p><strong>%1$s:</strong> %2$s</p>
                <p><a href="%3$s" id="start-queue-migration-btn" class="button-primary">%4$s</a></p>
            </div>',
            esc_html__('WP Statistics needs a quick update', 'wp-statistics'),
            __('Run this brief update to keep your stats accurate.', 'wp-statistics'),
            esc_url($migrationUrl),
            esc_html__('Update Now', 'wp-statistics')
        );

        Notice::addNotice($message, 'start_queue_background_process', 'warning', false);
    }

    /**
     * Handles the request to start the queue migration process.
     *
     * This method processes the admin POST request to start queue migration.
     * It validates the request action, verifies the nonce for security,
     * executes all pending migrations, marks the process as completed,
     * and handles the redirect back to the original page.
     *
     * @return bool|void False if the request is invalid, void otherwise
     */
    public function handleQueueMigration()
    {
        if (!Request::compare('action', self::MIGRATION_ACTION)) {
            return false;
        }

        check_admin_referer(self::MIGRATION_NONCE, 'nonce');

        $this->executeAllMigrations();

        Option::saveOptionGroup('status', 'done', 'queue_background_process');

        $this->handleRedirect();
    }

    /**
     * Executes all pending queue-based migration steps.
     *
     * This method retrieves all pending migration steps from the QueueFactory
     * and executes them sequentially. Each step is processed individually
     * to ensure proper completion and error handling.
     *
     * @return void
     */
    private function executeAllMigrations()
    {
        $pendingSteps = QueueFactory::getPendingMigrationSteps();

        foreach ($pendingSteps as $step) {
            QueueFactory::executeMigrationStep($step);
        }
    }

    /**
     * Handles the redirect after processing queue migrations.
     *
     * This method redirects the user back to the original page after
     * the migration process is completed. It attempts to use the current_page
     * parameter from the request, falling back to the home URL if not available.
     *
     * @return void This method always exits after redirect
     */
    private function handleRedirect()
    {
        $redirectUrl = $_POST['current_page'] ?? $_GET['current_page'] ?? '';

        if (empty($redirectUrl)) {
            $redirectUrl = home_url();
        }

        wp_redirect(esc_url_raw($redirectUrl));
        exit;
    }

    /**
     * Validates whether the current admin page and user have access to handle migration-related functionality.
     *
     * This method performs security checks to ensure that:
     * - The current user has the 'manage_options' capability
     * - The current page is a WP Statistics plugin page
     *
     * @return bool True if the context is valid for migration operations, false otherwise
     */
    private function isValidMigrationContext()
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (Menus::in_plugin_page()) {
            return true;
        }

        return false;
    }
}

