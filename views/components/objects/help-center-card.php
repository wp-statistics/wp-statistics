<div class="wps-help__card">
    <div class="wps-help__card-icon">
        <?php echo wp_kses(
            $icon,
            array(
                'svg'  => array(
                    'class'       => true,
                    'xmlns'       => true,
                    'width'       => true,
                    'height'      => true,
                    'viewBox'     => true,
                    'aria-hidden' => true,
                    'role'        => true,
                    'focusable'   => true,
                    'fill'        => true,
                ),
                'path' => array(
                    'd'               => true,
                    'fill'            => true,
                    'stroke'          => true,
                    'stroke-width'    => true,
                    'stroke-linecap'  => true,
                    'stroke-linejoin' => true,
                ),
                'g'    => array(
                    'fill'      => true,
                    'stroke'    => true,
                    'transform' => true,
                ),
            )
        );
        ?>
    </div>
    <span class="wps-help__card-category"><?php echo esc_html($category) ?></span>
    <h2 class="wps-help__card-title"><?php echo esc_html($title) ?></h2>
    <p class="wps-help__card-description"><?php echo esc_html($description); ?></p>
    <?php if (isset($view_more_link)): ?>
        <a href="<?php echo esc_url($view_more_link); ?>" target="_blank" class="wps-help__card-link">
            <?php echo esc_html($view_more_title); ?>
        </a>
    <?php endif; ?>
    <?php if (isset($social)): ?>
        <ul class="wps-help__card-socials">
            <li><a aria-label="WP Statistics on Linkedin" href="https://www.linkedin.com/showcase/wp-statistics" target="_blank" class="wps-help__card-social wps-help__card-social--linkedin"></a></li>
            <li><a aria-label="WP Statistics on Twitter" href="https://x.com/wp_statistics" target="_blank" class="wps-help__card-social wps-help__card-social--x"></a></li>
            <li><a aria-label="WP Statistics on Instagram" href="http://instagram.com/veronalabs" target="_blank" class="wps-help__card-social wps-help__card-social--instagram"></a></li>
            <li><a aria-label="WP Statistics on Github" href="https://github.com/wp-statistics" target="_blank" class="wps-help__card-social wps-help__card-social--github"></a></li>
        </ul>
    <?php endif; ?>
</div>