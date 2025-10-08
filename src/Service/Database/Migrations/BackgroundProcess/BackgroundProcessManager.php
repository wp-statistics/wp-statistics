<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess;

use WP_Statistics\Abstracts\BaseMigrationManager;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs\CalculatePostWordsCount;
use WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs\VisitorColumnsMigrator;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

/**
 * Class BackgroundProcessManager
 * 
 * Manages background processes for database migrations.
 */
class BackgroundProcessManager extends BaseMigrationManager
{
    /**
     * The background process instances.
     * 
     * @var array<string, object>
     */
    private $backgroundProcess = [];

    /**
     * List of background process classes to be registered.
     * 
     * @var array<string, string>
     */
    private $backgroundProcesses = [];

    /**
     * The key of the currently running background process.
     * 
     * @var string
     */
    private $currentProcess = '';

    /**
     * The action slug used for manually triggering the background migration.
     *
     * @var string
     */
    private const MIGRATION_ACTION = 'run_async_background_process';

    /**
     * The nonce name used to secure the manual migration action.
     *
     * @var string
     */
    private const MIGRATION_NONCE = 'run_ajax_background_process_nonce';

    /**
     * Class constructor.
     * 
     * Initializes background processes and attaches necessary WordPress hooks.
     */
    public function __construct()
    {
        $this->initializeBackgroundProcess();
        add_action('admin_init', [$this, 'showProgressNotices']);
        add_action('admin_enqueue_scripts', [$this, 'registerScript']);
        add_filter('wp_statistics_ajax_list', [$this, 'addAjax']);
    }

    /**
     * Initialize and register background processes.
     * 
     * @return void
     */
    private function initializeBackgroundProcess()
    {
        if (! empty($this->backgroundProcess) || empty($this->backgroundProcesses)) {
            return;
        }

        foreach ($this->backgroundProcesses as $key => $className) {
            $this->registerBackgroundProcess($className, $key);
        }
    }
    
    /**
     * Register a background process by its class name and key.
     * 
     * @param string $className The class name of the background process.
     * @param string $processKey The key to identify the background process.
     * 
     * @return void
     */
    private function registerBackgroundProcess($className, $processKey)
    {
        if (!class_exists($className)) {
            return;
        }

        $this->backgroundProcess[$processKey] = new $className();
    }

    /**
     * Get a background process instance by its key.
     * 
     * @param string $processKey The key of the background process.
     * 
     * @return object|null The background process instance or null if not found.
     */
    public function getBackgroundProcess($processKey)
    {
        return $this->backgroundProcess[$processKey] ?? null;
    }

    /**
     * Show progress notices for each registered background process.
     * Displays a notice like: "Calculate Post Words Count: 34% complete (34/100)."
     * Only shows while a process is active and has a non-zero total.
     *
     * @return void
     */
    public function showProgressNotices()
    {
        if (empty($this->backgroundProcess) || !$this->isValidContext()) {
            return;
        }

        foreach ($this->backgroundProcess as $key => $instance) {
            if (!is_object($instance)) {
                continue;
            }

            if (method_exists($instance, 'inititalNotice')) {
                $instance->inititalNotice();
            }

            $isProcessing = method_exists($instance, 'is_processing') ? (bool) $instance->is_processing() : false;

            if(!$isProcessing) {
                continue;
            }

            $this->currentProcess = $key;

            $total     = method_exists($instance, 'getTotal') ? $instance->getTotal() : 0;
            $processed = method_exists($instance, 'getProcessed') ? $instance->getProcessed() : 0;

            if ($total <= 0 || $processed >= $total) {
                continue;
            }

            $percent = (int) floor(($processed / $total) * 100);
            if ($percent >= 100) {
                $percent = 99;
            } elseif ($percent < 0) {
                $percent = 0;
            }

            $label = ucwords(str_replace('_', ' ', (string) $key));

            $message = sprintf(
                '<div id="wp-statistics-async-background-process-notice">%s: <span class="percentage">%d%%</span> complete (<span class="processed">%d</span>/%d).</div>',
                $label,
                $percent,
                $processed,
                $total
            );

            Notice::addNotice($message, 'info');
        }
    }

    /**
     * Registers JavaScript files required for migration execution.
     *
     * @return void
     */
    public function registerScript()
    {
        if (!$this->isValidContext()) {
            return;
        }

        wp_enqueue_script(
            'wp-statistics-async-background-process',
            Admin_Assets::url('background-process-tracker.min.js'),
            ['jquery'],
            Admin_Assets::version(),
            ['in_footer' => true]
        );

        wp_localize_script(
            'wp-statistics-async-background-process',
            'Wp_Statistics_Async_Background_Process_Data',
            [
                'rest_api_nonce' => wp_create_nonce('wp_rest'),
                'ajax_url'       => admin_url('admin-ajax.php'),
                'interval'       => apply_filters('wp_statistics_async_background_process_ajax_interval', 5000),
            ]
        );
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
            'class'  => $this,
            'action' => 'async_background_process',
            'public' => false
        ];

        return $list;
    }

    /**
     * Handles the AJAX request for the background process.
     * 
     * @return void
     */
    public function async_background_process_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        if (!Request::isFrom('ajax') || !User::Access('manage')) {
            wp_send_json_error([
                'message' => esc_html__('Unauthorized request or insufficient permissions.', 'wp-statistics')
            ]);
        }

        if (empty($this->currentProcess)) {
            wp_send_json_success([
                'completed' => true,
            ]);
        }

        $total     = $this->getBackgroundProcess($this->currentProcess)->getTotal();
        $processed = $this->getBackgroundProcess($this->currentProcess)->getProcessed();

        wp_send_json_success([
            'percentage' => (int) floor(($processed / $total) * 100),
            'processed'  => $this->getBackgroundProcess($this->currentProcess)->getProcessed(),
        ]);
    }
}
