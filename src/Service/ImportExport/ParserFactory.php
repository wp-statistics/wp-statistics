<?php

namespace WP_Statistics\Service\ImportExport;

use WP_Statistics\Service\ImportExport\Contracts\ParserInterface;
use WP_Statistics\Service\ImportExport\Parsers\CsvParser;
use WP_Statistics\Service\ImportExport\Parsers\JsonParser;
use RuntimeException;

/**
 * Factory for creating file parsers based on file extension.
 *
 * Third-party plugins can register custom parsers via the
 * `wp_statistics_import_parsers` filter.
 *
 * @since 15.0.0
 */
class ParserFactory
{
    /**
     * Get a parser instance for the given file extension.
     *
     * @param string $extension File extension (e.g. 'csv', 'json').
     * @return ParserInterface
     * @throws RuntimeException If no parser is available for the extension.
     */
    public static function create(string $extension): ParserInterface
    {
        $parsers = self::getRegisteredParsers();

        $extension = strtolower($extension);

        if (!isset($parsers[$extension])) {
            throw new RuntimeException(
                sprintf(__('No parser available for file type: %s', 'wp-statistics'), $extension)
            );
        }

        $class = $parsers[$extension];

        return new $class();
    }

    /**
     * Get registered parser classes keyed by extension.
     *
     * @return array<string, class-string<ParserInterface>>
     */
    public static function getRegisteredParsers(): array
    {
        $parsers = [
            'csv'  => CsvParser::class,
            'json' => JsonParser::class,
        ];

        /**
         * Filter registered import parsers.
         *
         * Allows third-party plugins to add custom parsers for additional file formats.
         *
         * @param array<string, class-string<ParserInterface>> $parsers Extension => class map.
         */
        return apply_filters('wp_statistics_import_parsers', $parsers);
    }

    /**
     * Get supported file extensions.
     *
     * @return string[]
     */
    public static function getSupportedExtensions(): array
    {
        return array_keys(self::getRegisteredParsers());
    }
}
