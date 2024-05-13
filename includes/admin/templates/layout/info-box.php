<div class="wps-info-box">
    <?php if (isset($infoTitle)) : ?>
        <div class="wps-info-box__title">
            <?php echo esc_attr($infoTitle); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($infoText)) : ?>
        <div class="wps-info-box__content">
            <?php echo $infoText; ?>
        </div>
    <?php endif; ?>
</div>