<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".postbox :not(.postbox-toggle) button.handlediv").click(function () {
            $(this).parent().parent().toggleClass("closed");
        });
        $(".postbox .postbox-toggle").click(function () {
            $(this).parent().toggleClass("closed");
        });
    });
</script>