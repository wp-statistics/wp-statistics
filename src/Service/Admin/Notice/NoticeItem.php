<?php

namespace WP_Statistics\Service\Admin\Notice;

/**
 * Notice Item DTO.
 *
 * Represents a single admin notice to be displayed.
 *
 * @since 15.0.0
 */
class NoticeItem
{
    /**
     * Unique identifier for this notice.
     *
     * @var string
     */
    public string $id;

    /**
     * Notice message (supports HTML).
     *
     * @var string
     */
    public string $message;

    /**
     * Notice type (info, warning, error, success).
     *
     * @var string
     */
    public string $type = 'info';

    /**
     * Whether the notice can be dismissed.
     *
     * @var bool
     */
    public bool $dismissible = true;

    /**
     * URL for the primary action button.
     *
     * @var string|null
     */
    public ?string $actionUrl = null;

    /**
     * Label for the primary action button.
     *
     * @var string|null
     */
    public ?string $actionLabel = null;

    /**
     * URL for help/documentation link.
     *
     * @var string|null
     */
    public ?string $helpUrl = null;

    /**
     * Priority for notice ordering (lower = higher priority).
     *
     * @var int
     */
    public int $priority = 10;

    /**
     * Pages/routes where this notice should appear.
     *
     * Empty array means show on all pages (global notice).
     * Route names like: 'geographic', 'devices', 'visitors-overview'
     *
     * @var array
     */
    public array $pages = [];

    /**
     * Create a new notice item.
     *
     * @param array $data Notice data.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Create an info notice.
     *
     * @param string      $id      Notice ID.
     * @param string      $message Notice message.
     * @param string|null $actionUrl Action URL.
     * @param string|null $actionLabel Action label.
     * @return self
     */
    public static function info(string $id, string $message, ?string $actionUrl = null, ?string $actionLabel = null): self
    {
        return new self([
            'id'          => $id,
            'message'     => $message,
            'type'        => 'info',
            'actionUrl'   => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
    }

    /**
     * Create a warning notice.
     *
     * @param string      $id      Notice ID.
     * @param string      $message Notice message.
     * @param string|null $actionUrl Action URL.
     * @param string|null $actionLabel Action label.
     * @return self
     */
    public static function warning(string $id, string $message, ?string $actionUrl = null, ?string $actionLabel = null): self
    {
        return new self([
            'id'          => $id,
            'message'     => $message,
            'type'        => 'warning',
            'actionUrl'   => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
    }

    /**
     * Create an error notice.
     *
     * @param string      $id      Notice ID.
     * @param string      $message Notice message.
     * @param string|null $actionUrl Action URL.
     * @param string|null $actionLabel Action label.
     * @return self
     */
    public static function error(string $id, string $message, ?string $actionUrl = null, ?string $actionLabel = null): self
    {
        return new self([
            'id'          => $id,
            'message'     => $message,
            'type'        => 'error',
            'actionUrl'   => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
    }

    /**
     * Create a success notice.
     *
     * @param string      $id      Notice ID.
     * @param string      $message Notice message.
     * @param string|null $actionUrl Action URL.
     * @param string|null $actionLabel Action label.
     * @return self
     */
    public static function success(string $id, string $message, ?string $actionUrl = null, ?string $actionLabel = null): self
    {
        return new self([
            'id'          => $id,
            'message'     => $message,
            'type'        => 'success',
            'actionUrl'   => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'message'     => $this->message,
            'type'        => $this->type,
            'dismissible' => $this->dismissible,
            'actionUrl'   => $this->actionUrl,
            'actionLabel' => $this->actionLabel,
            'helpUrl'     => $this->helpUrl,
            'priority'    => $this->priority,
            'pages'       => $this->pages,
        ];
    }
}
