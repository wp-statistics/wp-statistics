<?php
use WP_STATISTICS\Menus;

if (Menus::in_page('overview')) {
    $view_text = __('View Content', 'wp-statistics');
} else {
    $view_text = __('View', 'wp-statistics');
}
?>

<div class="o-table-wrapper">
    <table width="100%" class="o-table wps-new-table wps-new-table__most-visited">
        <thead>
        <tr>
            <th class="wps-pd-l"><?php esc_html_e('Page', 'wp-statistics'); ?></th>
            <th class="wps-pd-l"><span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span></th>
            <th class="wps-pd-l"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="wps-pd-l">
                <div class="wps-ellipsis-parent" title="Home Page: Home"><span class="wps-ellipsis-text">Home Page: Home</span></div>
            </td>
            <td class="wps-pd-l"><a href="" title="" target="_blank">11</a></td>
            <td class="wps-pd-l wps-middle-vertical">
                <a target="_blank" class="wps-view-content" href=""><?php echo esc_html($view_text) ?></a>
            </td>
        </tr>
        <tr>
            <td class="wps-pd-l">
                <div class="wps-ellipsis-parent" title="Home Page: Home"><span class="wps-ellipsis-text">Home Page: Home</span></div>
            </td>
            <td class="wps-pd-l"><a href="" title="" target="_blank">11</a></td>
            <td class="wps-pd-l wps-middle-vertical">
                <a target="_blank" class="wps-view-content" href=""><?php echo esc_html($view_text); ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>