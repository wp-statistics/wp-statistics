import { registerPlugin } from '@wordpress/plugins';

import Sidebar from './sidebar';

if (typeof (WP_Statistics_Editor_Sidebar_Object) !== 'undefined' && WP_Statistics_Editor_Sidebar_Object != null) {
    registerPlugin('wp-statistics-post-summary', { render: Sidebar });

    window.onload = function () {
        if (!wp.data.select('core/edit-post').isEditorPanelOpened('wp-statistics-post-summary/wp-statistics-post-summary-panel')) {
            wp.data.dispatch('core/edit-post').toggleEditorPanelOpened('wp-statistics-post-summary/wp-statistics-post-summary-panel');
        }
    };
}
