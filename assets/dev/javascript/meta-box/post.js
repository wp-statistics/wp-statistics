wps_js.post_meta_box = {
    params: function () {
        return { 'ID': wps_js.global['page']['ID'] };
    },

    view: function (args = []) {
        return args.hasOwnProperty('content') ?
            ' <div class="wps-center" style="padding: 15px;"> ' + args['content'] + '</div>' : ' <div class="wps-wrap"> ' + args['visitors']  + '</div>';
    },

    meta_box_init: function (args = []) {
        if (args.hasOwnProperty('content')) {
            jQuery("#" + wps_js.getMetaBoxKey('post') + " button[onclick]").remove();
        }
    },
};
