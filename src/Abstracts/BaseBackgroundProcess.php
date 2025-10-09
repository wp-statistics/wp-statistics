<?php

namespace WP_Statistics\Abstracts;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Database\Migrations\BackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\Traits\MigrationAccess;
use WP_STATISTICS\WP_Background_Process;

/**
 * Class BaseBackgroundProcess
 *
 * An abstract class for creating background processes using WP_Background_Process.
 * This class provides a structure for defining background tasks.
 * @package WP_Statistics\Abstracts
 */
abstract class BaseBackgroundProcess extends WP_Background_Process
{
    use MigrationAccess;

    /**
     * Prefix for the process.
     * 
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * Initiated key for option storage.
     * 
     * @var string
     */
    protected $initiatedKey = '';

    /**
     * Total number of items to process.
     * 
     * @var string
     */
    protected $totalOptionKey = '';

    /**
     * Number of processed items.
     * 
     * @var int
     */
    protected $processedOptionKey = '';

    /**
     * Check if the process has been initiated.
     *
     * @param bool $status Whether the job is marked as initiated. Default true.
     * @return string
     */
    public function setInitiated($status = true)
    {
        Option::saveOptionGroup($this->initiatedKey, $status, 'jobs');
    }

    /**
     * Check if the process has been initiated.
     *
     * @return bool
     */
    public function isInitiated()
    {
        return Option::getOptionGroup('jobs', $this->initiatedKey, false);
    }

    /**
     * Get the option key used to store the "initiated" flag for this job.
     *
     * @return string Option key name for the initiated status.
     */
    public function getInitiatedKey()
    {
        return $this->initiatedKey;
    }

    /**
     * Set a success notice to be displayed to the user.
     *
     * @param string $message The success message to display.
     */
    protected function setSuccessNotice($message)
    {
        Notice::addFlashNotice($message);
    }

    /**
     * Set the total and processed option keys.
     *
     * @return void
     */
    protected function setTotalAndProcessed()
    {
        $this->totalOptionKey     = $this->action . '_total';
        $this->processedOptionKey = $this->action . '_processed';
    }

    /**
     * Set the total number of items to process.
     *
     * @param array $items The items to count or count of the items.
     * @return void
     */
    protected function setTotal($items)
    {
        $total = $this->getTotal();

        if (! empty($total) || empty($items)) {
            return;
        }

        $total = is_array($items) ? count($items) : $items;
        Option::saveOptionGroup($this->totalOptionKey, $total, 'jobs');
    }

    /**
     * Set the number of processed items.
     *
     * @param array $processed The items that have been processed.
     * @return void
     */
    protected function setProcessed($processed)
    {
        if (empty($processed)) {
            return;
        }

        $processedCount   = 0;
        $alreadyProcessed = $this->getProcessed();

        $processedCount = (int) $alreadyProcessed + intval(count($processed));

        Option::saveOptionGroup($this->processedOptionKey, $processedCount, 'jobs');
    }

    /**
     * Clear the total and processed counts.
     * 
     * @return void
     */
    protected function clearTotalAndProcessed()
    {
        $this->setTotalAndProcessed();
        
        Option::deleteOptionGroup($this->totalOptionKey, 'jobs');
        Option::deleteOptionGroup($this->processedOptionKey, 'jobs');
    }

    /**
     * Get the total number of items to process.
     *
     * @return int
     */
    public function getTotal()
    {
        if (empty($this->totalOptionKey)) {
            $this->setTotalAndProcessed();
        }

        return (int) Option::getOptionGroup('jobs', $this->totalOptionKey, 0);
    }

    /**
     * Get the number of processed items.
     *
     * @return int
     */
    public function getProcessed()
    {
        if (empty($this->processedOptionKey)) {
            $this->setTotalAndProcessed();
        }

        return (int) Option::getOptionGroup('jobs', $this->processedOptionKey, 0);
    }

    /**
     * Build the admin-post URL to trigger this background process from the current admin page.
     *
     * @param bool $force Whether to include the `force` flag to allow restart. Default false.
     * @return string Fully formed admin-post URL, or an empty string when the current page URL is unavailable.
     */
    public function getActionUrl($force = false)
    {
        $currentPage = Menus::getCurrentPage();

        if (empty($currentPage['page_url'])) {
            return '';
        }

        $actionUrl = add_query_arg(
            [
                'action'   => BackgroundProcessFactory::getActionName(),
                'job_key'  => $this->action,
                'nonce'    => BackgroundProcessFactory::getActionNonce(),
                'redirect' => $currentPage['page_url'],
                'force'    => $force
            ],
            admin_url('admin-post.php')
        );

        return $actionUrl;
    }

    /**
     * Triggers the background process.
     *
     * @abstract
     * @return bool
     */
    public abstract function process();
}
