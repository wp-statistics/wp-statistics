<?php

namespace WP_Statistics\Testing\Simulator\Http;

/**
 * CurlMultiSender - Efficient parallel HTTP request sender using curl_multi
 *
 * Features:
 * - Configurable concurrency level
 * - Memory-efficient streaming of results
 * - Automatic retry on transient failures
 * - Response time tracking
 * - Rate limiting support
 *
 * @package WP_Statistics\Testing\Simulator\Http
 * @since 15.0.0
 */
class CurlMultiSender
{
    /**
     * Maximum concurrent requests
     */
    private int $maxConcurrent;

    /**
     * Target URL for requests
     */
    private string $targetUrl;

    /**
     * Connection timeout in seconds
     */
    private int $connectTimeout = 5;

    /**
     * Request timeout in seconds
     */
    private int $requestTimeout = 30;

    /**
     * Number of retries for failed requests
     */
    private int $maxRetries = 2;

    /**
     * Delay between requests in microseconds (for rate limiting)
     */
    private int $delayBetweenRequests = 0;

    /**
     * Common CURL options for all requests
     */
    private array $curlOptions = [];

    /**
     * Custom headers to send with requests
     */
    private array $headers = [];

    /**
     * Statistics tracking
     */
    private array $stats = [
        'sent'       => 0,
        'successful' => 0,
        'failed'     => 0,
        'retried'    => 0,
        'total_time' => 0,
    ];

    /**
     * Constructor
     *
     * @param string $targetUrl Target URL for requests
     * @param int $maxConcurrent Maximum concurrent requests (default: 10)
     */
    public function __construct(string $targetUrl, int $maxConcurrent = 10)
    {
        $this->targetUrl = $targetUrl;
        $this->maxConcurrent = max(1, min(100, $maxConcurrent));
        $this->initDefaultCurlOptions();
    }

    /**
     * Initialize default CURL options
     */
    private function initDefaultCurlOptions(): void
    {
        $this->curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->requestTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => false, // For local testing
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => 'gzip,deflate',
            CURLOPT_NOPROXY        => '*',   // Bypass proxy for all hosts
        ];
    }

    /**
     * Send multiple requests in parallel with streaming results
     *
     * @param \Generator|\Iterator|array $requests Generator/Iterator/Array of request data arrays
     * @param callable|null $onComplete Callback for each completed request: function(array $result): void
     * @return \Generator Yields results as they complete
     */
    public function streamRequests($requests, ?callable $onComplete = null): \Generator
    {
        $multiHandle = curl_multi_init();

        // Set curl_multi options for better performance
        curl_multi_setopt($multiHandle, CURLMOPT_MAXCONNECTS, $this->maxConcurrent);

        $activeHandles = [];
        $handleToRequestMap = [];
        $requestIterator = $this->normalizeRequests($requests);
        $running = 0;
        $requestIndex = 0;

        // Initial population of concurrent requests
        while (count($activeHandles) < $this->maxConcurrent && $requestIterator->valid()) {
            $requestData = $requestIterator->current();
            $requestIterator->next();

            $handle = $this->createCurlHandle($requestData);
            $handleId = (int)$handle;

            curl_multi_add_handle($multiHandle, $handle);
            $activeHandles[$handleId] = $handle;
            $handleToRequestMap[$handleId] = [
                'index'        => $requestIndex++,
                'request_data' => $requestData,
                'start_time'   => microtime(true),
                'retries'      => 0,
            ];
        }

        // Process requests
        do {
            // Execute requests
            $status = curl_multi_exec($multiHandle, $running);

            if ($status !== CURLM_OK) {
                break;
            }

            // Wait for activity
            if ($running > 0) {
                curl_multi_select($multiHandle, 0.1);
            }

            // Process completed requests
            while ($info = curl_multi_info_read($multiHandle)) {
                $handle = $info['handle'];
                $handleId = (int)$handle;

                if (!isset($handleToRequestMap[$handleId])) {
                    continue;
                }

                $requestInfo = $handleToRequestMap[$handleId];
                $result = $this->processCompletedRequest($handle, $info, $requestInfo);

                // Handle retries for transient failures
                if ($result['status'] === 'error' && $this->shouldRetry($result, $requestInfo)) {
                    $requestInfo['retries']++;
                    $this->stats['retried']++;

                    // Create new handle for retry
                    curl_multi_remove_handle($multiHandle, $handle);
                    curl_close($handle);
                    unset($activeHandles[$handleId]);

                    $newHandle = $this->createCurlHandle($requestInfo['request_data']);
                    $newHandleId = (int)$newHandle;

                    curl_multi_add_handle($multiHandle, $newHandle);
                    $activeHandles[$newHandleId] = $newHandle;
                    $handleToRequestMap[$newHandleId] = $requestInfo;
                    unset($handleToRequestMap[$handleId]);

                    continue;
                }

                // Remove completed handle
                curl_multi_remove_handle($multiHandle, $handle);
                curl_close($handle);
                unset($activeHandles[$handleId]);
                unset($handleToRequestMap[$handleId]);

                // Update stats
                $this->stats['sent']++;
                if ($result['status'] === 'success') {
                    $this->stats['successful']++;
                } else {
                    $this->stats['failed']++;
                }
                $this->stats['total_time'] += $result['time'];

                // Callback and yield
                if ($onComplete) {
                    $onComplete($result);
                }
                yield $result;

                // Add next request if available
                if ($requestIterator->valid()) {
                    if ($this->delayBetweenRequests > 0) {
                        usleep($this->delayBetweenRequests);
                    }

                    $requestData = $requestIterator->current();
                    $requestIterator->next();

                    $newHandle = $this->createCurlHandle($requestData);
                    $newHandleId = (int)$newHandle;

                    curl_multi_add_handle($multiHandle, $newHandle);
                    $activeHandles[$newHandleId] = $newHandle;
                    $handleToRequestMap[$newHandleId] = [
                        'index'        => $requestIndex++,
                        'request_data' => $requestData,
                        'start_time'   => microtime(true),
                        'retries'      => 0,
                    ];
                }
            }

        } while ($running > 0 || count($activeHandles) > 0 || $requestIterator->valid());

        curl_multi_close($multiHandle);
    }

    /**
     * Send requests and collect all results
     *
     * @param \Generator|\Iterator|array $requests Requests to send
     * @return array All results
     */
    public function sendAll($requests): array
    {
        $results = [];
        foreach ($this->streamRequests($requests) as $result) {
            $results[] = $result;
        }
        return $results;
    }

    /**
     * Send requests in batches with progress callback
     *
     * @param \Generator|\Iterator|array $requests Requests to send
     * @param int $batchSize Size of each batch for progress reporting
     * @param callable $onBatchComplete Callback: function(int $completed, int $successful, int $failed): void
     * @return array Final statistics
     */
    public function sendWithProgress($requests, int $batchSize, callable $onBatchComplete): array
    {
        $batchCount = 0;
        $batchSuccessful = 0;
        $batchFailed = 0;

        foreach ($this->streamRequests($requests) as $result) {
            $batchCount++;

            if ($result['status'] === 'success') {
                $batchSuccessful++;
            } else {
                $batchFailed++;
            }

            if ($batchCount >= $batchSize) {
                $onBatchComplete($this->stats['sent'], $batchSuccessful, $batchFailed);
                $batchCount = 0;
                $batchSuccessful = 0;
                $batchFailed = 0;
            }
        }

        // Final batch
        if ($batchCount > 0) {
            $onBatchComplete($this->stats['sent'], $batchSuccessful, $batchFailed);
        }

        return $this->getStats();
    }

    /**
     * Create a CURL handle for a request
     *
     * @param array $requestData Request data
     * @return resource|\CurlHandle CURL handle
     */
    private function createCurlHandle(array $requestData)
    {
        $handle = curl_init($this->targetUrl);

        // Apply base options
        curl_setopt_array($handle, $this->curlOptions);

        // Handle attack headers if present
        $headers = $this->headers;
        if (isset($requestData['_attack_headers'])) {
            foreach ($requestData['_attack_headers'] as $headerName => $headerValue) {
                $headers[] = "{$headerName}: {$headerValue}";
            }
            unset($requestData['_attack_headers']);
        }

        // Remove internal metadata before sending
        unset($requestData['_attack_meta']);

        // Set POST fields
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($requestData));

        // Set headers
        if (!empty($headers)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        }

        return $handle;
    }

    /**
     * Process a completed request
     *
     * @param resource|\CurlHandle $handle CURL handle
     * @param array $info Curl multi info
     * @param array $requestInfo Request metadata
     * @return array Result array
     */
    private function processCompletedRequest($handle, array $info, array $requestInfo): array
    {
        $response = curl_multi_getcontent($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($handle, CURLINFO_TOTAL_TIME);
        $error = curl_error($handle);
        $errno = curl_errno($handle);

        $endTime = microtime(true);
        $wallTime = $endTime - $requestInfo['start_time'];

        $result = [
            'index'        => $requestInfo['index'],
            'http_code'    => $httpCode,
            'time'         => $totalTime,
            'wall_time'    => $wallTime,
            'retries'      => $requestInfo['retries'],
            'request_data' => $requestInfo['request_data'],
        ];

        // Check for CURL errors
        if ($info['result'] !== CURLE_OK) {
            $result['status'] = 'error';
            $result['error'] = $error ?: "CURL error: {$errno}";
            $result['error_code'] = $errno;
            return $result;
        }

        // Check HTTP status
        if ($httpCode >= 200 && $httpCode < 400) {
            $result['status'] = 'success';
            $result['response'] = $response;

            // Try to parse JSON response
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result['response_data'] = $decoded;

                // Check for application-level success
                if (isset($decoded['success']) && $decoded['success'] === false) {
                    $result['status'] = 'rejected';
                    $result['rejection_reason'] = $decoded['message'] ?? 'Unknown rejection';
                }
            }
        } else {
            $result['status'] = 'error';
            $result['error'] = "HTTP error: {$httpCode}";
            $result['response'] = $response;
        }

        return $result;
    }

    /**
     * Determine if a failed request should be retried
     *
     * @param array $result Result from failed request
     * @param array $requestInfo Request metadata
     * @return bool
     */
    private function shouldRetry(array $result, array $requestInfo): bool
    {
        if ($requestInfo['retries'] >= $this->maxRetries) {
            return false;
        }

        // Retry on specific CURL errors (transient)
        $retryableErrors = [
            CURLE_OPERATION_TIMEOUTED,
            CURLE_COULDNT_CONNECT,
            CURLE_RECV_ERROR,
            CURLE_SEND_ERROR,
        ];

        if (isset($result['error_code']) && in_array($result['error_code'], $retryableErrors)) {
            return true;
        }

        // Retry on 5xx server errors
        if ($result['http_code'] >= 500 && $result['http_code'] < 600) {
            return true;
        }

        return false;
    }

    /**
     * Normalize various input types to an iterator
     *
     * @param \Generator|\Iterator|array $requests Input requests
     * @return \Iterator
     */
    private function normalizeRequests($requests): \Iterator
    {
        if ($requests instanceof \Generator || $requests instanceof \Iterator) {
            return $requests;
        }

        if (is_array($requests)) {
            return new \ArrayIterator($requests);
        }

        throw new \InvalidArgumentException('Requests must be an array, Iterator, or Generator');
    }

    /**
     * Set connection timeout
     *
     * @param int $seconds Timeout in seconds
     * @return self
     */
    public function setConnectTimeout(int $seconds): self
    {
        $this->connectTimeout = max(1, $seconds);
        $this->curlOptions[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        return $this;
    }

    /**
     * Set request timeout
     *
     * @param int $seconds Timeout in seconds
     * @return self
     */
    public function setRequestTimeout(int $seconds): self
    {
        $this->requestTimeout = max(1, $seconds);
        $this->curlOptions[CURLOPT_TIMEOUT] = $this->requestTimeout;
        return $this;
    }

    /**
     * Set maximum retries
     *
     * @param int $retries Number of retries
     * @return self
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = max(0, $retries);
        return $this;
    }

    /**
     * Set delay between requests (rate limiting)
     *
     * @param int $microseconds Delay in microseconds
     * @return self
     */
    public function setDelayBetweenRequests(int $microseconds): self
    {
        $this->delayBetweenRequests = max(0, $microseconds);
        return $this;
    }

    /**
     * Set maximum concurrent requests
     *
     * @param int $max Maximum concurrent requests
     * @return self
     */
    public function setMaxConcurrent(int $max): self
    {
        $this->maxConcurrent = max(1, min(100, $max));
        return $this;
    }

    /**
     * Add custom header
     *
     * @param string $header Header string (e.g., "X-Custom: value")
     * @return self
     */
    public function addHeader(string $header): self
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Set custom headers (replaces existing)
     *
     * @param array $headers Array of header strings
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set custom CURL option
     *
     * @param int $option CURLOPT_* constant
     * @param mixed $value Option value
     * @return self
     */
    public function setCurlOption(int $option, $value): self
    {
        $this->curlOptions[$option] = $value;
        return $this;
    }

    /**
     * Enable/disable SSL verification
     *
     * @param bool $verify Enable SSL verification
     * @return self
     */
    public function setSslVerification(bool $verify): self
    {
        $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = $verify;
        $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = $verify ? 2 : false;
        return $this;
    }

    /**
     * Get current statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = $this->stats;

        // Calculate derived stats
        $stats['success_rate'] = $stats['sent'] > 0
            ? round(($stats['successful'] / $stats['sent']) * 100, 2)
            : 0;

        $stats['avg_time'] = $stats['successful'] > 0
            ? round($stats['total_time'] / $stats['successful'], 4)
            : 0;

        $stats['requests_per_second'] = $stats['total_time'] > 0
            ? round($stats['sent'] / $stats['total_time'], 2)
            : 0;

        return $stats;
    }

    /**
     * Reset statistics
     *
     * @return self
     */
    public function resetStats(): self
    {
        $this->stats = [
            'sent'       => 0,
            'successful' => 0,
            'failed'     => 0,
            'retried'    => 0,
            'total_time' => 0,
        ];
        return $this;
    }

    /**
     * Get target URL
     *
     * @return string
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    /**
     * Set target URL
     *
     * @param string $url Target URL
     * @return self
     */
    public function setTargetUrl(string $url): self
    {
        $this->targetUrl = $url;
        return $this;
    }

    /**
     * Get max concurrent setting
     *
     * @return int
     */
    public function getMaxConcurrent(): int
    {
        return $this->maxConcurrent;
    }
}
