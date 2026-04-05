<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

class EventDataValueGroupBy extends AbstractGroupBy
{
    protected $name        = 'event_data_value';
    protected $column      = '';
    protected $alias       = 'event_data_value';
    protected $groupBy     = '';
    protected $requirement = 'events';
    protected $filter      = '';

    public function setParams(array $params): void
    {
        $jsonKey = $params['json_key'] ?? '';
        if (empty($jsonKey)) {
            return;
        }

        $jsonKey = preg_replace('/[^a-zA-Z0-9_]/', '', $jsonKey);
        $expr = "JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$." . $jsonKey . "'))";

        $this->column  = $expr;
        $this->alias   = $jsonKey;
        $this->groupBy = $expr;
        $this->filter  = "$expr IS NOT NULL AND $expr != ''";
    }
}
