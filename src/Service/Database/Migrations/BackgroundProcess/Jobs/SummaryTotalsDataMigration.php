<?php
namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs;

use WP_Statistics\Abstracts\BaseBackgroundProcess;
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
            __('We’ve introduced a new summary table in this version. To ensure accurate reports, please initiate the data migration <a href="%s">by clicking here</a>.', 'wp-statistics'),
            esc_url($actionUrl)
        );

        Notice::addNotice($message, "{$this->action}_notice", 'info', false);
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

        @ini_set('memory_limit', '-1');

        $visitorModel = new VisitorsModel();
        $data         = $visitorModel->countDailyVisitors([
            'date' => [
                'from' => date('Y-m-d', 0),
                'to'   => date('Y-m-d', strtotime('yesterday'))
            ],
            'include_hits' => true,
            'bypass_cache' => true
        ]);

        $this->setTotal($data);

        $batchData = [];

        // Push each row to the batch, in the format of [date, visitors, hits]
        foreach ($data as $row) {
            $batchData[] = [$row->date, $row->visitors, $row->hits];
        }

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
