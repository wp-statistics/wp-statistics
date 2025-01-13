<?php
namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts;

abstract class RecommendedAudit extends BaseAudit
{
    /**
     * Returns the content of audit in passed state.
     *
     * @return array
     */
    abstract public static function getPassedStateInfo();

    /**
     * Returns the content of audit in when it is not in passed state.
     *
     * @return array
     */
    abstract public static function getUnpassedStateInfo();

    /**
     * Returns the content of audit in when it is in recommended state.
     *
     * @return array
     */
    abstract public static function getRecommendedStateInfo();

    /**
     * Returns an array of all states an audit item could have with details.
     *
     * @return array
     */
    public static function getStates()
    {
        $passedInfo         = static::getPassedStateInfo();
        $unpassedInfo       = static::getUnpassedStateInfo();
        $recommendedInfo    = static::getRecommendedStateInfo();

        return [
            'action_required' => [
                'status'     => 'warning',
                'icon'       => $unpassedInfo['icon'],
                'title'      => $unpassedInfo['title'],
                'notes'      => $unpassedInfo['notes'],
                'compliance' => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ],
            'recommended'     => [
                'status'     => 'recommended',
                'icon'       => $recommendedInfo['icon'],
                'title'      => $recommendedInfo['title'],
                'notes'      => $recommendedInfo['notes'],
                'compliance' => [
                    'key'   => 'recommended',
                    'value' => esc_html__('Recommended', 'wp-statistics'),
                ],
            ],
            'passed' => [
                'status'     => 'success',
                'icon'       => $passedInfo['icon'],
                'title'      => $passedInfo['title'],
                'notes'      => $passedInfo['notes'],
                'compliance' => [
                    'key'   => 'passed',
                    'value' => esc_html__('Passed', 'wp-statistics'),
                ]
            ],
        ];
    }
}