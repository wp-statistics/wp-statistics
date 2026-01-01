<?php

namespace WP_Statistics\Testing\Simulator\Generators;

/**
 * InvalidDataGenerator - Generates invalid/edge case data for testing system reliability
 *
 * Categories of invalid data:
 * - Boundary values (min/max integers, empty strings)
 * - Malformed data (invalid base64, corrupted encoding)
 * - Missing required fields
 * - Type mismatches (string where int expected)
 * - Encoding issues (unicode edge cases, control characters)
 *
 * @package WP_Statistics\Testing\Simulator\Generators
 * @since 15.0.0
 */
class InvalidDataGenerator extends AbstractDataGenerator
{
    /**
     * Categories of invalid data
     */
    public const CATEGORY_BOUNDARY = 'boundary';
    public const CATEGORY_MALFORMED = 'malformed';
    public const CATEGORY_MISSING = 'missing';
    public const CATEGORY_OVERFLOW = 'overflow';
    public const CATEGORY_ENCODING = 'encoding';
    public const CATEGORY_TYPE = 'type';

    /**
     * Invalid data patterns loaded from JSON
     */
    private array $invalidPatterns = [];

    /**
     * All categories
     */
    private array $categories = [
        self::CATEGORY_BOUNDARY,
        self::CATEGORY_MALFORMED,
        self::CATEGORY_MISSING,
        self::CATEGORY_OVERFLOW,
        self::CATEGORY_ENCODING,
        self::CATEGORY_TYPE,
    ];

    /**
     * Constructor
     *
     * @param string $dataDir Path to data directory
     */
    public function __construct(string $dataDir)
    {
        parent::__construct($dataDir);
        $this->loadInvalidPatterns();
    }

    /**
     * Load invalid data patterns from JSON
     */
    private function loadInvalidPatterns(): void
    {
        try {
            $this->invalidPatterns = $this->loadDataFile('invalid-data.json');
        } catch (\RuntimeException $e) {
            $this->invalidPatterns = $this->getDefaultInvalidPatterns();
        }
    }

    /**
     * Generate random invalid data
     *
     * @return array Invalid request data with metadata
     */
    public function generate(): array
    {
        $category = $this->randomFrom($this->categories);
        return $this->generateForCategory($category);
    }

    /**
     * Generate invalid data for a specific category
     *
     * @param string $category Category name
     * @return array Invalid request data with metadata
     */
    public function generateForCategory(string $category): array
    {
        switch ($category) {
            case self::CATEGORY_BOUNDARY:
                return $this->generateBoundaryCase();
            case self::CATEGORY_MALFORMED:
                return $this->generateMalformedData();
            case self::CATEGORY_MISSING:
                return $this->generateMissingFields();
            case self::CATEGORY_OVERFLOW:
                return $this->generateOverflowData();
            case self::CATEGORY_ENCODING:
                return $this->generateEncodingIssues();
            case self::CATEGORY_TYPE:
                return $this->generateTypeErrors();
            default:
                return $this->generateBoundaryCase();
        }
    }

    /**
     * Generate boundary value test case
     *
     * @return array
     */
    public function generateBoundaryCase(): array
    {
        $field = $this->randomFrom(['resourceUriId', 'resource_id', 'screenWidth', 'screenHeight']);
        $boundaries = $this->invalidPatterns['boundary_values'][$field] ?? [-1, 0, PHP_INT_MAX];
        $value = $this->randomFrom($boundaries);

        return [
            'category'     => self::CATEGORY_BOUNDARY,
            'field'        => $field,
            'value'        => $value,
            'description'  => "Boundary value test: {$field} = {$value}",
            'expected'     => 'rejection',
            'request_data' => $this->buildInvalidRequest($field, $value),
        ];
    }

    /**
     * Generate malformed data test case
     *
     * @return array
     */
    public function generateMalformedData(): array
    {
        $field = $this->randomFrom(['resourceUri', 'referred', 'timezone', 'language']);
        $malformedData = $this->invalidPatterns['malformed_strings'][$field]
            ?? $this->getDefaultMalformedStrings();
        $value = $this->randomFrom($malformedData);

        $requestValue = $value;
        if (in_array($field, ['resourceUri', 'referred', 'page_uri'])) {
            // These fields expect base64, so we might corrupt the base64
            if ($this->randomBool(0.5)) {
                $requestValue = base64_encode($value); // Valid base64 of malformed content
            } else {
                $requestValue = $value . '!!!invalid-base64!!!'; // Invalid base64
            }
        }

        return [
            'category'     => self::CATEGORY_MALFORMED,
            'field'        => $field,
            'value'        => $value,
            'description'  => "Malformed data test: {$field}",
            'expected'     => 'rejection',
            'request_data' => $this->buildInvalidRequest($field, $requestValue),
        ];
    }

    /**
     * Generate missing required fields test case
     *
     * @return array
     */
    public function generateMissingFields(): array
    {
        $combinations = $this->invalidPatterns['missing_field_combinations'] ?? [
            ['resourceUriId'],
            ['resource_id'],
            ['resourceUri'],
            ['timezone', 'language'],
            ['screenWidth', 'screenHeight'],
        ];

        $fieldsToOmit = $this->randomFrom($combinations);

        return [
            'category'      => self::CATEGORY_MISSING,
            'fields'        => $fieldsToOmit,
            'description'   => "Missing fields test: " . implode(', ', $fieldsToOmit),
            'expected'      => 'rejection',
            'request_data'  => $this->buildRequestWithMissingFields($fieldsToOmit),
        ];
    }

    /**
     * Generate overflow data test case
     *
     * @return array
     */
    public function generateOverflowData(): array
    {
        $oversizedData = $this->invalidPatterns['oversized_values'] ?? [];
        $field = $this->randomFrom(array_keys($oversizedData) ?: ['timezone']);

        $config = $oversizedData[$field] ?? ['length' => 10000, 'pattern' => 'A'];
        $value = str_repeat($config['pattern'], $config['length']);

        return [
            'category'     => self::CATEGORY_OVERFLOW,
            'field'        => $field,
            'length'       => strlen($value),
            'description'  => "Overflow test: {$field} with " . strlen($value) . " chars",
            'expected'     => 'rejection',
            'request_data' => $this->buildInvalidRequest($field, $value),
        ];
    }

    /**
     * Generate encoding issues test case
     *
     * @return array
     */
    public function generateEncodingIssues(): array
    {
        $encodingIssues = $this->invalidPatterns['encoding_issues'] ?? [];
        $issueType = $this->randomFrom(array_keys($encodingIssues) ?: ['unicode_edge_cases']);
        $values = $encodingIssues[$issueType] ?? ["\x00", "\xFFFD"];
        $value = $this->randomFrom($values);

        $field = $this->randomFrom(['timezone', 'language', 'languageFullName']);

        return [
            'category'     => self::CATEGORY_ENCODING,
            'field'        => $field,
            'issue_type'   => $issueType,
            'value'        => bin2hex($value),
            'description'  => "Encoding issue test: {$issueType} in {$field}",
            'expected'     => 'rejection',
            'request_data' => $this->buildInvalidRequest($field, $value),
        ];
    }

    /**
     * Generate type error test case
     *
     * @return array
     */
    public function generateTypeErrors(): array
    {
        $typeErrors = [
            'resourceUriId' => ['string_value', 'abc', '12.5', [1, 2, 3], true, null],
            'resource_id'   => ['not_a_number', '12abc', '[]', false],
            'screenWidth'   => ['wide', '100%', 'auto', '1920px'],
            'screenHeight'  => ['tall', '100vh', 'fit-content'],
        ];

        $field = $this->randomFrom(array_keys($typeErrors));
        $value = $this->randomFrom($typeErrors[$field]);

        return [
            'category'     => self::CATEGORY_TYPE,
            'field'        => $field,
            'value'        => is_array($value) ? json_encode($value) : $value,
            'description'  => "Type error test: {$field} with wrong type",
            'expected'     => 'rejection',
            'request_data' => $this->buildInvalidRequest($field, $value),
        ];
    }

    /**
     * Get all invalid data cases for comprehensive testing
     *
     * @return \Generator
     */
    public function getAllCases(): \Generator
    {
        foreach ($this->categories as $category) {
            for ($i = 0; $i < 10; $i++) {
                yield $this->generateForCategory($category);
            }
        }
    }

    /**
     * Build invalid request with a specific field modified
     *
     * @param string $field Field to modify
     * @param mixed $value Invalid value
     * @return array Request data
     */
    private function buildInvalidRequest(string $field, $value): array
    {
        $validBase = $this->getValidBaseRequest();
        $validBase[$field] = $value;
        return $validBase;
    }

    /**
     * Build request with missing fields
     *
     * @param array $fieldsToOmit Fields to remove
     * @return array Request data
     */
    private function buildRequestWithMissingFields(array $fieldsToOmit): array
    {
        $validBase = $this->getValidBaseRequest();
        foreach ($fieldsToOmit as $field) {
            unset($validBase[$field]);
        }
        return $validBase;
    }

    /**
     * Get a valid base request that can be modified
     *
     * @return array
     */
    private function getValidBaseRequest(): array
    {
        return [
            'action'           => 'wp_statistics_hit_record',
            'resourceUriId'    => 1,
            'resourceUri'      => base64_encode('/sample-page/'),
            'resource_type'    => 'page',
            'resource_id'      => 1,
            'signature'        => md5('test-signature'),
            'timezone'         => 'America/New_York',
            'language'         => 'en-US',
            'languageFullName' => 'English',
            'screenWidth'      => 1920,
            'screenHeight'     => 1080,
            'referred'         => base64_encode(''),
            'page_uri'         => base64_encode('/sample-page/'),
        ];
    }

    /**
     * Get default malformed strings
     *
     * @return array
     */
    private function getDefaultMalformedStrings(): array
    {
        return [
            '',
            ' ',
            "\t",
            "\n",
            "\r\n",
            "\0",
            '../../../../etc/passwd',
            '../../../wp-config.php',
            'javascript:alert(1)',
            'http://',
            '//evil.com',
        ];
    }

    /**
     * Get default invalid patterns
     *
     * @return array
     */
    private function getDefaultInvalidPatterns(): array
    {
        return [
            'boundary_values' => [
                'resourceUriId' => [-1, 0, PHP_INT_MAX, 9223372036854775807],
                'resource_id'   => [-1, 0, PHP_INT_MAX],
                'screenWidth'   => [-1, 0, 99999, -100],
                'screenHeight'  => [-1, 0, 99999, -100],
            ],
            'malformed_strings' => [
                'timezone'    => ['', 'Invalid/Timezone', '../../../../etc/passwd'],
                'language'    => ['', 'xx-XX', 'en-US-invalid-extra'],
                'resourceUri' => ['', '//invalid', 'javascript:alert(1)'],
            ],
            'oversized_values' => [
                'timezone'    => ['length' => 1000, 'pattern' => 'A'],
                'language'    => ['length' => 500, 'pattern' => 'x'],
                'referrer'    => ['length' => 10000, 'pattern' => 'http://x.com/'],
            ],
            'encoding_issues' => [
                'unicode_edge_cases'  => ["\u{0000}", "\u{FFFD}", "\u{202E}"],
                'control_characters' => ["\x00", "\x0A", "\x0D", "\x1B"],
            ],
            'missing_field_combinations' => [
                ['resourceUriId'],
                ['resource_id'],
                ['resourceUri'],
                ['timezone', 'language'],
            ],
        ];
    }
}
