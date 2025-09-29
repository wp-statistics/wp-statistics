<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\Models\SummaryModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\WP_Background_Process;

class SummaryTotalsDataMigrationProcess extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'summary_totals_data_migration';

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
            if ($record) {
                continue;
            }

            $summaryModel->insert([
                'date'     => $date,
                'visitors' => $visitors,
                'views'    => $views
            ]);
        }

        return false;
    }

    public function is_initiated()
    {
        return Option::getOptionGroup('jobs', 'summary_totals_data_migration_initiated', false);
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        Notice::addFlashNotice(esc_html__('Summary data migration completed successfully.', 'wp-statistics'));
    }
}
