<?php
/**
 * Extended Background Process
 *
 * Extends the Composer-installed WP_Statistics_WP_Background_Process with custom functionality.
 *
 * @package WP_Statistics\BackgroundProcess
 * @since   15.0.0
 */

namespace WP_Statistics\BackgroundProcess;

/**
 * Abstract ExtendedBackgroundProcess class.
 *
 * Provides the stopProcess method for stopping background processes.
 *
 * @abstract
 * @extends WP_Statistics_WP_Background_Process
 */
abstract class ExtendedBackgroundProcess extends \WP_Statistics_WP_Background_Process
{
    /**
     * Stops the background process.
     *
     * Clears the scheduled event, deletes all batches, and unlocks the process.
     *
     * @return void
     */
    public function stopProcess()
    {
        $this->clear_scheduled_event();
        $this->delete_all();
        $this->unlock_process();
    }
}
