<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title_text) ?>
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
            <table width="100%" class="o-table wps-authors-table">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Time', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitors', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l start">
                            <?php esc_html_e('Views ', 'wp-statistics') ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php for ($i = 1; $i < 10; $i++): ?>
                        <tr>
                            <td><b>This year (Jan-Today)</b></td>
                            <td class="wps-pd-l">8,834</td>
                            <td class="wps-pd-l start">12,099</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>