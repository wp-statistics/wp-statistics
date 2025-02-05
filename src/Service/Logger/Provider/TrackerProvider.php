<?php

namespace WP_Statistics\Service\Logger\Provider;

use WP_Statistics\Service\Logger\AbstractLoggerProvider;

/**
 * Tracker-based logger provider implementation.
 */
class TrackerProvider extends AbstractLoggerProvider
{
    /**
     * Formats and returns the last logged error message for display.
     * 
     * @return string The formatted error message or an empty string if no valid log exists.
     */
    public function print()
    {
        if (empty($this->errors)) {
            return '';
        }

        $lastLog = end($this->errors);

        if (!isset($lastLog['name'], $lastLog['message'], $lastLog['date'])) {
            return '';
        }

        $errorType = ucfirst($lastLog['name']);
        $timestamp = strtotime($lastLog['date']);

        $date = sprintf(
            __('%1$s at %2$s', 'wp-statistics'),
            date_i18n('F j, Y', $timestamp),
            date_i18n('H:i:s', $timestamp)
        );

        $message = trim(preg_replace('/\s+/', ' ', $lastLog['message']));

        return sprintf(
            '<p>%1$s %2$s</p>
            <p>%3$s %4$s</p>
            <p>%5$s %6$s</p>',
            esc_html__('Type:', 'wp-statistics'),
            esc_html($errorType),
            esc_html__('Message:', 'wp-statistics'),
            esc_html($message),
            esc_html__('Occurred At:', 'wp-statistics'),
            esc_html($date)
        );
    }
}
