<?php
namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs;

use WP_Statistics\Abstracts\BaseBackgroundProcess;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\SummaryModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class SummaryTotalsDataMigration extends BaseBackgroundProcess
{
    /**
     * Background-process action slug for this job.
     *
     * @var string
     */
    protected $action = 'summary_totals_data_migration';

    /**
     * Initiated key for option storage.
     *
     * @var string
     */
    protected $initiatedKey = 'summary_totals_data_migration_initiated';

    /**
     * Whether this job requires user confirmation before starting.
     *
     * @var bool
     */
    protected $confirmation = true;

    /**
     * Constructor to initialize the background process.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        add_action('admin_init', [$this, 'localizeJobTexts']);
    }

    /**
     * Localize the job's title and description for display in the admin UI.
     *
     * @return void
     */
    public function localizeJobTexts()
    {
        $this->setSuccessNotice(esc_html__('Summary data migration processed successfully.', 'wp-statistics'));
        $this->setJobTitle(esc_html__('Refresh Summary Totals', 'wp-statistics'));
        $this->setJobDescription(esc_html__('This recalculate daily totals from your current data. Days without detail will stay unchanged.', 'wp-statistics'));
        $this->setJobButtonTitle(esc_html__('Refresh Totals', 'wp-statistics'));
    }

    /**
     * Perform task with queued item.
     *
     * @param mixed $item Queue item to iterate over.
     * @return mixed
     */
    protected function task($item)
    {
        $data         = $item['data'];
        $summaryModel = new SummaryModel();

        foreach ($data as $row) {
            // Skip empty rows
            if (empty($row) || count($row) < 3) {
                continue;
            }

            [$date, $visitors, $views] = $row;

            // Check if record is already inserted
            $record = $summaryModel->recordExists(['date' => $date]);

            $args = [
                'date'     => $date,
                'visitors' => $visitors,
                'views'    => $views
            ];

            if ($record) {
                $summaryModel->update($args);
            } else {
                $summaryModel->insert($args);
            }
        }

        $this->setProcessed($data);

        return false;
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        $this->clearTotalAndProcessed();
    }

    /**
     * Show initial notice to start the background process.
     *
     * When `$force` is true, the generated action URL includes `force=1` so the
     * manager can re-initiate the job even if it has already been started.
     *
     * @param bool $force Whether to include the `force` flag to restart the job. Default false.
     * @return void
     */
    public function initialNotice($force = false)
    {
        if ($this->isInitiated() || $this->is_active()) {
            return;
        }

        $actionUrl = $this->getActionUrl($force);

        $message = sprintf(
            '<div id="wp-statistics-queue-process-notice">
                <p><strong>%1$s:</strong> %2$s</p>
                <p><a href="%3$s" id="start-queue-migration-btn" class="button-primary">%4$s</a></p>
            </div>',
            esc_html__('We’ve introduced a new summary table', 'wp-statistics'),
            __('Run this quick migration to ensure your reports stay accurate.', 'wp-statistics'),
            esc_url($actionUrl),
            esc_html__('Start Migration', 'wp-statistics')
        );

        Notice::addNotice($message, "{$this->action}_notice", 'warning', false);
    }

    /**
     * Initiate the background process to calculate word counts for posts.
     *
     * @return void
     */
    public function process()
    {
        if ($this->is_active()) {
            return;
        }

        $this->clearTotalAndProcessed();

        @ini_set('memory_limit', '-1');

        $dateRange = [
            'from' => date('Y-m-d', 0),
            'to'   => date('Y-m-d', strtotime('yesterday'))
        ];

        $visitorModel = new VisitorsModel();
        $rawData      = $visitorModel->countDailyVisitors([
            'date'         => $dateRange,
            'include_hits' => true,
            'bypass_cache' => true
        ]);

        if (empty($rawData)) {
            $this->setInitiated();
            return;
        }

        // Set date as object key for easy lookup
        $rawData = array_column($rawData, null, 'date');

        // Set start date based on the first available record
        $dateRange['from'] = array_key_first($rawData);

        // Get all dates within the range
        $dates     = DateRange::getDatesInRange($dateRange);
        $batchData = [];

        foreach ($dates as $date) {
            // Get the row data for the current date, or default to zeros
            $row = $rawData[$date] ?? (object) ['date' => $date, 'visitors' => 0, 'hits' => 0];

            $batchData[] = [$row->date, $row->visitors, $row->hits];
        }

        $this->setTotal($batchData);

        // Define the batch size
        $batchSize = 100;
        $batches   = array_chunk($batchData, $batchSize);

        // Push each batch to the queue
        foreach ($batches as $batch) {
            $this->push_to_queue(['data' => $batch]);
        }

        $this->setInitiated();
        $this->save()->dispatch();
    }
}
