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
