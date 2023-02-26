<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" class="wps-inline" id="jquery-datepicker">
    <?php
    if (isset($select_box)) {
        ?>
        <br/>
        <?php echo esc_attr($select_box['title']); ?>:&nbsp;<select name="<?php echo esc_attr($select_box['name']); ?>" id="<?php echo esc_attr($select_box['name']); ?>">
            <?php
            foreach ($select_box['list'] as $value => $name) {
                $selected = ((isset($select_box['active']) and $select_box['active'] == $value) ? ' selected' : '');
                ?>
                <option value="<?php echo esc_attr($value); ?>"<?php echo esc_attr($selected); ?>><?php echo esc_attr($name); ?></option>
                <?php
            }
            ?>
        </select><input type="submit" value="<?php _e('Select', 'wp-statistics'); ?>" class="button-primary btn-danger wps-btn-inline"><br/>
        <?php
    }
    ?>

    <!-- Set Page name To Form -->
    <input name="page" type="hidden" value="<?php echo esc_attr($pageName); ?>">

    <!-- Set Custom Input -->
    <?php
    if (isset($custom_get)) {
        foreach ($custom_get as $key => $val) {
            ?>
            <input name="<?php echo esc_attr($key); ?>" type="hidden" value="<?php echo esc_attr($val); ?>">
            <?php
        }
    }
    ?>
    <?php if (!empty($DateRang['from'])) { ?>
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_from_date; ?>" id="date-from" value="<?php echo esc_attr($DateRang['from']); ?>">
    <?php } ?>
    <?php if (!empty($DateRang['to'])) { ?>
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_to_date; ?>" id="date-to" value="<?php echo esc_attr($DateRang['to']); ?>">
    <?php } ?>
</form>

<div class="c-pages-date-range">
    <div class="c-footer__filter js-pages-date-range-picker">
        <div class="c-footer__filter__btn-group">
            <button onclick="jQuery('.ranges li').map((key, value) => { if(value.classList.contains('active')) { const prevDateRange = jQuery('.ranges li')[key - 1]; prevDateRange.click();}})" class="c-footer__filter__btn c-footer__filter__btn--sm">
                <svg width="6" height="10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="a" fill="#fff">
                        <path d="M4.951.133.406 4.68a.454.454 0 0 0 0 .643L4.95 9.867a.454.454 0 1 0 .643-.643L1.37 5 5.594.776a.453.453 0 0 0 0-.643.455.455 0 0 0-.643 0Z"/>
                    </mask>
                    <path d="M4.951.133.406 4.68a.454.454 0 0 0 0 .643L4.95 9.867a.454.454 0 1 0 .643-.643L1.37 5 5.594.776a.453.453 0 0 0 0-.643.455.455 0 0 0-.643 0Z" fill="#666"/>
                    <path d="m4.951.133-.707-.707.707.707ZM.406 4.68l.707.707-.707-.707Zm0 .643-.707.707.707-.707ZM4.95 9.867l.707-.707-.707.707Zm.643-.643.707-.707-.707.707ZM1.37 5l-.707-.707L-.044 5l.707.707L1.37 5ZM5.594.776 4.887.069l.707.707Zm0-.643L4.887.84l.707-.707Zm-1.35-.707L-.3 3.972l1.414 1.414L5.658.84 4.244-.574ZM-.3 3.971a1.454 1.454 0 0 0-.426 1.03h2a.546.546 0 0 1-.16.385L-.301 3.971Zm-.426 1.03c0 .385.153.755.426 1.028l1.414-1.415c.102.103.16.241.16.386h-2Zm.426 1.028 4.545 4.545L5.658 9.16 1.113 4.614-.301 6.03Zm4.545 4.545a1.455 1.455 0 0 0 2.057 0L4.887 9.16a.545.545 0 0 1 .771 0l-1.414 1.414Zm2.057 0a1.455 1.455 0 0 0 0-2.057L4.887 9.93a.545.545 0 0 1 0-.771l1.414 1.414Zm0-2.057L2.077 4.293.663 5.707l4.224 4.224 1.414-1.414Zm-4.224-2.81 4.224-4.224L4.887.07.663 4.293l1.414 1.414Zm4.224-4.224c.284-.284.426-.658.426-1.028h-2c0-.138.054-.28.16-.386l1.414 1.414ZM6.727.455c0-.371-.142-.745-.426-1.03L4.887.84a.547.547 0 0 1-.16-.385h2Zm-.426-1.03a1.455 1.455 0 0 0-2.057.001L5.658.84a.545.545 0 0 1-.77 0L6.3-.574Z" fill="#666" mask="url(#a)"/>
                </svg>
            </button>
            <button class="c-footer__filter__btn js-date-range-picker-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none">
                    <g clip-path="url(#A)" stroke="#666" stroke-linejoin="round">
                        <path d="M13 2.5H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5z"/>
                        <g stroke-linecap="round">
                            <path d="M11 1.5v2m-6-2v2m-2.5 2h11"/>
                        </g>
                    </g>
                    <defs>
                        <clipPath id="A">
                            <path fill="#fff" d="M0 0h16v16H0z"/>
                        </clipPath>
                    </defs>
                </svg>
                <span><?php echo __('Last 30 Days', 'wp-statistics') ?></span>
            </button>
            <button onclick="jQuery('.ranges li').map((key, value) => { if(value.classList.contains('active')) { const prevDateRange = jQuery('.ranges li')[key + 1]; prevDateRange.click(); }})" class="c-footer__filter__btn c-footer__filter__btn--sm">
                <svg width="6" height="10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="a" fill="#fff">
                        <path d="M1.049 9.867 5.594 5.32a.454.454 0 0 0 0-.643L1.05.133a.455.455 0 1 0-.643.643L4.63 5 .406 9.224a.453.453 0 0 0 0 .643.455.455 0 0 0 .643 0Z"/>
                    </mask>
                    <path d="M1.049 9.867 5.594 5.32a.454.454 0 0 0 0-.643L1.05.133a.455.455 0 1 0-.643.643L4.63 5 .406 9.224a.453.453 0 0 0 0 .643.455.455 0 0 0 .643 0Z" fill="#666"/>
                </svg>
            </button>
        </div>
        <input type="text" class="c-footer__filters__custom-date-input js-date-range-picker-input">
    </div>
    <form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" style="display: none" class="js-date-range-picker-form">
        <input name="page" type="hidden" value="<?php echo esc_attr($pageName); ?>">
        <?php
        if (isset($custom_get)) {
            foreach ($custom_get as $key => $val) {
                ?>
                <input name="<?php echo esc_attr($key); ?>" type="hidden" value="<?php echo esc_attr($val); ?>">
                <?php
            }
        }
        ?>
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_from_date; ?>" class="js-date-range-picker-input-from" value="<?php echo esc_attr($DateRang['from']); ?>">
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_to_date; ?>" class="js-date-range-picker-input-to" value="<?php echo esc_attr($DateRang['to']); ?>">
    </form>
</div>

<?php
if (isset($filter) and isset($filter['code'])) {
    echo $filter['code']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>
    <div class="wp-clearfix"></div>
    <?php
}
?>
