<?php

namespace WP_Statistics\Tests;

use WP_Error;
use WP_Statistics\Abstracts\BaseBackgroundProcess;
use WP_STATISTICS\WP_Background_Process;
use WP_UnitTestCase;

class Test_BaseBackgroundProcess extends WP_UnitTestCase
{
    /**
     * @var Test_BackgroundProcess
     */
    private $process;

    public function setUp(): void
    {
        parent::setUp();

        $this->process = new Test_BackgroundProcess();
    }

    public function tearDown(): void
    {
        wp_clear_scheduled_hook($this->process->getCronHook());
        delete_site_option($this->process->getStatusKey());
        delete_site_transient($this->process->getProcessLockKey());
        $this->process->delete_all();

        parent::tearDown();
    }

    public function test_cancelled_empty_queue_is_finalized_by_cron_healthcheck()
    {
        $cancelled = 0;
        $callback  = static function () use (&$cancelled) {
            $cancelled++;
        };

        update_site_option($this->process->getStatusKey(), WP_Background_Process::STATUS_CANCELLED);
        $this->process->scheduleHealthcheck();
        add_action($this->process->getIdentifier() . '_cancelled', $callback);

        try {
            $this->process->handle_cron_healthcheck();

            $this->assertFalse(get_site_option($this->process->getStatusKey(), false));
            $this->assertSame(1, $cancelled);
            $this->assertFalse(wp_next_scheduled($this->process->getCronHook()));
        } finally {
            remove_action($this->process->getIdentifier() . '_cancelled', $callback);
        }
    }

    public function test_cron_healthcheck_preserves_existing_wp_die_filter()
    {
        $hook = $this->process->getIdentifier() . '_wp_die';

        add_filter($hook, '__return_false');
        $this->process->push_to_queue('item')->save();

        try {
            $this->process->handle_cron_healthcheck();

            $this->assertSame(10, has_filter($hook, '__return_false'));
            $this->assertFalse($this->process->hasQueuedItems());
        } finally {
            remove_filter($hook, '__return_false');
        }
    }

    public function test_cron_healthcheck_processes_queue_without_an_ajax_loopback()
    {
        $loopbackRequests = 0;
        $cronContinued    = false;
        $blockLoopback    = static function ($preempt, $args, $url) use (&$loopbackRequests) {
            if (false !== strpos($url, 'admin-ajax.php')) {
                $loopbackRequests++;
                return new WP_Error('loopback_blocked', 'Loopback blocked for the test.');
            }

            return $preempt;
        };
        $afterHealthcheck = static function () use (&$cronContinued) {
            $cronContinued = true;
        };

        $this->process->queueItem('queued item');
        $this->process->scheduleHealthcheck();
        add_filter('pre_http_request', $blockLoopback, 10, 3);
        add_action($this->process->getCronHook(), $afterHealthcheck, 20);

        try {
            do_action($this->process->getCronHook());

            $this->assertSame(['queued item'], $this->process->getHandledItems());
            $this->assertSame(0, $this->process->getBatchCount());
            $this->assertSame(0, $loopbackRequests);
            $this->assertTrue($cronContinued);
            $this->assertFalse(wp_next_scheduled($this->process->getCronHook()));
        } finally {
            remove_filter('pre_http_request', $blockLoopback, 10);
            remove_action($this->process->getCronHook(), $afterHealthcheck, 20);
        }
    }
}

class Test_BackgroundProcess extends BaseBackgroundProcess
{
    protected $action = 'test_background_process';

    private $handledItems = [];

    public function process()
    {
        return true;
    }

    protected function task($item)
    {
        $this->handledItems[] = $item;
        return false;
    }

    public function queueItem($item)
    {
        $this->push_to_queue($item)->save();
    }

    public function getHandledItems()
    {
        return $this->handledItems;
    }

    public function getBatchCount()
    {
        return count($this->get_batches());
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getCronHook()
    {
        return $this->cron_hook_identifier;
    }

    public function getStatusKey()
    {
        return $this->get_status_key();
    }

    public function getProcessLockKey()
    {
        return $this->identifier . '_process_lock';
    }

    public function scheduleHealthcheck()
    {
        $this->schedule_event();
    }

    public function hasQueuedItems()
    {
        return !$this->is_queue_empty();
    }
}
