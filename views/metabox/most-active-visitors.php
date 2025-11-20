<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
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
                <?php
                    if ($visitor instanceof VisitorDecorator) {
                        $hits = $visitor->getHits();

                        $lastResouceViewDate = $visitor->getLastView();

                        $countryCode = $visitor->getLocation()->getCountryCode();
                        $countryName = $visitor->getLocation()->getCountryName();
                        $countryflag = $visitor->getLocation()->getCountryFlag();
                        $region      = $visitor->getLocation()->getRegion();
                        $city        = $visitor->getLocation()->getCity();

                        $lastResource        = $visitor->getLastPage();
                        $lastResourceLink    = $lastResource['link'];
                        $lastResourceTitle   = $lastResource['title'];
                        $lastResourceQuery   = $lastResource['query'] ? "?{$lastResource['query']}" : '';
                        $lastResouceViewDate = $visitor->getLastView();
                    } else {
                        $hits = $visitor->getViews();

                        $lastResouceViewDate = $visitor->getLastView()->getViewedAt();  

                        $countryCode = $visitor->getCountry()->getCode();
                        $countryName = $visitor->getCountry()->getName();
                        $countryflag = $visitor->getCountry()->getFlag();
                        $region      = $visitor->getCity()->getRegionName();
                        $city        = $visitor->getCity()->getName();

                        $lastResource        = $visitor->getLastView()->getResource();
                        $lastResourceLink    = $lastResource->getUrl();
                        $lastResourceTitle   = $lastResource->getTitle();
                        $lastResourceQuery   = $visitor->getParameter($lastResource->getId())->getFull();
                        $lastResouceViewDate = $visitor->getLastView()->getViewedAt();                                   
                    }
                ?>

                <tr>
                    <td class="wps-pd-l">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><?php echo esc_html($hits) ?></a>
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

                        if (!empty($firstPage)) :
                            View::load("components/objects/internal-link", [
                                'url'       => $firstPage['report'],
                                'title'     => $firstPage['title'],
                                'tooltip'   => $firstPage['query'] ? "?{$firstPage['query']}" : ''
                            ]);
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
                        <?php echo esc_html($lastResouceViewDate); ?>
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
        'url'   => WP_STATISTICS_URL . 'public/images/no-data/vector-1.svg',
        'title' => $title
    ]);
    ?>
<?php endif; ?>