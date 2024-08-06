import { registerPlugin } from '@wordpress/plugins';

import Sidebar from './sidebar';

registerPlugin('wp-statistics', { render: Sidebar });
