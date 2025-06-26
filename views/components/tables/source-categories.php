<?php
$source_colors = [
    '#4285F4',
    '#9C27B0',
    '#34A853',
    '#FBBC05',
    '#9E9E9E',
];
?>
<div class="inside">
    <?php if (!empty($data)): ?>
        <div class="o-table-wrapper wps-source-category">
            <table class="o-table wps-new-table wps-source-category__table">
                <thead>
                <tr>
                    <th scope="col" class="wps-pd-l"><?php echo esc_html_e('Source Category', 'wp-statistics') ?></th>
                    <th scope="col" class="wps-pd-l"><?php echo esc_html_e('Top Domain', 'wp-statistics') ?></th>
                    <th scope="col" class="wps-pd-l"><?php echo esc_html_e('Visitors', 'wp-statistics') ?></th>
                    <th scope="col" class="wps-pd-l">%</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $index => $row):
                    $color = $source_colors[$index] ?? '#4285F4'; // default color
                    ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-source-category__bg" style="border-color:<?php echo $color; ?>;color: <?php echo $color; ?>;"></div>
                            <span><?php echo htmlspecialchars($row['source_category']); ?></span>
                            <p class="wps-ellipsis-parent <?php echo ($row['top_domain'] === '-') ? 'wps-hidden' : ''; ?>" title="<?php echo htmlspecialchars($row['top_domain']); ?>">
                                <span class="wps-ellipsis-text"><?php echo htmlspecialchars($row['top_domain']); ?></span>
                            </p>
                        </td>
                        <td class="wps-pd-l"><span><?php echo htmlspecialchars($row['top_domain']); ?></span></td>
                        <td class="wps-pd-l"><span><?php echo esc_html($row['visitors']); ?></span></td>
                        <td class="wps-pd-l"><span><?php echo htmlspecialchars($row['percentage']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>
