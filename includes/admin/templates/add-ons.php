<div id="poststuff" class="wp-statistics-plugins">
    <div id="post-body" class="metabox-holder">
        <div class="wp-list-table widefat widefat plugin-install">
            <form action="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')); ?>" method="post">
                <?php wp_nonce_field('wps_optimization_nonce'); ?>
                <div id="the-list">
                    <?php
                    /* @var $addOns \WP_Statistics\Service\Admin\AddOnDecorator[] */
                    $activatedAddonsCount = \WP_STATISTICS\Service\Admin\AddOnDecorator::countActivatedAddOns();
                    foreach ($addOns as $addOn) : ?>
                        <?php if ($activatedAddonsCount === (count($addOns) - 1) && $addOn->getSlug() == 'add-ons-bundle') continue; ?>
                        <div class="plugin-card">
                            <?php if ($addOn->isFeatured() and $addOn->getFeaturedLabel()) : ?>
                                <div class="cover-ribbon">
                                    <div class="cover-ribbon-inside"><?php echo esc_attr($addOn->getFeaturedLabel()); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a target="_blank" href="<?php echo esc_url($addOn->getUrl()); ?>" class="thickbox open-plugin-details-modal">
                                            <?php echo esc_attr($addOn->getName()); ?>
                                            <img src="<?php echo esc_url($addOn->getIcon()); ?>" class="plugin-icon" alt="<?php echo esc_attr($addOn->getName()); ?>">
                                        </a>
                                    </h3>
                                </div>

                                <div class="desc column-description">
                                    <p><?php echo esc_html(wp_trim_words(wp_kses_post($addOn->getDescription()), 30)); ?></p>

                                    <div class="version">
                                        <strong><?php esc_html_e('Current Version:', 'wp-statistics'); ?></strong><?php echo ' ' . esc_html($addOn->getVersion()); ?>
                                        <div class="status"><strong><?php esc_html_e('Current Status:', 'wp-statistics'); ?></strong>
                                            <span class="<?php echo $addOn->isActivated() ? 'wps-text-success' : 'wps-text-danger'; ?>"><?php echo esc_html($addOn->getStatus()); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="column-downloaded">
                                    <?php if ($addOn->isEnabled()) : ?>
                                        <input type="password" name="licences[<?php echo esc_attr($addOn->getSlug()); ?>]" value="<?php echo esc_attr($addOn->getLicense()); ?>"/>
                                    <?php endif; ?>
                                </div>

                                <div class="column-right-side">
                                    <?php if ($addOn->isEnabled()) { ?>
                                        <input type="submit" class="button" name="update-licence" value="<?php esc_html_e('Update License'); ?>"/>
                                    <?php } else { ?>
                                        <?php if ($addOn->isExist()) { ?>
                                            <a href="<?php echo esc_attr($addOn->getActivateUrl()); ?>" class="button"><?php esc_html_e('Activate Add-On', 'wp-statistics'); ?></a>
                                        <?php } else { ?>
                                            <div class="column-price">
                                                <strong><?php echo wp_kses_post($addOn->getPrice()); ?></strong>
                                            </div><a target="_blank" href="<?php echo esc_url($addOn->getUrl()); ?>" class="button-primary"><?php esc_html_e('Purchase Add-On', 'wp-statistics'); ?></a>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>
</div>
