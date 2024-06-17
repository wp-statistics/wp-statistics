jQuery(document).ready(function ($) {

// WP 3.5+ uploader
    var file_frame;

    $(document.body).on('click', '.wps_img_settings_upload_button', function (e) {

        e.preventDefault();

        var button = $(this);

        // If the media frame already exists, reopen it.
        if (file_frame) {
            //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            frame: 'post',
            state: 'insert',
            library: {
                type: ['image']
            },
            multiple: false
        });

        file_frame.on('menu:render:default', function (view) {
            // Store our views in an object.
            var views = {};

            // Unset default menu items
            view.unset('library-separator');
            view.unset('gallery');
            view.unset('featured-image');
            view.unset('embed');
            view.unset('playlist');
            view.unset('video-playlist');

            // Initialize the views in our view object.
            view.set(views);
        });

        // When an image is selected, run a callback.
        file_frame.on('insert', function () {

            var selection = file_frame.state().get('selection');
            selection.each(function (attachment, index) {

                attachment = attachment.toJSON();
                button.parent().parent().find('input[type="text"]').val(attachment.url);
                button.parent().parent().parent().find('#wps-upload-image-preview').attr('src', attachment.url);
                button.parent().parent().find('.wps_img_settings_clear_upload_button').show();
            });
        });

        // Finally, open the modal
        file_frame.open();
    });


    $(document.body).on('click', '.wps_img_settings_clear_upload_button', function (e) {

        e.preventDefault();

        $(this).parent().prev().val('');
        $('#wps-upload-image-preview').attr('src', wps_ar_vars.default_avatar_url);
        $('.wps_img_settings_clear_upload_button').hide();

    });

});