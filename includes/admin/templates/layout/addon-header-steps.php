<div class="wps-wrap__top tabbed_page">
    <?php if (!empty($tabs ) && is_array($tabs )) { ?>
        <ul class="wps-steps">
            <?php
            $index = 1;
            $total_steps = count($tabs);
            $found_current = false;

            foreach ($tabs as $key => $step) {
                // Skip the 'Add-Ons' tab
                if ($step['title'] !== esc_html__('Add-Ons', 'wp-statistics')) :

                    $step_class = esc_attr($step['class']);
                    if ($found_current) {
                        $step_class = $step_class;
                    } elseif ($step_class === 'current') {
                         $found_current = true;
                    } else {
                         $step_class .= 'completed';
                    }
                    ?>
                    <li class="wps-step-link <?php echo $step_class; ?>">
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