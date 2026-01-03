/**
 * WP Statistics Block
 *
 * Gutenberg block for displaying WP Statistics data.
 *
 * @since 15.0.0
 */

(function (wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const {
        PanelBody,
        SelectControl,
        ToggleControl,
        Placeholder,
        Spinner,
    } = wp.components;
    const { __ } = wp.i18n;
    const { useState, useEffect } = wp.element;
    const { apiFetch } = wp;

    // Get block data from PHP
    const blockData = window.wpStatisticsBlockData || {};

    // Stat options
    const statOptions = [
        { value: 'usersonline', label: __('Online Visitors', 'wp-statistics') },
        { value: 'visits', label: __('Total Views', 'wp-statistics') },
        { value: 'visitors', label: __('Total Visitors', 'wp-statistics') },
        { value: 'pagevisits', label: __('Page Views', 'wp-statistics') },
        { value: 'pagevisitors', label: __('Page Visitors', 'wp-statistics') },
        { value: 'searches', label: __('Search Queries', 'wp-statistics') },
        { value: 'referrer', label: __('Referrers', 'wp-statistics') },
        { value: 'postcount', label: __('Post Count', 'wp-statistics') },
        { value: 'pagecount', label: __('Page Count', 'wp-statistics') },
        { value: 'commentcount', label: __('Comment Count', 'wp-statistics') },
        { value: 'usercount', label: __('User Count', 'wp-statistics') },
    ];

    // Time options
    const timeOptions = [
        { value: 'today', label: __('Today', 'wp-statistics') },
        { value: 'yesterday', label: __('Yesterday', 'wp-statistics') },
        { value: 'week', label: __('This Week', 'wp-statistics') },
        { value: 'month', label: __('This Month', 'wp-statistics') },
        { value: 'year', label: __('This Year', 'wp-statistics') },
        { value: 'total', label: __('All Time', 'wp-statistics') },
    ];

    // Format options
    const formatOptions = [
        { value: 'none', label: __('None', 'wp-statistics') },
        { value: 'i18n', label: __('Internationalized', 'wp-statistics') },
        { value: 'english', label: __('English', 'wp-statistics') },
        { value: 'abbreviated', label: __('Abbreviated (1K, 1M)', 'wp-statistics') },
    ];

    // Layout options
    const layoutOptions = [
        { value: 'card', label: __('Card', 'wp-statistics') },
        { value: 'inline', label: __('Inline', 'wp-statistics') },
        { value: 'minimal', label: __('Minimal', 'wp-statistics') },
    ];

    // Icon map
    const iconMap = {
        usersonline: 'dashicons-admin-users',
        visits: 'dashicons-visibility',
        visitors: 'dashicons-groups',
        pagevisits: 'dashicons-analytics',
        pagevisitors: 'dashicons-businessman',
        searches: 'dashicons-search',
        referrer: 'dashicons-admin-links',
        postcount: 'dashicons-admin-post',
        pagecount: 'dashicons-admin-page',
        commentcount: 'dashicons-admin-comments',
        spamcount: 'dashicons-warning',
        usercount: 'dashicons-admin-users',
    };

    // Register the block
    registerBlockType('wp-statistics/statistics', {
        title: __('WP Statistics', 'wp-statistics'),
        description: __('Display statistics from WP Statistics.', 'wp-statistics'),
        category: 'widgets',
        icon: 'chart-pie',
        keywords: [
            __('statistics', 'wp-statistics'),
            __('analytics', 'wp-statistics'),
            __('visitors', 'wp-statistics'),
        ],
        supports: {
            html: false,
            align: ['wide', 'full'],
            className: true,
            anchor: true,
        },
        attributes: {
            stat: {
                type: 'string',
                default: 'visitors',
            },
            time: {
                type: 'string',
                default: 'today',
            },
            format: {
                type: 'string',
                default: 'i18n',
            },
            showLabel: {
                type: 'boolean',
                default: true,
            },
            showIcon: {
                type: 'boolean',
                default: true,
            },
            layout: {
                type: 'string',
                default: 'card',
            },
        },

        edit: function (props) {
            const { attributes, setAttributes } = props;
            const { stat, time, format, showLabel, showIcon, layout } = attributes;

            const blockProps = useBlockProps({
                className: `wps-statistics-block wps-statistics-block--${layout}`,
            });

            // Get current stat label
            const currentStat = statOptions.find((s) => s.value === stat);
            const currentTime = timeOptions.find((t) => t.value === time);
            const iconClass = iconMap[stat] || 'dashicons-chart-pie';

            return (
                <>
                    <InspectorControls>
                        <PanelBody title={__('Statistics Settings', 'wp-statistics')}>
                            <SelectControl
                                label={__('Statistic', 'wp-statistics')}
                                value={stat}
                                options={statOptions}
                                onChange={(value) => setAttributes({ stat: value })}
                            />
                            <SelectControl
                                label={__('Time Period', 'wp-statistics')}
                                value={time}
                                options={timeOptions}
                                onChange={(value) => setAttributes({ time: value })}
                            />
                            <SelectControl
                                label={__('Number Format', 'wp-statistics')}
                                value={format}
                                options={formatOptions}
                                onChange={(value) => setAttributes({ format: value })}
                            />
                        </PanelBody>
                        <PanelBody title={__('Display Settings', 'wp-statistics')}>
                            <SelectControl
                                label={__('Layout', 'wp-statistics')}
                                value={layout}
                                options={layoutOptions}
                                onChange={(value) => setAttributes({ layout: value })}
                            />
                            <ToggleControl
                                label={__('Show Label', 'wp-statistics')}
                                checked={showLabel}
                                onChange={(value) => setAttributes({ showLabel: value })}
                            />
                            <ToggleControl
                                label={__('Show Icon', 'wp-statistics')}
                                checked={showIcon}
                                onChange={(value) => setAttributes({ showIcon: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div {...blockProps}>
                        {layout === 'card' && (
                            <>
                                {showIcon && (
                                    <span className={`wps-statistics-block__icon dashicons ${iconClass}`}></span>
                                )}
                                <div className="wps-statistics-block__content">
                                    <span className="wps-statistics-block__value">---</span>
                                    {showLabel && (
                                        <>
                                            <span className="wps-statistics-block__label">
                                                {currentStat ? currentStat.label : stat}
                                            </span>
                                            <span className="wps-statistics-block__period">
                                                {currentTime ? currentTime.label : time}
                                            </span>
                                        </>
                                    )}
                                </div>
                            </>
                        )}

                        {layout === 'inline' && (
                            <>
                                {showIcon && (
                                    <span className={`wps-statistics-block__icon dashicons ${iconClass}`}></span>
                                )}
                                {showLabel && (
                                    <span className="wps-statistics-block__label">
                                        {currentStat ? currentStat.label : stat}:
                                    </span>
                                )}
                                <span className="wps-statistics-block__value">---</span>
                                {showLabel && (
                                    <span className="wps-statistics-block__period">
                                        ({currentTime ? currentTime.label : time})
                                    </span>
                                )}
                            </>
                        )}

                        {layout === 'minimal' && (
                            <>
                                <span className="wps-statistics-block__value">---</span>
                                {showLabel && (
                                    <span className="wps-statistics-block__label">
                                        {currentStat ? currentStat.label : stat}
                                    </span>
                                )}
                            </>
                        )}
                    </div>
                </>
            );
        },

        save: function () {
            // Server-side rendered
            return null;
        },
    });
})(window.wp);
