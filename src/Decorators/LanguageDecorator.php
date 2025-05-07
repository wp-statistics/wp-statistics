<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'languages' table.
 *
 * Provides accessors for each column in the 'languages' schema.
 */
class LanguageDecorator
{
    /**
     * The language record.
     *
     * @var object|null
     */
    private $language;

    /**
     * LanguageDecorator constructor.
     *
     * @param object|null $language A stdClass representing a 'languages' row, or null.
     */
    public function __construct($language)
    {
        $this->language = $language;
    }

    /**
     * Get the language ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->language->ID) ? null : (int)$this->language->ID;
    }

    /**
     * Get the language code.
     *
     * @return string
     */
    public function getCode()
    {
        return empty($this->language->code) ? '' : $this->language->code;
    }

    /**
     * Get the language name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->language->name) ? '' : $this->language->name;
    }

    /**
     * Get the region.
     *
     * @return string
     */
    public function getRegion()
    {
        return empty($this->language->region) ? '' : $this->language->region;
    }
}
