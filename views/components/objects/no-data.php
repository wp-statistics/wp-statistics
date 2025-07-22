<div class="wps--no-data__container" aria-live="polite">
    <?php if(isset($url)):?>
    <img class="wps--no-data__image" src="<?php echo esc_url($url) ?>" role="presentation" alt="<?php echo esc_attr(sprintf(__('Illustration for %s', 'wp-statistics'), $title)) ?>">
    <?php endif ?>
    <p class="<?php echo isset($url) ? 'wps--no-data__text'  : 'o-wrap o-wrap--no-data wps-center'?>"><?php echo esc_html($title)?></p>
</div>