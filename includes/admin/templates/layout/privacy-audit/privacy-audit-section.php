<div class="wps-privacy-list__item wps-privacy-list__item--<?php echo esc_attr($type_class); ?>">
    <div class="wps-privacy-list__title">
        <div>
            <span class="wps-privacy-list__icon wps-privacy-list__icon--<?php echo esc_attr($icon_class); ?>"></span>
            <span><?php echo $title_text; ?></span>
        </div>
        <?php
        if($button_text):
        if ($button_class === 'success') : ?>
            <span class="wps-privacy-list__button wps-privacy-list__button--<?php echo esc_attr($button_class); ?>"><?php echo $button_text; ?></span>
        <?php else: ?>
            <a class="wps-privacy-list__button wps-privacy-list__button--<?php echo esc_attr($button_class); ?>"><?php echo $button_text; ?></a>
        <?php endif;
        endif; ?>
    </div>
    
    <div class="wps-privacy-list__content">
        <?php echo $content; ?>
    </div>
</div>