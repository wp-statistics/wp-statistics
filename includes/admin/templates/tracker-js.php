<script>
    <?php if($dntEnabled) : ?>
    let WP_Statistics_Dnd_Active = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);
    if (WP_Statistics_Dnd_Active !== 1) {
        <?php endif; ?>
        var WP_Statistics_http = new XMLHttpRequest();
        WP_Statistics_http.open("GET", "<?php echo $requestUrl; ?>" + "&referred=" + encodeURIComponent(document.referrer) + "&_=" + Date.now(), true);
        WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        WP_Statistics_http.send(null);
        <?php if($dntEnabled) : ?>
    }
    <?php endif; ?>
</script>