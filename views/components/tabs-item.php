<a class="wps-tabs-item" href="<?php echo esc_url($href) ?>">
    <div class="wps-content-tabs__item--image">
        <span>#<?php echo esc_html($counter); ?></span>
        <?php if (has_post_thumbnail(esc_html($post->ID))) : ?>
            <img src="<?php echo esc_url(get_the_post_thumbnail_url(esc_html($post->ID))) ?>" alt="<?php echo esc_attr($post->post_title) ?>">
        <?php else : ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none">
                <g clip-path="url(#clip0_9208_25189)">
                    <path d="M0 0H140V140H0V0Z" fill="#E0E0E0"/>
                    <path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2"/>
                </g>
                <defs>
                    <clipPath id="clip0_9208_25189">
                        <rect width="140" height="140" fill="white"/>
                    </clipPath>
                </defs>
            </svg>
        <?php endif; ?>
    </div>
    <div class="wps-content-tabs__item--content">
        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($post->post_title); ?></span></h3>
        <span><?php echo esc_html($count);?> <?php echo esc_html($count_title); ?></span>
    </div>
</a>