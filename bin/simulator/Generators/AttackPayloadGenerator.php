<?php

namespace WP_Statistics\Testing\Simulator\Generators;

/**
 * AttackPayloadGenerator - Generates security attack payloads for testing threat detection
 *
 * Based on OWASP Top 10 vulnerability categories:
 * - SQL Injection (A03:2021)
 * - XSS Cross-Site Scripting (A03:2021)
 * - Path Traversal (A01:2021)
 * - Command Injection (A03:2021)
 * - XXE XML External Entity (A05:2021)
 * - SSRF Server-Side Request Forgery (A10:2021)
 * - Insecure Deserialization (A08:2021)
 *
 * These payloads are for authorized security testing only.
 *
 * @package WP_Statistics\Testing\Simulator\Generators
 * @since 15.0.0
 */
class AttackPayloadGenerator extends AbstractDataGenerator
{
    /**
     * Attack categories
     */
    public const CATEGORY_SQL_INJECTION = 'sql_injection';
    public const CATEGORY_XSS = 'xss';
    public const CATEGORY_ENCODING_BYPASS = 'encoding_bypass';
    public const CATEGORY_HEADER_INJECTION = 'header_injection';
    public const CATEGORY_PATH_TRAVERSAL = 'path_traversal';
    public const CATEGORY_COMMAND_INJECTION = 'command_injection';
    public const CATEGORY_XXE = 'xxe';
    public const CATEGORY_SSRF = 'ssrf';
    public const CATEGORY_LDAP_INJECTION = 'ldap_injection';
    public const CATEGORY_NOSQL_INJECTION = 'nosql_injection';
    public const CATEGORY_LOG_INJECTION = 'log_injection';
    public const CATEGORY_DESERIALIZATION = 'deserialization';
    public const CATEGORY_PROTOTYPE_POLLUTION = 'prototype_pollution';

    /**
     * Severity levels
     */
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_LOW = 'low';

    /**
     * Attack payloads loaded from JSON
     */
    private array $attackPayloads = [];

    /**
     * All available categories
     */
    private array $categories = [];

    /**
     * Target fields configuration
     */
    private array $targetFields = [];

    /**
     * Constructor
     *
     * @param string $dataDir Path to data directory
     */
    public function __construct(string $dataDir)
    {
        parent::__construct($dataDir);
        $this->loadAttackPayloads();
        $this->initializeCategories();
    }

    /**
     * Load attack payloads from JSON
     */
    private function loadAttackPayloads(): void
    {
        try {
            $this->attackPayloads = $this->loadDataFile('attack-payloads.json');
            $this->targetFields = $this->attackPayloads['target_fields'] ?? $this->getDefaultTargetFields();
        } catch (\RuntimeException $e) {
            $this->attackPayloads = $this->getDefaultAttackPayloads();
            $this->targetFields = $this->getDefaultTargetFields();
        }
    }

    /**
     * Initialize available categories from loaded payloads
     */
    private function initializeCategories(): void
    {
        $this->categories = [
            self::CATEGORY_SQL_INJECTION,
            self::CATEGORY_XSS,
            self::CATEGORY_ENCODING_BYPASS,
            self::CATEGORY_HEADER_INJECTION,
            self::CATEGORY_PATH_TRAVERSAL,
            self::CATEGORY_COMMAND_INJECTION,
            self::CATEGORY_XXE,
            self::CATEGORY_SSRF,
            self::CATEGORY_LDAP_INJECTION,
            self::CATEGORY_NOSQL_INJECTION,
            self::CATEGORY_LOG_INJECTION,
            self::CATEGORY_DESERIALIZATION,
            self::CATEGORY_PROTOTYPE_POLLUTION,
        ];
    }

    /**
     * Generate random attack payload
     *
     * @return array Attack request data with metadata
     */
    public function generate(): array
    {
        $category = $this->randomFrom($this->categories);
        return $this->generateForCategory($category);
    }

    /**
     * Generate attack payload for a specific category
     *
     * @param string $category Category name
     * @return array Attack request data with metadata
     */
    public function generateForCategory(string $category): array
    {
        $payloads = $this->getPayloadsForCategory($category);
        if (empty($payloads)) {
            return $this->generateForCategory(self::CATEGORY_SQL_INJECTION);
        }

        $subcategory = $this->randomFrom(array_keys($payloads));
        $payload = $this->randomFrom($payloads[$subcategory]);
        $targetField = $this->selectTargetField($category);

        return [
            'category'     => $category,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => $this->getSeverity($category),
            'description'  => "Security test: {$category}/{$subcategory} targeting {$targetField}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, $category),
        ];
    }

    /**
     * Generate attack by severity level
     *
     * @param string $severity Severity level (critical, high, medium, low)
     * @return array Attack request data with metadata
     */
    public function generateBySeverity(string $severity): array
    {
        $severityMap = $this->attackPayloads['severity_levels'] ?? $this->getDefaultSeverityLevels();

        if (!isset($severityMap[$severity])) {
            $severity = self::SEVERITY_HIGH;
        }

        $category = $this->randomFrom($severityMap[$severity]);
        return $this->generateForCategory($category);
    }

    /**
     * Generate SQL injection attack
     *
     * @param string|null $type Specific injection type (classic_union, blind_boolean, etc.)
     * @return array
     */
    public function generateSqlInjection(?string $type = null): array
    {
        $sqlPayloads = $this->attackPayloads[self::CATEGORY_SQL_INJECTION] ?? [];
        // Filter to only include subcategories with array payloads (exclude 'description' etc.)
        $payloadKeys = array_filter(array_keys($sqlPayloads), fn($k) => is_array($sqlPayloads[$k]));

        if ($type && isset($sqlPayloads[$type])) {
            $payload = $this->randomFrom($sqlPayloads[$type]);
            $subcategory = $type;
        } else {
            $subcategory = $this->randomFrom($payloadKeys ?: ['classic_union']);
            $payload = $this->randomFrom($sqlPayloads[$subcategory] ?? ["' OR 1=1--"]);
        }

        $targetField = $this->randomFrom($this->targetFields['plain_fields']);

        return [
            'category'     => self::CATEGORY_SQL_INJECTION,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => self::SEVERITY_CRITICAL,
            'description'  => "SQL Injection: {$subcategory}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, self::CATEGORY_SQL_INJECTION),
        ];
    }

    /**
     * Generate XSS attack
     *
     * @param string|null $type Specific XSS type (script_tags, event_handlers, etc.)
     * @return array
     */
    public function generateXss(?string $type = null): array
    {
        $xssPayloads = $this->attackPayloads[self::CATEGORY_XSS] ?? [];
        // Filter to only include subcategories with array payloads (exclude 'description' etc.)
        $payloadKeys = array_filter(array_keys($xssPayloads), fn($k) => is_array($xssPayloads[$k]));

        if ($type && isset($xssPayloads[$type])) {
            $payload = $this->randomFrom($xssPayloads[$type]);
            $subcategory = $type;
        } else {
            $subcategory = $this->randomFrom($payloadKeys ?: ['script_tags']);
            $payload = $this->randomFrom($xssPayloads[$subcategory] ?? ['<script>alert(1)</script>']);
        }

        // XSS can target any field type
        $fieldType = $this->randomFrom(['base64_fields', 'plain_fields']);
        $targetField = $this->randomFrom($this->targetFields[$fieldType]);

        return [
            'category'     => self::CATEGORY_XSS,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => self::SEVERITY_HIGH,
            'description'  => "XSS Attack: {$subcategory}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, self::CATEGORY_XSS),
        ];
    }

    /**
     * Generate path traversal attack
     *
     * @return array
     */
    public function generatePathTraversal(): array
    {
        $pathPayloads = $this->attackPayloads[self::CATEGORY_PATH_TRAVERSAL] ?? [];
        // Filter to only include subcategories with array payloads (exclude 'description' etc.)
        $payloadKeys = array_filter(array_keys($pathPayloads), fn($k) => is_array($pathPayloads[$k]));
        $subcategory = $this->randomFrom($payloadKeys ?: ['basic_traversal']);
        $payload = $this->randomFrom($pathPayloads[$subcategory] ?? ['../../../etc/passwd']);

        // Path traversal typically targets URI fields
        $targetField = $this->randomFrom($this->targetFields['base64_fields']);

        return [
            'category'     => self::CATEGORY_PATH_TRAVERSAL,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => self::SEVERITY_HIGH,
            'description'  => "Path Traversal: {$subcategory}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, self::CATEGORY_PATH_TRAVERSAL),
        ];
    }

    /**
     * Generate header injection attack
     *
     * @return array
     */
    public function generateHeaderInjection(): array
    {
        $headerPayloads = $this->attackPayloads[self::CATEGORY_HEADER_INJECTION] ?? [];
        // Filter to only include subcategories with array payloads (exclude 'description' etc.)
        $payloadKeys = array_filter(array_keys($headerPayloads), fn($k) => is_array($headerPayloads[$k]));
        $subcategory = $this->randomFrom($payloadKeys ?: ['crlf_injection']);
        $payload = $this->randomFrom($headerPayloads[$subcategory] ?? ["value\r\nX-Injected: header"]);

        $targetField = $this->randomFrom($this->targetFields['plain_fields']);

        return [
            'category'     => self::CATEGORY_HEADER_INJECTION,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => self::SEVERITY_MEDIUM,
            'description'  => "Header Injection: {$subcategory}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, self::CATEGORY_HEADER_INJECTION),
        ];
    }

    /**
     * Generate encoding bypass attack
     *
     * @return array
     */
    public function generateEncodingBypass(): array
    {
        $encodingPayloads = $this->attackPayloads[self::CATEGORY_ENCODING_BYPASS] ?? [];
        // Filter to only include subcategories with array payloads (exclude 'description' etc.)
        $payloadKeys = array_filter(array_keys($encodingPayloads), fn($k) => is_array($encodingPayloads[$k]));
        $subcategory = $this->randomFrom($payloadKeys ?: ['double_url_encoding']);
        $payload = $this->randomFrom($encodingPayloads[$subcategory] ?? ['%253Cscript%253E']);

        $fieldType = $this->randomFrom(['base64_fields', 'plain_fields']);
        $targetField = $this->randomFrom($this->targetFields[$fieldType]);

        return [
            'category'     => self::CATEGORY_ENCODING_BYPASS,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => self::SEVERITY_MEDIUM,
            'description'  => "Encoding Bypass: {$subcategory}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, self::CATEGORY_ENCODING_BYPASS),
        ];
    }

    /**
     * Run a predefined test scenario
     *
     * @param string $scenario Scenario name from attack-payloads.json
     * @return \Generator
     */
    public function runScenario(string $scenario): \Generator
    {
        $scenarios = $this->attackPayloads['test_scenarios'] ?? [];

        if (!isset($scenarios[$scenario])) {
            $scenario = 'basic_security_scan';
        }

        $config = $scenarios[$scenario];
        $categories = $config['categories'] ?? ['sql_injection'];
        $countPerCategory = $config['count_per_category'] ?? 3;

        if ($categories === ['all']) {
            $categories = $this->categories;
        }

        foreach ($categories as $category) {
            // Handle subcategory notation (e.g., "sql_injection.classic_union")
            $parts = explode('.', $category);
            $mainCategory = $parts[0];

            for ($i = 0; $i < $countPerCategory; $i++) {
                if (count($parts) > 1) {
                    // Specific subcategory
                    yield $this->generateForSubcategory($mainCategory, $parts[1]);
                } else {
                    yield $this->generateForCategory($mainCategory);
                }
            }
        }
    }

    /**
     * Generate attack for specific subcategory
     *
     * @param string $category Main category
     * @param string $subcategory Subcategory name
     * @return array
     */
    public function generateForSubcategory(string $category, string $subcategory): array
    {
        $payloads = $this->getPayloadsForCategory($category);

        if (!isset($payloads[$subcategory])) {
            return $this->generateForCategory($category);
        }

        $payload = $this->randomFrom($payloads[$subcategory]);
        $targetField = $this->selectTargetField($category);

        return [
            'category'     => $category,
            'subcategory'  => $subcategory,
            'payload'      => $payload,
            'field'        => $targetField,
            'severity'     => $this->getSeverity($category),
            'description'  => "Security test: {$category}/{$subcategory} targeting {$targetField}",
            'expected'     => 'rejection',
            'request_data' => $this->buildAttackRequest($targetField, $payload, $category),
        ];
    }

    /**
     * Get all attack cases for comprehensive testing
     *
     * @return \Generator
     */
    public function getAllCases(): \Generator
    {
        foreach ($this->categories as $category) {
            $payloads = $this->getPayloadsForCategory($category);

            foreach ($payloads as $subcategory => $items) {
                // Yield one test per subcategory
                yield $this->generateForSubcategory($category, $subcategory);
            }
        }
    }

    /**
     * Get payloads for a category
     *
     * @param string $category Category name
     * @return array
     */
    private function getPayloadsForCategory(string $category): array
    {
        $categoryPayloads = $this->attackPayloads[$category] ?? [];

        // Filter out non-payload keys like 'description'
        return array_filter($categoryPayloads, function ($value, $key) {
            return is_array($value) && $key !== 'description';
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Select appropriate target field based on attack category
     *
     * @param string $category Attack category
     * @return string Field name
     */
    private function selectTargetField(string $category): string
    {
        switch ($category) {
            case self::CATEGORY_PATH_TRAVERSAL:
            case self::CATEGORY_SSRF:
                return $this->randomFrom($this->targetFields['base64_fields']);

            case self::CATEGORY_HEADER_INJECTION:
                return $this->randomFrom($this->targetFields['header_fields'] ?? $this->targetFields['plain_fields']);

            case self::CATEGORY_SQL_INJECTION:
            case self::CATEGORY_LDAP_INJECTION:
            case self::CATEGORY_NOSQL_INJECTION:
                // Can target numeric or plain fields
                if ($this->randomBool(0.3)) {
                    return $this->randomFrom($this->targetFields['numeric_fields']);
                }
                return $this->randomFrom($this->targetFields['plain_fields']);

            default:
                // XSS and others can target any field type
                $fieldType = $this->randomFrom(['base64_fields', 'plain_fields']);
                return $this->randomFrom($this->targetFields[$fieldType]);
        }
    }

    /**
     * Build attack request with payload injected
     *
     * @param string $field Target field
     * @param string $payload Attack payload
     * @param string $category Attack category
     * @return array Request data
     */
    private function buildAttackRequest(string $field, string $payload, string $category): array
    {
        $validBase = $this->getValidBaseRequest();

        // Handle different field types
        if (in_array($field, $this->targetFields['base64_fields'])) {
            // Base64 encode the payload for these fields
            $validBase[$field] = base64_encode($payload);
        } elseif (in_array($field, $this->targetFields['numeric_fields'])) {
            // For numeric fields, inject as string (type mismatch attack)
            $validBase[$field] = $payload;
        } elseif (in_array($field, $this->targetFields['header_fields'] ?? [])) {
            // Header fields are handled separately in HTTP layer
            // Store payload info for HTTP sender
            $validBase['_attack_headers'] = [$field => $payload];
        } else {
            // Plain fields - inject directly
            $validBase[$field] = $payload;
        }

        // Add attack metadata for logging/analysis
        $validBase['_attack_meta'] = [
            'category' => $category,
            'field'    => $field,
            'payload'  => $payload,
        ];

        return $validBase;
    }

    /**
     * Get a valid base request
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
     * Get severity level for a category
     *
     * @param string $category Category name
     * @return string Severity level
     */
    private function getSeverity(string $category): string
    {
        $severityMap = $this->attackPayloads['severity_levels'] ?? $this->getDefaultSeverityLevels();

        foreach ($severityMap as $severity => $categories) {
            if (in_array($category, $categories)) {
                return $severity;
            }
        }

        return self::SEVERITY_MEDIUM;
    }

    /**
     * Get default target fields
     *
     * @return array
     */
    private function getDefaultTargetFields(): array
    {
        return [
            'base64_fields'  => ['resourceUri', 'referred', 'page_uri'],
            'plain_fields'   => ['timezone', 'language', 'languageFullName'],
            'numeric_fields' => ['resourceUriId', 'resource_id', 'screenWidth', 'screenHeight'],
            'header_fields'  => ['User-Agent', 'Referer', 'X-Forwarded-For'],
        ];
    }

    /**
     * Get default severity levels
     *
     * @return array
     */
    private function getDefaultSeverityLevels(): array
    {
        return [
            self::SEVERITY_CRITICAL => [
                self::CATEGORY_SQL_INJECTION,
                self::CATEGORY_COMMAND_INJECTION,
                self::CATEGORY_XXE,
                self::CATEGORY_DESERIALIZATION,
            ],
            self::SEVERITY_HIGH => [
                self::CATEGORY_XSS,
                self::CATEGORY_PATH_TRAVERSAL,
                self::CATEGORY_SSRF,
            ],
            self::SEVERITY_MEDIUM => [
                self::CATEGORY_HEADER_INJECTION,
                self::CATEGORY_LDAP_INJECTION,
                self::CATEGORY_NOSQL_INJECTION,
                self::CATEGORY_ENCODING_BYPASS,
            ],
            self::SEVERITY_LOW => [
                self::CATEGORY_LOG_INJECTION,
                self::CATEGORY_PROTOTYPE_POLLUTION,
            ],
        ];
    }

    /**
     * Get default attack payloads
     *
     * @return array
     */
    private function getDefaultAttackPayloads(): array
    {
        return [
            self::CATEGORY_SQL_INJECTION => [
                'classic_union' => ["' UNION SELECT NULL--", "' UNION ALL SELECT 1,2,3--"],
                'blind_boolean' => ["' AND 1=1--", "' AND 1=2--", "' OR 1=1--"],
                'time_based'    => ["' AND SLEEP(5)--"],
                'comment_bypass' => ["admin'--", "admin'/*"],
            ],
            self::CATEGORY_XSS => [
                'script_tags'    => ['<script>alert(1)</script>', '<script>alert(document.cookie)</script>'],
                'event_handlers' => ['<img src=x onerror=alert(1)>', '<svg onload=alert(1)>'],
                'javascript_uris' => ['javascript:alert(1)'],
            ],
            self::CATEGORY_PATH_TRAVERSAL => [
                'basic_traversal'    => ['../../../etc/passwd', '..\\..\\..\\windows\\system32\\'],
                'wordpress_specific' => ['../../../wp-config.php', '../../wp-includes/version.php'],
            ],
            self::CATEGORY_HEADER_INJECTION => [
                'crlf_injection' => ["value\r\nX-Injected: header", "value%0d%0aX-Injected: header"],
            ],
            self::CATEGORY_ENCODING_BYPASS => [
                'double_url_encoding' => ['%253Cscript%253E', '%2527%2520OR%25201%253D1--'],
                'unicode_encoding'    => ['\\u003cscript\\u003e', '\\x3cscript\\x3e'],
            ],
            'target_fields' => $this->getDefaultTargetFields(),
            'severity_levels' => $this->getDefaultSeverityLevels(),
        ];
    }

    /**
     * Get available categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get available scenarios
     *
     * @return array
     */
    public function getScenarios(): array
    {
        return array_keys($this->attackPayloads['test_scenarios'] ?? []);
    }
}
