/**
 * "WP Statistics" panel in the block editor's Post sidebar.
 *
 * Shows the "Statistics Summary" content (rendered by PHP, see EditorPanel::getPanelData())
 * and a `WPStatisticsPostPanel` slot where add-ons add their own sections:
 *
 *     const { Fill } = window.wpStatistics.PostPanel; // or <wp.components.Fill name="WPStatisticsPostPanel">
 *     wp.plugins.registerPlugin('my-addon', { render: () => <Fill>{ ({ postId }) => ... }</Fill> });
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { createSlotFill } from '@wordpress/components';
import { RawHTML, useEffect, useRef } from '@wordpress/element';
import { useDispatch, useRegistry, useSelect } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import { __ } from '@wordpress/i18n';

const PLUGIN_NAME = 'wp-statistics';
const PANEL_NAME = 'post-panel';
const PANEL_ID = `${PLUGIN_NAME}/${PANEL_NAME}`;

const data = window.wpStatisticsPostPanelData || null;
const SLOT_NAME = (data && data.slot) || 'WPStatisticsPostPanel';
const { Fill, Slot } = createSlotFill(SLOT_NAME);

window.wpStatistics = window.wpStatistics || {};
window.wpStatistics.PostPanel = { Fill, Slot, name: SLOT_NAME };

const icon = (
    <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <rect x="4" y="12" width="3.4" height="8" rx="1" fill="#404BF2" />
        <rect x="10.3" y="7" width="3.4" height="13" rx="1" fill="#404BF2" />
        <rect x="16.6" y="3" width="3.4" height="17" rx="1" fill="#404BF2" />
    </svg>
);

/**
 * The summary markup from PHP plus the mini chart (drawn by admin.min.js).
 */
const Summary = ({ html, chart }) => {
    const ref = useRef(null);

    useEffect(() => {
        if (!chart) {
            return undefined;
        }

        let instance = null;
        let cancelled = false;

        const draw = () => {
            if (cancelled || !ref.current || typeof window.wpStatisticsPostSummaryChart !== 'function') {
                return;
            }
            const canvas = ref.current.querySelector('canvas');
            if (canvas) {
                instance = window.wpStatisticsPostSummaryChart(canvas, chart);
            }
        };

        if (typeof window.wpStatisticsPostSummaryChart === 'function') {
            draw();
        } else if (window.jQuery) {
            // admin.min.js defines the chart helper on document ready.
            window.jQuery(draw);
        }

        return () => {
            cancelled = true;
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        };
    }, [chart]);

    return (
        <div ref={ref} className="wps-post-panel__summary">
            <RawHTML>{html}</RawHTML>
        </div>
    );
};

/**
 * Open the panel the first time a user sees it. After that, the editor remembers
 * whether they keep it open or closed, like every other panel.
 */
const useOpenOnFirstVisit = () => {
    const registry = useRegistry();
    const seen = useSelect((select) => !!select(preferencesStore).get(PLUGIN_NAME, 'postPanelSeen'), []);
    const { set } = useDispatch(preferencesStore);
    const { toggleEditorPanelOpened } = useDispatch('core/editor');

    useEffect(() => {
        // Ask the stores, not this render: with SCRIPT_DEBUG on, React runs the effect twice,
        // and a second toggle would close the panel again.
        if (registry.select(preferencesStore).get(PLUGIN_NAME, 'postPanelSeen')) {
            return;
        }
        set(PLUGIN_NAME, 'postPanelSeen', true);
        if (!registry.select('core/editor').isEditorPanelOpened(PANEL_ID)) {
            toggleEditorPanelOpened(PANEL_ID);
        }
    }, [seen]);
};

const PostPanel = () => {
    useOpenOnFirstVisit();

    return (
        <PluginDocumentSettingPanel
            name={PANEL_NAME}
            title={__('WP Statistics', 'wp-statistics')}
            icon={icon}
            className="wps-post-panel"
        >
            <Summary html={data.html} chart={data.chart} />
            <Slot fillProps={{ postId: data.postId }}>
                {(fills) => (fills && fills.length ? <div className="wps-post-panel__sections">{fills}</div> : null)}
            </Slot>
        </PluginDocumentSettingPanel>
    );
};

if (data) {
    registerPlugin(PLUGIN_NAME, { render: PostPanel, icon });
}
