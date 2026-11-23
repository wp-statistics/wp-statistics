jQuery(document).ready(function ($) {

    // Image upload field (settings > Customization header logo, Advanced Reporting report logo).
    // Markup: .wps-media-upload > .wps-media-upload__preview (img + remove button) + input group.
    // One media frame is shared; `currentField` points at the field that opened it, so two
    // upload fields on the same page never write into each other.
    var file_frame;
    var currentField = null;

    function setImage($field, url) {
        var $preview = $field.find('.wps-media-upload__preview');
        $field.find('.wps-input-group__field').val(url);
        $field.find('.wps-media-upload__image').attr('src', url || '');
        $preview.prop('hidden', !url);
    }

    $(document.body).on('click', '.wps_img_settings_upload_button', function (e) {
        e.preventDefault();

        currentField = $(this).closest('.wps-media-upload');

        // If the media frame already exists, reopen it.
        if (file_frame) {
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
            // Unset default menu items
            view.unset('library-separator');
            view.unset('gallery');
            view.unset('featured-image');
            view.unset('embed');
            view.unset('playlist');
            view.unset('video-playlist');
            view.set({});
        });

        // When an image is selected, fill the field that opened the frame.
        file_frame.on('insert', function () {
            var attachment = file_frame.state().get('selection').first();
            if (attachment && currentField && currentField.length) {
                setImage(currentField, attachment.toJSON().url);
            }
        });

        file_frame.open();
    });

    $(document.body).on('click', '.wps_img_settings_clear_upload_button', function (e) {
        e.preventDefault();
        var $field = $(this).closest('.wps-media-upload');
        setImage($field, '');
        $field.find('.wps-input-group__field').trigger('focus');
    });

    // A URL typed or pasted by hand updates the preview too.
    $(document.body).on('change', '.wps-media-upload .wps-input-group__field', function () {
        setImage($(this).closest('.wps-media-upload'), $.trim($(this).val()));
    });

});
