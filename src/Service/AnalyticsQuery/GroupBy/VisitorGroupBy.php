<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Visitor group by - groups by visitor.
 *
 * @since 15.0.0
 */
class VisitorGroupBy extends AbstractGroupBy
{
    protected $name         = 'visitor';
    protected $column       = 'visitors.ID';
    protected $alias        = 'visitor_id';
    protected $extraColumns = [
        'visitors.hash AS visitor_hash',
        'sessions.user_id',
        'sessions.ip AS ip_address',
    ];
    protected $joins        = [
        'table' => 'visitors',
        'alias' => 'visitors',
        'on'    => 'sessions.visitor_id = visitors.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'visitors.ID';
}
