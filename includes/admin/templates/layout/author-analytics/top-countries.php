<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="inside">
        <!--  if empty-->
        <!-- <div class="o-wrap o-wrap--no-data wps-center">-->
        <!-- <?php //esc_html_e('No recent data available.', 'wp-statistics')   ?> -->
        <!-- </div>
          else
          -->
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-authors-table wps-top-countries-table">
                <thead>
                <tr>
                    <th></th>
                    <th>
                        <?php esc_html_e('Country', 'wp-statistics') ?>
                        <span class="wps-tooltip" title="<?php esc_html_e('Country tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Visitors', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>
                <tbody >
                <?php for ($i = 1; $i < 10; $i++): ?>
                    <tr>
                        <td> <?php echo esc_html($i)  ?></td>
                        <td>
                            <div >
                                <img src="<?php echo WP_STATISTICS_URL?>/assets/images/flags/000.svg" alt="">
                                <b>France</b>
                            </div>
                        </td>
                         <td class="wps-pd-l">
                             <span>12,099</span>
                         </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>