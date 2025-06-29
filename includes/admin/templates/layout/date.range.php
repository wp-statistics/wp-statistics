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
        </select><input type="submit" value="<?php esc_html_e('Select', 'wp-statistics'); ?>" class="button-primary btn-danger wps-btn-inline"><br/>
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
        <input type="hidden" name="<?php echo esc_attr(\WP_STATISTICS\Admin_Template::$request_from_date); ?>" id="date-from" value="<?php echo esc_attr($DateRang['from']); ?>">
    <?php } ?>
    <?php if (!empty($DateRang['to'])) { ?>
        <input type="hidden" name="<?php echo esc_attr(\WP_STATISTICS\Admin_Template::$request_to_date); ?>" id="date-to" value="<?php echo esc_attr($DateRang['to']); ?>">
    <?php } ?>
</form>

<div class="c-pages-date-range">
    <div class="c-footer__filter js-pages-date-range-picker">
        <div class="c-footer__filter__btn-group">
            <?php if (isset($hasDateRang)): ?>
                <button  data-date-format="<?php echo str_replace('F', 'M', get_option('date_format')) ?>"
                    class="c-footer__filter__btn js-date-range-picker-btn
                    <?php echo isset($allTimeOption) && $allTimeOption === true ? 'js-date-range-picker-all-time' : ''; ?>">
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
                <span><?php echo esc_html__('Last 30 Days', 'wp-statistics') ?></span>
            </button>
            <?php endif ?>
        </div>
        <input type="text" class="c-footer__filters__custom-date-input js-date-range-picker-input" aria-label="Select custom date range">
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
        <?php if (!empty($DateRang['from'])) { ?>
            <input type="hidden" name="<?php echo esc_attr(\WP_STATISTICS\Admin_Template::$request_from_date); ?>" class="js-date-range-picker-input-from" value="<?php echo esc_attr($DateRang['from']); ?>">
        <?php } ?>
        <?php if (!empty($DateRang['to'])) { ?>
            <input type="hidden" name="<?php echo esc_attr(\WP_STATISTICS\Admin_Template::$request_to_date); ?>" class="js-date-range-picker-input-to" value="<?php echo esc_attr($DateRang['to']); ?>">
        <?php } ?>
    </form>
</div>

