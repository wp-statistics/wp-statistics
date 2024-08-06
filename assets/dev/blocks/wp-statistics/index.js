import { registerPlugin } from '@wordpress/plugins';

import Sidebar from './sidebar';

registerPlugin('wp-statistics', { render: Sidebar });

toggleEditorPanelOpened('wp-statistics-block-editor-panel');
