<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($rows)) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                                <thead>
                                <tr>
                                    <?php foreach ($headers as $header) : ?>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url($header['sort_url']); ?>" class="sort <?php echo esc_attr($header['order_class']); ?>">
                                                <?php echo esc_html($header['label']); ?>
                                            </a>
                                        </th>
                                    <?php endforeach; ?>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $row) : ?>
                                    <tr>
                                        <?php foreach ($row['columns'] as $column) : ?>
                                            <td class="wps-pd-l">
                                                <?php echo $column; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="view-more view-more__arrow wps-pd-l">
                                            <a target="_blank" href="<?php echo esc_url($row['view_more_link']); ?>"><?php echo esc_html($row['view_more_text']); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo $pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>

