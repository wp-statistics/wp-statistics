<?php 
use WP_STATISTICS\Country;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['countries'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Country', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
    
                                <tbody>

                                    <?php foreach ($data['countries'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                    <img alt="<?php echo esc_attr(Country::getName($item->country)) ?>" src="<?php echo esc_url(Country::flag($item->country)) ?>" title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="log-tools wps-flag"/>
                                                    <?php echo esc_html(Country::getName($item->country)) ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->visitors)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->views)) ?>
                                            </td>
                                            <td class=" -table__cell o-table__cell--right">
                                                <a class="button disabled" href="#" disabled="">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="12" viewBox="0 0 13 12" fill="none">
                                                        <path d="M6.51172 10.875C8.24609 10.875 9.83984 9.96094 10.7305 8.4375C11.5977 6.9375 11.5977 5.08594 10.7305 3.5625C9.83984 2.0625 8.24609 1.125 6.51172 1.125C4.75391 1.125 3.16016 2.0625 2.26953 3.5625C1.40234 5.08594 1.40234 6.9375 2.26953 8.4375C3.16016 9.96094 4.75391 10.875 6.51172 10.875ZM6.51172 0C8.64453 0 10.6133 1.14844 11.6914 3C12.7695 4.875 12.7695 7.14844 11.6914 9C10.6133 10.875 8.64453 12 6.51172 12C4.35547 12 2.38672 10.875 1.30859 9C0.230469 7.14844 0.230469 4.875 1.30859 3C2.38672 1.14844 4.35547 0 6.51172 0ZM7.26172 6C7.26172 6.42188 6.91016 6.75 6.51172 6.75C6.08984 6.75 5.76172 6.42188 5.76172 6C5.76172 5.60156 6.08984 5.25 6.51172 5.25C6.91016 5.25 7.26172 5.60156 7.26172 6ZM8.76172 5.25C9.16016 5.25 9.51172 5.60156 9.51172 6C9.51172 6.42188 9.16016 6.75 8.76172 6.75C8.33984 6.75 8.01172 6.42188 8.01172 6C8.01172 5.60156 8.33984 5.25 8.76172 5.25ZM5.01172 6C5.01172 6.42188 4.66016 6.75 4.26172 6.75C3.83984 6.75 3.51172 6.42188 3.51172 6C3.51172 5.60156 3.83984 5.25 4.26172 5.25C4.66016 5.25 5.01172 5.60156 5.01172 6Z" fill="#A9AAAE"></path>
                                                    </svg>
                                                    <span class="hover-effect hover-effect--hide"><?php esc_html_e('View Details', 'wp-statistics') ?></span>
                                                    <span class="hover-effect hover-effect--show"><?php esc_html_e('Coming Soon', 'wp-statistics') ?></span>
                                                </a>
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
            </div>
        </div>
    </div>
</div>