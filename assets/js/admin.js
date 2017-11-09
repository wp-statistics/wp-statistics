jQuery(document).ready(function(){
    jQuery('.wp-statistics-settings ul.tabs li').click(function(){
        var tab_id = jQuery(this).attr('data-tab');

        jQuery('.wp-statistics-settings ul.tabs li').removeClass('current');
        jQuery('.wp-statistics-settings .tab-content').removeClass('current');

        jQuery(this).addClass('current');
        jQuery("#"+tab_id).addClass('current');
    })
});