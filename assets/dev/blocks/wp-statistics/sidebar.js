import { __ } from '@wordpress/i18n';

import { PluginDocumentSettingPanel } from '@wordpress/editor';

import './style.scss';

export default function sidebar() {
    return (
        <PluginDocumentSettingPanel
            className="wp-statistics-block-editor-panel"
            title={__('WP Statistics', 'wp-statistics')} >
            <p>Over the past week (August 03 - August 09), this post has been viewed 200 times by 150 visitors. The top referrer domain is 'example.com' with 50 visits. In total, it has been viewed 1,000 times by 700 visitors, with 'example.com' leading with 300 referrals. For more detailed insights, visit the analytics section.</p>
        </PluginDocumentSettingPanel>
    )
}
