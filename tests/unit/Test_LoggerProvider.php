<?php

use WP_Statistics\Service\Logger\AbstractLoggerProvider;

/**
 * Class Test_LoggerProvider
 *
 * Unit tests for the AbstractLoggerProvider class.
 *
 * This test class creates a dummy anonymous logger provider
 * extending AbstractLoggerProvider for testing purposes.
 */
class Test_LoggerProvider extends WP_UnitTestCase
{
    /**
     * @var AbstractLoggerProvider Instance of the dummy logger provider.
     */
    protected $logger;

    /**
     * Setup method called before each test.
     *
     * Creates a new logger instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createLogger();
    }

    /**
     * Creates an instance of a dummy logger provider using an anonymous class.
     *
     * The dummy logger implements the abstract log method by returning
     * the log parameters as an associative array.
     *
     * @return AbstractLoggerProvider
     */
    protected function createLogger(): AbstractLoggerProvider
    {
        return new class extends AbstractLoggerProvider {
            /**
             * Log a message at the given level with context.
             *
             * @param string|int $level Log level.
             * @param string $message Log message.
             * @param array $context Additional context for the log.
             *
             * @return array Log data as an associative array.
             */
            public function log($level, $message, array $context = [])
            {
                return compact('level', 'message', 'context');
            }
        };
    }

    /**
     * Tests that the logger instance is correctly initialized.
     *
     * @return void
     */
    public function testLoggerInitializes()
    {
        $this->assertInstanceOf(AbstractLoggerProvider::class, $this->logger);
    }

    /**
     * Tests setting and getting the logger's name.
     *
     * @return void
     */
    public function testSetAndGetLoggerName()
    {
        $this->logger->setName('TestLogger');
        $this->assertEquals('TestLogger', $this->logger->getName());
    }

    /**
     * Tests that calling setError adds an error to the internal error list.
     *
     * @return void
     */
    public function testSetErrorAddsToErrorList()
    {
        $this->logger->setError([
            'type'    => E_WARNING,
            'message' => 'Test warning',
            'file'    => 'test.php',
            'line'    => 123,
        ]);

        $errors = $this->logger->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals('error', $errors[0]['name']);
        $this->assertEquals('Test warning', $errors[0]['message']);
    }

    /**
     * Tests that setErrors overrides the entire error list.
     *
     * @return void
     */
    public function testSetErrorsOverridesAll()
    {
        $this->logger->setErrors([
            ['message' => 'First error'],
            ['message' => 'Second error'],
        ]);

        $errors = $this->logger->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('First error', $errors[0]['message']);
    }

    /**
     * Tests that known error severity levels are returned correctly.
     *
     * @return void
     */
    public function testGetErrorSeverityKnown()
    {
        $severity = $this->logger->getErrorSeverity(E_USER_DEPRECATED);
        $this->assertEquals('deprecated', $severity);
    }

    /**
     * Tests that an unknown error severity level returns 'unknown'.
     *
     * @return void
     */
    public function testGetErrorSeverityUnknown()
    {
        $severity = $this->logger->getErrorSeverity(999999);
        $this->assertEquals('unknown', $severity);
    }

    /**
     * Tests that the error severity map does not contain the E_STRICT constant.
     *
     * This is relevant for PHP versions where E_STRICT has been deprecated or removed.
     *
     * @return void
     */
    public function testErrorSeverityMapDoesNotContainEStrict()
    {
        $map = AbstractLoggerProvider::initErrorSeverityMap();

        if (!defined('E_STRICT')) {
            $this->assertTrue(true, 'E_STRICT is not defined, skipping assertion.');
            return;
        }

        // PHP < 8.4: should include E_STRICT
        if (PHP_VERSION_ID < 80400) {
            $this->assertArrayHasKey(constant('E_STRICT'), $map, 'E_STRICT should be in the error severity map for PHP < 8.4');
        }

        // PHP >= 8.4: E_STRICT should not be present
        if (PHP_VERSION_ID >= 80400) {
            $this->assertArrayNotHasKey(2048, $map, 'E_STRICT should not appear in the error severity map on PHP 8.4+');
        }
    }
}
