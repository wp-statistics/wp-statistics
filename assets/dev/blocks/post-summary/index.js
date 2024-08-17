import { registerPlugin } from '@wordpress/plugins';

import Sidebar from './sidebar';

registerPlugin('wp-statistics', { render: Sidebar });

window.onload = function () {
    if (!wp.data.select('core/edit-post').isEditorPanelOpened('wp-statistics/wp-statistics-block-editor-panel')) {
        wp.data.dispatch('core/edit-post').toggleEditorPanelOpened('wp-statistics/wp-statistics-block-editor-panel');
    }
};
