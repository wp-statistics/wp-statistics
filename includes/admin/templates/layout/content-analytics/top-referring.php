<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
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
            <table width="100%" class="o-table wps-content-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Referrer Name', 'wp-statistics') ?>
                        <span class="wps-tooltip" title="Referrer Name tooltip"><i class="wps-tooltip-icon info"></i></span>
                    </th>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Domain Address', 'wp-statistics') ?>
                        <span class="wps-tooltip" title="Referring Site tooltip"><i class="wps-tooltip-icon info"></i></span>
                    </th>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Number of Refers', 'wp-statistics') ?>
                        <span class="wps-tooltip" title="Number of Refers tooltip"><i class="wps-tooltip-icon info"></i></span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 1; $i < 10; $i++): ?>
                    <tr>
                        <td class="wps-pd-l">
                            google
                        </td>
                        <td class="wps-pd-l">
                            <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/search-engine/google.png'); ?>" title="google" class="log-tools wps-flag">
                            www.google.com
                        </td>
                        <td class="wps-pd-l">8,834</td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>