<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

class PrivacyCheckResult
{
    public const STATUS_PASS = 'pass';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAIL = 'fail';

    public string $key;
    public string $label;
    public string $description;
    public string $status;
    public string $message;
    public string $category;
    public string $settingsLink;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function pass(string $key, string $label, string $description, string $message, string $category, string $settingsLink): self
    {
        return new self([
            'key'          => $key,
            'label'        => $label,
            'description'  => $description,
            'status'       => self::STATUS_PASS,
            'message'      => $message,
            'category'     => $category,
            'settingsLink' => $settingsLink,
        ]);
    }

    public static function warning(string $key, string $label, string $description, string $message, string $category, string $settingsLink): self
    {
        return new self([
            'key'          => $key,
            'label'        => $label,
            'description'  => $description,
            'status'       => self::STATUS_WARNING,
            'message'      => $message,
            'category'     => $category,
            'settingsLink' => $settingsLink,
        ]);
    }

    public static function fail(string $key, string $label, string $description, string $message, string $category, string $settingsLink): self
    {
        return new self([
            'key'          => $key,
            'label'        => $label,
            'description'  => $description,
            'status'       => self::STATUS_FAIL,
            'message'      => $message,
            'category'     => $category,
            'settingsLink' => $settingsLink,
        ]);
    }

    public function toArray(): array
    {
        return [
            'key'          => $this->key,
            'label'        => $this->label,
            'description'  => $this->description,
            'status'       => $this->status,
            'message'      => $this->message,
            'category'     => $this->category,
            'settingsLink' => $this->settingsLink,
        ];
    }
}
