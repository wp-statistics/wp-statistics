<?php

namespace WP_Statistics\Service\Consent;

class ConsentStatus
{
    public const FULL      = 'full';
    public const ANONYMOUS = 'anonymous';
    public const NONE      = 'none';

    private static $valid = [self::FULL, self::ANONYMOUS, self::NONE];

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function full(): self
    {
        return new self(self::FULL);
    }

    public static function anonymous(): self
    {
        return new self(self::ANONYMOUS);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::$valid, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid consent status "%s". Valid values: %s', $value, implode(', ', self::$valid))
            );
        }

        return new self($value);
    }

    public function shouldTrack(): bool
    {
        return $this->value !== self::NONE;
    }

    public function shouldAnonymize(): bool
    {
        return $this->value === self::ANONYMOUS;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
