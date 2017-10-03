/*
 Function to handle the loading of widget content
 */

function wp_statistics_get_widget_contents(widget, container_id) {
    var data = {
        'action': 'wp_statistics_get_widget_contents',
        'widget': widget,
        'format': 'dashboard'
    };

    container = jQuery("#" + container_id);

    if (container.is(':visible')) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            datatype: 'json',
        })
            .always(function (result) {
                // Take the returned result and add it to the DOM.
                jQuery("#" + container_id).html("").html(result);
            })
            .fail(function (result) {
                // If we failed for some reason, like a timeout, try again.
                container.html(wp_statistics_loading_image);
                wp_statistics_get_widget_contents(widget, container_id);
            });

    }
}

function wp_statistics_refresh_widget() {
    var container_id = this.id.replace('_refresh_button', '');
    container_id = container_id.replace('-widget', '-div');

    var widget = container_id.replace('wp-statistics-', '');
    widget = widget.replace('-div', '');
    widget = widget.replace('-', '.');

    container = jQuery("#" + container_id);

    if (container.is(':visible')) {
        container.html(wp_statistics_loading_image);

        wp_statistics_get_widget_contents(widget, container_id);
    }

    return false;
}

function wp_statistics_refresh_on_toggle_widget() {
    if (this.value.substring(0, 14) != 'wp-statistics-') {
        return;
    }

    var container_id = this.value.replace('-widget', '-div');
    var widget = container_id.replace('wp-statistics-', '');
    widget = widget.replace('-div', '');
    widget = widget.replace('-', '.');

    wp_statistics_get_widget_contents(widget, container_id);
}

function wp_statistics_goto_more() {
    var widget = this.id;

    if (wp_statistics_destinations[widget] !== undefined) {
        window.location.href = wp_statistics_destinations[widget];
    }

    return false;
}

