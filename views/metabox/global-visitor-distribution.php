<div class="o-wrap">
    <div id="wp-statistics-visitors-map__container">
        <div id="wp-statistics-visitors-map"></div>
    </div>
    <?php if (isset($data['data']) && !empty($data['data'])): ?>
    <div class="wps-map-info">
        <span>0</span>
        <span class="wps-map-info__color"></span>
        <span><?php echo max($data['data']); ?></span>
    </div>
    <?php endif; ?>
</div>