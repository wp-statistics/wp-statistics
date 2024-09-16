<div class="wps-wrap__top tabbed_page">
    <?php if (!empty($tabs ) && is_array($tabs )) { ?>
        <ul class="wps-steps">
            <?php
            $index = 1;
            foreach ($tabs as $step) {
                if ($step['title'] !== esc_html__('Add-Ons', 'wp-statistics')) :
                    ?>
                    <li class="wps-step-link <?php echo esc_attr($step['class']); ?>">
                        <a href="<?php echo esc_attr($step['link']); ?>">
                            <span class="wps-step-link__badge"><?php echo $index; ?></span>
                            <span class="wps-step-link__title"><?php echo esc_html($step['title']); ?></span>
                        </a>
                    </li>
                    <?php
                    $index++;
                endif;
            }
            ?>
        </ul>

    <?php } ?>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>