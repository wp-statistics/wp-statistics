<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs;

use WP_Statistics\Abstracts\BaseBackgroundProcess;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Posts\WordCountService;

class CalculatePostWordsCount extends BaseBackgroundProcess
{
    /**
     * Background-process action slug for this job.
     *
     * @var string
     */
    protected $action = 'calculate_post_words_count';

    /**
     * Word count service instance.
     *
     * @var WordCountService
     */
    private $wordsCount;

    /**
     * Initiated key for option storage.
     *
     * @var string
     */
    protected $initiatedKey = 'word_count_process_initiated';

    /**
     * Constructor to initialize the background process.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->wordsCount = new WordCountService();
        add_action('admin_init', [$this, 'localizeJobTexts']);
    }

    /**
     * Localize the job's title and description for display in the admin UI.
     *
     * @return void
     */
    public function localizeJobTexts()
    {
        $this->setSuccessNotice(esc_html__('Word count processed successfully.', 'wp-statistics'));
        $this->setJobTitle(esc_html__('Recalculate Post Word Counts', 'wp-statistics'));
        $this->setJobDescription(esc_html__('Updates the total word count for all contents across your site. This data is used in Content and Author Analytics reports. Run this if you’ve recently edited or added many contents and want the latest word counts reflected.', 'wp-statistics'));
    }


    /**
     * Perform task with queued item.
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over.
     *
     * @return mixed
     */
    protected function task($item)
    {
        $posts = $item['posts'];

        foreach ($posts as $postId) {
            $post           = get_post($postId);
            $wordCountClass = new WordCountService();
            $wordCountClass->handleSavePost($postId, $post);
        }

        $this->setProcessed($posts);

        return false;
    }

    /**
     * Complete processing.
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
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
        $pageSlug = Menus::in_page('author-analytics')
            ? 'author-analytics'
            : (Menus::in_page('content-analytics') ? 'content-analytics' : '');

        if (!empty($pageSlug) && $this->wordsCount->isActive()) {
            if ($this->isInitiated() || $this->is_active()) {
                return;
            }

            $actionUrl = $this->getActionUrl($force);

            $message = sprintf(
                __('Please <a href="%s">click here</a> to process the word count in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                esc_url($actionUrl)
            );

            Notice::addNotice($message, 'word_count_prompt', 'info', false);
        }
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

        $wordCount             = new WordCountService();
        $postsWithoutWordCount = $wordCount->getPostsWithoutWordCountMeta();

        $this->setTotal($postsWithoutWordCount);

        $batchSize = 100;
        $batches   = array_chunk($postsWithoutWordCount, $batchSize);

        foreach ($batches as $batch) {
            $this->push_to_queue(['posts' => $batch]);
        }

        $this->setInitiated();

        $this->save()->dispatch();
    }
}
