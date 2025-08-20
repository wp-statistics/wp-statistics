<?php
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Url;
?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th scope="col" class="wps-pd-l">
                        <span class="wps-order"><?php esc_html_e('Total Views', 'wp-statistics'); ?>
                            <span class="wps-tooltip" title="<?php echo esc_html__('Total views for a single day. Privacy rules assign users a new ID daily, so visits on different days are counted separately.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></span>
                        </span>
                    </th>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Visitor Information', 'wp-statistics'); ?>
                    </th>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                    </th>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Entry Page', 'wp-statistics'); ?>
                    </th>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Exit Page', 'wp-statistics'); ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $visitor) : ?>
                    <?php /** @var VisitorDecorator $visitor */ ?>
                    <tr>
                        <td class="wps-pd-l">
                            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>"><?php echo esc_html($visitor->getHits()); ?></a>
                        </td>

                        <td class="wps-pd-l">
                            <?php
                                View::load("components/visitor-information", ['visitor' => $visitor]);
                            ?>
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
                            $page = $visitor->getFirstPage();

                            if (!empty($page)) :?>
                                <div class="wps-entry-page">
                                    <?php
                                    View::load("components/objects/internal-link", [
                                        'url'     => $page['report'],
                                        'title'   => $page['title'],
                                        'tooltip' => $page['query'] ? "?{$page['query']}" : ''
                                    ]);
                                    ?>

                                    <?php $campaign = Url::getParam('?' . $page['query'], 'utm_campaign'); ?>
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
                            <?php $page = $visitor->getLastPage(); ?>
                            <?php if (!empty($page)) :
                                View::load("components/objects/internal-link", [
                                    'url'   => $page['report'],
                                    'title' => $page['title'],
                                ]);
                            else : ?>
                                <?php echo Admin_Template::UnknownColumn() ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>