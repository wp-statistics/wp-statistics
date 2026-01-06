/**
 * WP Statistics Block
 *
 * Gutenberg block for displaying statistics.
 * Uses ServerSideRender for preview in editor.
 *
 * @since 15.0.0
 */
(function(wp) {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var Disabled = wp.components.Disabled;
    var __ = wp.i18n.__;

    // Get block data from localized script
    var blockData = window.wpStatisticsBlockData || {};
    var stats = blockData.stats || [];
    var timeBasedStats = blockData.timeBasedStats || [];

    // Build stat options for SelectControl
    var statOptions = stats.map(function(stat) {
        return {
            label: stat.label,
            value: stat.value
        };
    });

    // Time options
    var timeOptions = [
        { label: __('Today', 'wp-statistics'), value: 'today' },
        { label: __('Yesterday', 'wp-statistics'), value: 'yesterday' },
        { label: __('This Week', 'wp-statistics'), value: 'week' },
        { label: __('This Month', 'wp-statistics'), value: 'month' },
        { label: __('This Year', 'wp-statistics'), value: 'year' },
        { label: __('All Time', 'wp-statistics'), value: 'total' }
    ];

    // Provider options (for searches stat)
    var providerOptions = [
        { label: __('All Search Engines', 'wp-statistics'), value: 'all' },
        { label: __('Google', 'wp-statistics'), value: 'google' },
        { label: __('Bing', 'wp-statistics'), value: 'bing' },
        { label: __('Yahoo', 'wp-statistics'), value: 'yahoo' },
        { label: __('DuckDuckGo', 'wp-statistics'), value: 'duckduckgo' },
        { label: __('Yandex', 'wp-statistics'), value: 'yandex' }
    ];

    // Format options
    var formatOptions = [
        { label: __('Localized (i18n)', 'wp-statistics'), value: 'i18n' },
        { label: __('English', 'wp-statistics'), value: 'english' },
        { label: __('Abbreviated (1K, 1M)', 'wp-statistics'), value: 'abbreviated' },
        { label: __('None', 'wp-statistics'), value: 'none' }
    ];

    // Layout options
    var layoutOptions = [
        { label: __('Card', 'wp-statistics'), value: 'card' },
        { label: __('Inline', 'wp-statistics'), value: 'inline' },
        { label: __('Minimal', 'wp-statistics'), value: 'minimal' }
    ];

    // Check if stat needs time control
    function showTimeControl(stat) {
        return timeBasedStats.indexOf(stat) !== -1;
    }

    // Check if stat needs provider control
    function showProviderControl(stat) {
        return stat === 'searches';
    }

    registerBlockType('wp-statistics/statistics', {
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps();

            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: __('Statistics Settings', 'wp-statistics'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Statistic', 'wp-statistics'),
                            value: attributes.stat,
                            options: statOptions,
                            onChange: function(value) {
                                setAttributes({ stat: value });
                            }
                        }),
                        showTimeControl(attributes.stat) && el(SelectControl, {
                            label: __('Time Frame', 'wp-statistics'),
                            value: attributes.time,
                            options: timeOptions,
                            onChange: function(value) {
                                setAttributes({ time: value });
                            }
                        }),
                        showProviderControl(attributes.stat) && el(SelectControl, {
                            label: __('Search Engine', 'wp-statistics'),
                            value: attributes.provider,
                            options: providerOptions,
                            onChange: function(value) {
                                setAttributes({ provider: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Number Format', 'wp-statistics'),
                            value: attributes.format,
                            options: formatOptions,
                            onChange: function(value) {
                                setAttributes({ format: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Layout', 'wp-statistics'),
                            value: attributes.layout,
                            options: layoutOptions,
                            onChange: function(value) {
                                setAttributes({ layout: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show Label', 'wp-statistics'),
                            checked: attributes.showLabel,
                            onChange: function(value) {
                                setAttributes({ showLabel: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show Icon', 'wp-statistics'),
                            checked: attributes.showIcon,
                            onChange: function(value) {
                                setAttributes({ showIcon: value });
                            }
                        })
                    )
                ),
                el(
                    Disabled,
                    null,
                    el(ServerSideRender, {
                        block: 'wp-statistics/statistics',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            // Server-side rendered block
            return null;
        }
    });
})(window.wp);
