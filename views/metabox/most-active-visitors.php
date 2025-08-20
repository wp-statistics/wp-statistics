<?php
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Url;
?>
<?php if (!empty($data)) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
            <tr>
                <th class="wps-pd-l">
                    <span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Visitor Info', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Entry Page', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Exit Page', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Last View', 'wp-statistics'); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($data as $visitor) : ?>
                <?php /** @var VisitorDecorator $visitor */ ?>

                <tr>
                    <td class="wps-pd-l">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><?php echo esc_html($visitor->getHits()) ?></a>
                    </td>

                    <td class="wps-pd-l">
                        <?php View::load("components/visitor-information", ['visitor' => $visitor]); ?>
                    </td>


                    <td class="wps-pd-l">
                        <?php
                        View::load("components/objects/referrer-link", [
                            'label' => $visitor->getReferral()->getSourceChannel(),
                            'url'   => $visitor->getReferral()->getReferrer(),
                            'title' => $visitor->getReferral()->getRawReferrer()
                        ]);
                        ?>
                    </td>

                    <td class="wps-pd-l">
                        <?php
                        $firstPage = $visitor->getFirstPage();

                        if (!empty($firstPage)) :?>
                            <div class="wps-entry-page">
                                <?php
                                View::load("components/objects/internal-link", [
                                    'url'     => $firstPage['report'],
                                    'title'   => $firstPage['title'],
                                    'tooltip' => $firstPage['query'] ? "?{$firstPage['query']}" : ''
                                ]);
                                ?>

                                <?php $campaign = Url::getParam('?' . $firstPage['query'], 'utm_campaign'); ?>
                                <?php if ($campaign) : ?>
                                    <span class="wps-campaign-label wps-tooltip" title="<?php echo esc_attr__('Campaign:', 'wp-statistics') . ' ' . esc_attr($campaign); ?>"><?php echo esc_html($campaign); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php
                        else :
                            echo Admin_Template::UnknownColumn();
                        endif;
                        ?>
                    </td>


                    <td class="wps-pd-l">
                        <?php
                        $lastPage = $visitor->getLastPage();

                        if (!empty($lastPage)) :
                            View::load("components/objects/internal-link", [
                                'url'       => $lastPage['report'],
                                'title'     => $lastPage['title'],
                            ]);
                        else :
                            echo Admin_Template::UnknownColumn();
                        endif;
                        ?>
                    </td>

                    <td class="wps-pd-l">
                        <?php echo esc_html($visitor->getLastView()) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php
    $title = __('No data found for this date range.', 'wp-statistics');
    if ($isTodayOrFutureDate) {
        $title = __('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-1.svg',
        'title' => $title
    ]);
    ?>
<?php endif; ?>