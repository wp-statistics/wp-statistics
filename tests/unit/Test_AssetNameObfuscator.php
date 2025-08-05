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
     * Test if hashed file directory is set correctly.
     */
    public function test_get_hashed_file_dir()
    {
        $hashedFileDir = $this->obfuscator->getHashedFileDir();
        $this->assertNotEmpty($hashedFileDir);
        $this->assertDirectoryExists(dirname($hashedFileDir));
    }

    /**
     * Test if the hashed file is created.
     */
    public function test_hashed_file_creation()
    {
        $hashedFileDir = $this->obfuscator->getHashedFileDir();
        $this->assertFileExists($hashedFileDir);
    }

    /**
     * Test deletion of all hashed files.
     */
    public function test_delete_all_hashed_files()
    {
        $hashedFileDir = $this->obfuscator->getHashedFileDir();
        $this->assertFileExists($hashedFileDir);

        $this->obfuscator->deleteAllHashedFiles();

        $this->assertFileDoesNotExist($hashedFileDir);
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
