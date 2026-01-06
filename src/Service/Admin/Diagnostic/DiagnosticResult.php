<?php

namespace WP_Statistics\Service\Admin\Diagnostic;

/**
 * Diagnostic Result DTO.
 *
 * Represents the result of a diagnostic check.
 *
 * @since 15.0.0
 */
class DiagnosticResult
{
    /**
     * Check passed successfully.
     */
    public const STATUS_PASS = 'pass';

    /**
     * Check passed with warnings.
     */
    public const STATUS_WARNING = 'warning';

    /**
     * Check failed.
     */
    public const STATUS_FAIL = 'fail';

    /**
     * Unique key for this check.
     *
     * @var string
     */
    public string $key;

    /**
     * Human-readable label.
     *
     * @var string
     */
    public string $label;

    /**
     * Status of the check (pass, warning, fail).
     *
     * @var string
     */
    public string $status;

    /**
     * Human-readable message describing the result.
     *
     * @var string
     */
    public string $message;

    /**
     * Additional details about the check result.
     *
     * @var array
     */
    public array $details = [];

    /**
     * Help URL for when the check fails.
     *
     * @var string|null
     */
    public ?string $helpUrl = null;

    /**
     * Timestamp when this check was run.
     *
     * @var int
     */
    public int $timestamp;

    /**
     * Create a new diagnostic result.
     *
     * @param array $data Optional data to initialize the result.
     */
    public function __construct(array $data = [])
    {
        $this->timestamp = time();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Create a passing result.
     *
     * @param string      $key     Check key.
     * @param string      $label   Check label.
     * @param string      $message Success message.
     * @param array       $details Additional details.
     * @param string|null $helpUrl Help URL.
     * @return self
     */
    public static function pass(string $key, string $label, string $message, array $details = [], ?string $helpUrl = null): self
    {
        return new self([
            'key'     => $key,
            'label'   => $label,
            'status'  => self::STATUS_PASS,
            'message' => $message,
            'details' => $details,
            'helpUrl' => $helpUrl,
        ]);
    }

    /**
     * Create a warning result.
     *
     * @param string      $key     Check key.
     * @param string      $label   Check label.
     * @param string      $message Warning message.
     * @param array       $details Additional details.
     * @param string|null $helpUrl Help URL.
     * @return self
     */
    public static function warning(string $key, string $label, string $message, array $details = [], ?string $helpUrl = null): self
    {
        return new self([
            'key'     => $key,
            'label'   => $label,
            'status'  => self::STATUS_WARNING,
            'message' => $message,
            'details' => $details,
            'helpUrl' => $helpUrl,
        ]);
    }

    /**
     * Create a failing result.
     *
     * @param string      $key     Check key.
     * @param string      $label   Check label.
     * @param string      $message Failure message.
     * @param array       $details Additional details.
     * @param string|null $helpUrl Help URL.
     * @return self
     */
    public static function fail(string $key, string $label, string $message, array $details = [], ?string $helpUrl = null): self
    {
        return new self([
            'key'     => $key,
            'label'   => $label,
            'status'  => self::STATUS_FAIL,
            'message' => $message,
            'details' => $details,
            'helpUrl' => $helpUrl,
        ]);
    }

    /**
     * Check if the result is a pass.
     *
     * @return bool
     */
    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASS;
    }

    /**
     * Check if the result is a warning.
     *
     * @return bool
     */
    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * Check if the result is a failure.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'key'       => $this->key,
            'label'     => $this->label,
            'status'    => $this->status,
            'message'   => $this->message,
            'details'   => $this->details,
            'helpUrl'   => $this->helpUrl,
            'timestamp' => $this->timestamp,
        ];
    }
}
