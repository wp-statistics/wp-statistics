<div class="wps-wrap__top tabbed_page">
    <?php if (!empty($tabs) && is_array($tabs)) { ?>
        <ul class="wps-steps">
            <?php
            $stepBadge       = 1;
            $foundCurrent    = false;

            // Add "completed" class to all tabs until "current" class is found
            // And add "disabled" class to next tabs if the user has no licenses
            // So, for example if the second tab is the current tab, add "completed" to the first tab only, etc.
            foreach ($tabs as $key => $step) {
                // Skip the "Add-Ons" tab
                if ($key !== 0) {
                    $stepClass = esc_attr($step['class']);
                    if ($foundCurrent) {
                        $stepClass   .= ' disabled';
                        $step['link'] = '#';
                    } else if (stripos($stepClass, 'current') !== false) {
                        // Current tab found, don't change the CSS class
                        $foundCurrent = true;
                    } else {
                        // Current tab is not found yet, add "completed" class
                        $stepClass .= 'completed ';
                    }
                    ?>
                    <li class="wps-step-link <?php echo $stepClass; ?>">
                        <a href="<?php echo esc_attr($step['link']); ?>" class="<?php echo esc_attr($step['class'])?>">
                            <span class="wps-step-link__badge"><?php echo $stepBadge; ?></span>
                            <span class="wps-step-link__title"><?php echo esc_html($step['title']); ?></span>
                        </a>
                    </li>
                    <?php
                    $stepBadge++;
                }
            }
            ?>
        </ul>
    <?php } ?>
</div>