<div class="wps-audit-card wps-audit-card--<?php echo esc_html($status)?>">
    <div class="wps-audit-card__header">
        <div class="wps-audit-card__top">
            <div class="wps-audit-card__details">
                <div class="wps-audit-card__icon"><?php echo $svg ?></div>
                <div>
                    <h3 class="wps-audit-card__title"><?php echo esc_html($title) ?></h3>
                    <?php if (isset($description)) : ?>
                        <p class="wps-audit-card__summary"><?php echo esc_html($description) ?></p>
                    <?php endif; ?>
                </div>

            </div>
            <div class="wps-audit-card__status">
                <span class="wps-audit-card__status-indicator"></span>
                <?php if (($status === 'danger') || ($status === 'warning')) : ?>
                    <span class="wps-audit-card__status-text"> <?php echo $status === 'danger' ? esc_html__('Attention Needed', 'wp-statistics'): esc_html__('Warning', 'wp-statistics') ?> </span>
                <?php endif; ?>

                <button class="wps-audit-card__toggle" aria-expanded="false"></button>
            </div>

        </div>

    </div>
    <div class="wps-audit-card__body">
        <div class="wps-audit-card__content-text"><?php echo $content ?></div>
        <?php if (isset($suggestion)) : ?>
            <div class="wps-audit-card__suggestion">
                <div class="wps-audit-card__suggestion-head">
                    <?php echo esc_html__('Suggestion', 'wp-statistics') ?>
                </div>
                <p class="wps-audit-card__suggestion-text"><?php echo $suggestion ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
