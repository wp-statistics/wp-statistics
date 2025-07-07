<div class="wps--no-data__container">
    <?php if(isset($url)):?>
    <img class="wps--no-data__image" src="<?php echo esc_url($url) ?>" alt="<?php echo esc_html($title)?>">
    <?php endif ?>
    <p class="<?php echo isset($url) ? 'wps--no-data__text'  : 'o-wrap o-wrap--no-data wps-center'?>"><?php echo esc_html($title)?></p>
</div>