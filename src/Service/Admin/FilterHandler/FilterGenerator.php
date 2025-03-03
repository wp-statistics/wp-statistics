<?php

namespace WP_Statistics\Service\Admin\FilterHandler;

class FilterGenerator
{
    /**
     * Holds all the filters generated.
     * 
     * @var array
     */
    protected $filters = [];

    /**
     * Create a new instance of the FilterGenerator class.
     *
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Adds a hidden input filter.
     *
     * @param string $name The name of the hidden field.
     * @param array $options Additional options for the field.
     * @return $this
     */
    public function hidden($name, $options = [])
    {
        $this->filters[$name] = [
            'type' => 'hidden',
            'name' => $options['name'] ?? $name,
            'attributes' => $options['attributes'] ?? [],
        ];

        return $this;
    }

    /**
     * Adds a select dropdown filter.
     *
     * @param string $name The name of the select field.
     * @param array $options Additional options including label, classes, placeholder, etc.
     * @return $this
     */
    public function select($name, $options = [])
    {
        $this->filters[$name] = array_merge(
            [
                'type' => 'select',
                'name' => $options['name'] ?? $name,
                'label' => $options['label'] ?? esc_html__(ucfirst($name), 'wp-statistics'),
                'classes' => $options['classes'] ?? 'filter-select select2 wps-width-100',
                'placeholder' => $options['placeholder'] ?? 'All',
                'options' => $options['options'] ?? [],
                'attributes' => $options['attributes'] ?? [
                    'data-type' => $name,
                    'data-type-show' => 'select2',
                ],
            ],
            $options
        );
        return $this;
    }

    /**
     * Adds an input field filter.
     *
     * @param string $type The type of input field (e.g., text, number, etc.).
     * @param string $name The name of the input field.
     * @param array $options Additional options including label, classes, placeholder, etc.
     * @return $this
     */
    public function input($type, $name, $options = [])
    {
        $this->filters[$name] = array_merge(
            [
                'type' => $options['type'] ?? $type,
                'name' => $options['name'] ?? $name,
                'label' => $options['label'] ?? esc_html__(ucfirst($name), 'wp-statistics'),
                'classes' => $options['classes'] ?? 'wps-width-100 filter-input',
                'placeholder' => $options['placeholder'] ?? '',
                'attributes' => $options['attributes'] ?? [
                    'autocomplete' => 'off',
                ],
            ],
            $options
        );
        return $this;
    }

    /**
     * Adds a button filter.
     *
     * @param string $name The name of the button.
     * @param array $options Additional options including classes, attributes, and label.
     * @return $this
     */
    public function button($name, $options = [])
    {
        $this->filters[$name] = array_merge(
            [
                'type' => 'button',
                'classes' => $options['classes'] ?? 'button',
                'attributes' => $options['attributes'] ?? ['type' => 'button'],
                'label' => $options['label'] ?? esc_html__($name, 'wp-statistics'),
            ],
            $options
        );
        return $this;
    }

    /**
     * Adds a dropdown filter.
     *
     * @param string $name The name of the dropdown field.
     * @param array $options Additional options including label, classes, options, etc.
     * @return $this
     */
    public function dropdown($name, $options = [])
    {
        $this->filters[$name] = array_merge(
            [
                'type' => 'dropdown',
                'name' => $options['name'] ?? $name,
                'label' => $options['label'] ?? '',
                'classes' => $options['classes'] ?? 'wps-dropdown',
                'options' => $options['options'] ?? [],
                'selected' => $options['selected'] ?? '',
                'attributes' => $options['attributes'] ?? [
                    'data-type' => $options['name'] ?? $name,
                    'data-type-show' => 'dropdown',
                ],
            ],
            $options
        );
        return $this;
    }

    /**
     * Retrieves all the filters that have been generated.
     *
     * @return array
     */
    public function get()
    {
        return $this->filters;
    }
}
