<?php

use WP_Statistics\Components\AssetNameObfuscator;
use WP_Statistics\Helper;
use WP_Statistics\Option;

/**
 * Class Test_AssetNameObfuscator
 *
 * Test case for AssetNameObfuscator class.
 */
class Test_AssetNameObfuscator extends WP_UnitTestCase
{
    private $testFile;

    /**
     * @var AssetNameObfuscator
     */
    public $obfuscator;

    public function setUp(): void
    {
        parent::setUp();

        // Create a temporary test file
        $this->testFile = WP_CONTENT_DIR . '/test-asset.js';
        file_put_contents($this->testFile, 'console.log("Test file");');

        // Mock Option class
        Option::saveOptionGroup('hashed_assets', [], 'hashed_assets');

        $this->obfuscator = new AssetNameObfuscator($this->testFile);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Remove test file if it exists
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }

        // Clean up hashed files
        $this->obfuscator->deleteAllHashedFiles();
        $this->obfuscator->deleteDatabaseOption();
    }

    public function test_get_hashed_file_name()
    {
        $hashedFileName = $this->obfuscator->getHashedFileName();
        $this->assertNotEmpty($hashedFileName);
        $this->assertStringEndsWith('.js', $hashedFileName);
        $this->assertSame(substr(wp_hash($this->testFile), 0, 10) . '.js', $hashedFileName);
    }

    public function test_resolves_cached_hash_from_previous_version()
    {
        $previousVersion = '14.16.13';
        $hashedFileName  = substr(wp_hash($previousVersion . $this->testFile), 0, 10) . '.js';
        $method          = new ReflectionMethod(AssetNameObfuscator::class, 'getHashedAssetPath');
        $option          = get_option('wp_statistics_hashed_assets');

        $method->setAccessible(true);

        $this->assertSame(
            $this->testFile,
            $method->invoke($this->obfuscator, $hashedFileName, $option, $previousVersion)
        );
    }

    public function test_regenerates_current_version_mapping_with_legacy_hash()
    {
        $option      = get_option('wp_statistics_hashed_assets');
        $originalKey = null;

        foreach ($option as $key => $entry) {
            if (($entry['name'] ?? null) === $this->obfuscator->getHashedFileName()) {
                $originalKey = $key;
                break;
            }
        }

        $this->assertNotNull($originalKey);

        $legacyName = substr(wp_hash(WP_STATISTICS_VERSION . $originalKey), 0, 10) . '.js';

        Option::saveOptionGroup($originalKey, [
            'version' => WP_STATISTICS_VERSION,
            'dir'     => $this->testFile,
            'name'    => $legacyName,
        ], 'hashed_assets');

        $obfuscator = new AssetNameObfuscator($this->testFile);
        $option     = get_option('wp_statistics_hashed_assets');

        $this->assertNotSame($legacyName, $obfuscator->getHashedFileName());
        $this->assertSame($obfuscator->getHashedFileName(), $option[$originalKey]['name']);
    }

    public function test_rejects_cached_hash_with_wrong_version()
    {
        $hashedFileName = substr(wp_hash('14.16.13' . $this->testFile), 0, 10) . '.js';
        $method         = new ReflectionMethod(AssetNameObfuscator::class, 'getHashedAssetPath');
        $option         = get_option('wp_statistics_hashed_assets');

        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->obfuscator, $hashedFileName, $option, '14.16.12'));
    }

    /**
     * The mapping stores the original input file path under 'dir' and
     * the hashed name under 'name'. The proxy reads the original file
     * directly, so no copy is written to uploads/.
     */
    public function test_option_entry_layout()
    {
        $option = get_option('wp_statistics_hashed_assets');
        $this->assertIsArray($option);

        $entry = null;
        foreach ($option as $candidate) {
            if (is_array($candidate) && isset($candidate['name']) && $candidate['name'] === $this->obfuscator->getHashedFileName()) {
                $entry = $candidate;
                break;
            }
        }

        $this->assertNotNull($entry, 'Expected an entry for the test file in the hashed_assets option.');
        $this->assertArrayHasKey('dir', $entry);
        $this->assertArrayHasKey('name', $entry);
        $this->assertSame($this->testFile, $entry['dir']);
    }

    /**
     * No copy of the input file should land in uploads/.
     */
    public function test_no_copy_written_to_uploads()
    {
        $uploadsDir   = Helper::get_uploads_dir();
        $hashedInUploads = path_join($uploadsDir, $this->obfuscator->getHashedFileName());

        $this->assertFileDoesNotExist($hashedInUploads);
    }

    /**
     * Test deletion of database option.
     */
    public function test_delete_database_option()
    {
        $this->obfuscator->deleteDatabaseOption();
        $option = get_option('wp_statistics_hashed_assets');
        $this->assertFalse($option);
    }

    /**
     * Test getUrlThroughProxy method.
     */
    public function test_get_url_through_proxy()
    {
        $expectedUrl = esc_url(home_url('?' . $this->obfuscator->getDynamicAssetKey() . '=' . $this->obfuscator->getHashedFileName()));

        $this->assertEquals(
            $expectedUrl,
            $this->obfuscator->getUrlThroughProxy()
        );
    }
}
