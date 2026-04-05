#!/usr/bin/env bash
#
# WP Statistics v15 — HTTP Load Test for Tracker Endpoints
#
# Tests the tracker endpoints under concurrent HTTP load using curl.
# Measures response times, error rates, and throughput.
#
# Usage:
#   ./tests/benchmark-tracker-http.sh                    # Run all tests
#   ./tests/benchmark-tracker-http.sh --base-url=https://wp-statistics.test
#   ./tests/benchmark-tracker-http.sh --test=concurrent  # Run specific test
#   ./tests/benchmark-tracker-http.sh --concurrency=20   # Set concurrency
#
# Requirements: curl, bc, awk

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────

BASE_URL="${BASE_URL:-https://wp-statistics.test}"
AJAX_URL="${BASE_URL}/wp-admin/admin-ajax.php"
CONCURRENCY=10
TOTAL_REQUESTS=100
SELECTED_TEST=""

# Parse args
for arg in "$@"; do
    case $arg in
        --base-url=*)   BASE_URL="${arg#*=}"; AJAX_URL="${BASE_URL}/wp-admin/admin-ajax.php" ;;
        --concurrency=*) CONCURRENCY="${arg#*=}" ;;
        --requests=*)   TOTAL_REQUESTS="${arg#*=}" ;;
        --test=*)       SELECTED_TEST="${arg#*=}" ;;
    esac
done

# ─── Helpers ──────────────────────────────────────────────────────────────────

GREEN='\033[32m'
RED='\033[31m'
YELLOW='\033[33m'
CYAN='\033[36m'
NC='\033[0m'

banner() {
    echo ""
    printf '%.0s─' {1..60}; echo ""
    echo "  $1"
    printf '%.0s─' {1..60}; echo ""
    echo ""
}

result() {
    local icon="$1" metric="$2" value="$3" unit="${4:-}"
    printf "  %b %-40s %s %s\n" "$icon" "$metric" "$value" "$unit"
}

PASS="${GREEN}✓${NC}"
FAIL="${RED}✗${NC}"
WARN="${YELLOW}⚠${NC}"
INFO="${CYAN}ℹ${NC}"

RESULTS_DIR=$(mktemp -d)
trap "rm -rf $RESULTS_DIR" EXIT

# Get valid signature from the frontend page
get_signature() {
    local resource_type="${1:-post}"
    local resource_id="${2:-1}"
    local user_id="${3:-0}"

    # Fetch a page to get the AUTH_KEY+AUTH_SALT based signature
    # Since we can't compute server-side MD5, we'll skip signature for load tests
    # and rely on the server's configuration
    echo ""
}

# Send a single hit request and return timing
send_hit() {
    local ip="${1:-192.168.1.100}"
    local resource_id="${2:-1}"
    local output_file="${3:-/dev/null}"

    local referrer
    referrer=$(echo -n "https://google.com" | base64)
    local resource_uri
    resource_uri=$(echo -n "/sample-page-${resource_id}" | base64)

    curl -sk -o "$output_file" -w '%{http_code} %{time_total}' \
        -X POST "$AJAX_URL" \
        -H "X-Forwarded-For: $ip" \
        -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0" \
        -d "action=wp_statistics_collect" \
        -d "resource_uri_id=0" \
        -d "resource_type=post" \
        -d "resource_id=${resource_id}" \
        -d "user_id=0" \
        -d "referrer=${referrer}" \
        -d "resource_uri=${resource_uri}" \
        -d "tracking_level=full" \
        -d "timezone=UTC" \
        -d "language_code=en-US" \
        -d "language_name=English" \
        -d "screen_width=1920" \
        -d "screen_height=1080" \
        -d "signature=" \
        2>/dev/null
}

# Send a batch request
send_batch() {
    local ip="${1:-192.168.1.100}"
    local engagement_ms="${2:-5000}"
    local output_file="${3:-/dev/null}"

    local batch_data
    batch_data=$(cat <<EOJSON
{"engagement_time":${engagement_ms},"events":[{"type":"custom_event","data":{"action":"click"},"timestamp":$(date +%s)000,"url":"${BASE_URL}/"}]}
EOJSON
)

    curl -sk -o "$output_file" -w '%{http_code} %{time_total}' \
        -X POST "$AJAX_URL" \
        -H "X-Forwarded-For: $ip" \
        -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36" \
        -d "action=wp_statistics_batch" \
        --data-urlencode "batch_data=${batch_data}" \
        2>/dev/null
}

# ─── Tests ────────────────────────────────────────────────────────────────────

test_connectivity() {
    banner "Test 0: Connectivity Check"

    local response_file="$RESULTS_DIR/connectivity.json"
    local result_line
    result_line=$(curl -sk -o "$response_file" -w '%{http_code} %{time_total}' \
        -X POST "$AJAX_URL" \
        -d "action=wp_statistics_collect" \
        2>/dev/null)

    local http_code time_total
    http_code=$(echo "$result_line" | awk '{print $1}')
    time_total=$(echo "$result_line" | awk '{print $2}')

    if [[ "$http_code" -eq 000 ]]; then
        result "$FAIL" "Cannot reach $AJAX_URL" "" ""
        echo "  Check that the site is running and accessible."
        exit 1
    fi

    result "$PASS" "Endpoint reachable" "$AJAX_URL" ""
    result "$INFO" "HTTP status" "$http_code" ""
    result "$INFO" "Response time" "${time_total}s" ""

    if [[ -f "$response_file" ]]; then
        local body
        body=$(cat "$response_file")
        result "$INFO" "Response body" "${body:0:80}" ""
    fi
}

test_single_hit_latency() {
    banner "Test 1: Single Hit Latency (${TOTAL_REQUESTS} sequential)"

    local times=()
    local errors=0
    local successes=0

    for i in $(seq 1 "$TOTAL_REQUESTS"); do
        local ip="10.0.$((i % 255)).$((i / 255 + 1))"
        local response_file="$RESULTS_DIR/hit_${i}.json"
        local result_line
        result_line=$(send_hit "$ip" "$((i % 20 + 1))" "$response_file")

        local http_code time_total
        http_code=$(echo "$result_line" | awk '{print $1}')
        time_total=$(echo "$result_line" | awk '{print $2}')

        if [[ "$http_code" -eq 200 ]]; then
            times+=("$time_total")
            ((successes++))
        else
            ((errors++))
        fi

        # Progress indicator
        if (( i % 25 == 0 )); then
            echo "  ... $i / $TOTAL_REQUESTS requests sent"
        fi
    done

    if [[ ${#times[@]} -eq 0 ]]; then
        result "$FAIL" "No successful requests" "" ""
        return
    fi

    # Calculate statistics
    local sorted
    sorted=$(printf '%s\n' "${times[@]}" | sort -g)
    local count=${#times[@]}
    local sum
    sum=$(printf '%s\n' "${times[@]}" | awk '{s+=$1}END{print s}')
    local avg
    avg=$(echo "scale=4; $sum / $count" | bc)
    local p50
    p50=$(echo "$sorted" | sed -n "$((count * 50 / 100 + 1))p")
    local p95
    p95=$(echo "$sorted" | sed -n "$((count * 95 / 100 + 1))p")
    local p99
    p99=$(echo "$sorted" | sed -n "$((count * 99 / 100 + 1))p")
    local min_t
    min_t=$(echo "$sorted" | head -1)
    local max_t
    max_t=$(echo "$sorted" | tail -1)

    result "$INFO" "Successful requests" "$successes / $TOTAL_REQUESTS" "($errors errors)"
    result "$INFO" "Average latency" "${avg}s" ""
    result "$INFO" "P50 latency" "${p50}s" ""
    result "$INFO" "P95 latency" "${p95}s" ""
    result "$INFO" "P99 latency" "${p99}s" ""
    result "$INFO" "Min / Max" "${min_t}s / ${max_t}s" ""
}

test_concurrent_load() {
    banner "Test 2: Concurrent Load (${CONCURRENCY} parallel x ${TOTAL_REQUESTS} total)"

    local batch_size=$CONCURRENCY
    local num_batches=$((TOTAL_REQUESTS / batch_size))
    local timing_file="$RESULTS_DIR/concurrent_times.txt"
    local error_file="$RESULTS_DIR/concurrent_errors.txt"

    > "$timing_file"
    > "$error_file"

    local start_time
    start_time=$(date +%s)

    for batch in $(seq 1 "$num_batches"); do
        # Launch concurrent requests
        for j in $(seq 1 "$batch_size"); do
            local idx=$(( (batch - 1) * batch_size + j ))
            local ip="10.1.$((idx % 255)).$((idx / 255 + 1))"
            (
                local result_line
                result_line=$(send_hit "$ip" "$((idx % 50 + 1))" /dev/null)
                local http_code time_total
                http_code=$(echo "$result_line" | awk '{print $1}')
                time_total=$(echo "$result_line" | awk '{print $2}')

                if [[ "$http_code" -eq 200 ]]; then
                    echo "$time_total" >> "$timing_file"
                else
                    echo "$http_code" >> "$error_file"
                fi
            ) &
        done
        wait

        if (( batch % 5 == 0 )); then
            echo "  ... batch $batch / $num_batches complete"
        fi
    done

    local end_time
    end_time=$(date +%s)
    local duration=$((end_time - start_time))

    local successes
    successes=$(wc -l < "$timing_file" | tr -d ' ')
    local errors
    errors=$(wc -l < "$error_file" | tr -d ' ')
    local throughput
    throughput=$(echo "scale=1; $successes / ($duration + 1)" | bc)

    result "$INFO" "Duration" "${duration}s" ""
    result "$INFO" "Successful" "$successes" "requests"
    result "$INFO" "Errors" "$errors" "requests"
    result "$INFO" "Throughput" "$throughput" "req/s"

    if [[ -s "$timing_file" ]]; then
        local sorted
        sorted=$(sort -g "$timing_file")
        local count
        count=$(wc -l < "$timing_file" | tr -d ' ')
        local avg
        avg=$(awk '{s+=$1}END{printf "%.4f", s/NR}' "$timing_file")
        local p50
        p50=$(echo "$sorted" | sed -n "$((count * 50 / 100 + 1))p")
        local p95
        p95=$(echo "$sorted" | sed -n "$((count * 95 / 100 + 1))p")

        result "$INFO" "Avg latency" "${avg}s" ""
        result "$INFO" "P50 latency" "${p50}s" ""
        result "$INFO" "P95 latency" "${p95}s" ""
    fi

    if [[ -s "$error_file" ]]; then
        echo ""
        echo "  Error codes:"
        sort "$error_file" | uniq -c | sort -rn | while read -r cnt code; do
            echo "    HTTP $code: $cnt times"
        done
    fi
}

test_batch_endpoint() {
    banner "Test 3: Batch/Engagement Endpoint (50 requests)"

    local times=()
    local errors=0

    # First create some sessions via hits
    for i in $(seq 1 5); do
        send_hit "10.88.0.$i" "$i" /dev/null > /dev/null 2>&1
    done

    for i in $(seq 1 50); do
        local ip="10.88.0.$((i % 5 + 1))"
        local engagement=$((RANDOM % 30000 + 1000))
        local response_file="$RESULTS_DIR/batch_${i}.json"

        local result_line
        result_line=$(send_batch "$ip" "$engagement" "$response_file")

        local http_code time_total
        http_code=$(echo "$result_line" | awk '{print $1}')
        time_total=$(echo "$result_line" | awk '{print $2}')

        if [[ "$http_code" -eq 200 ]]; then
            times+=("$time_total")
        else
            ((errors++))
        fi
    done

    if [[ ${#times[@]} -gt 0 ]]; then
        local count=${#times[@]}
        local sum
        sum=$(printf '%s\n' "${times[@]}" | awk '{s+=$1}END{print s}')
        local avg
        avg=$(echo "scale=4; $sum / $count" | bc)

        result "$INFO" "Successful" "$count / 50" "($errors errors)"
        result "$INFO" "Avg batch latency" "${avg}s" ""
    else
        result "$FAIL" "No successful batch requests" "" ""
    fi
}

test_rate_limit_burst() {
    banner "Test 4: Rate Limit Burst Test (60 rapid hits, same IP)"

    local accepted=0
    local rejected=0
    local single_ip="192.168.99.99"

    for i in $(seq 1 60); do
        local response_file="$RESULTS_DIR/rate_${i}.json"
        local result_line
        result_line=$(send_hit "$single_ip" "$((i % 5 + 1))" "$response_file")

        local http_code
        http_code=$(echo "$result_line" | awk '{print $1}')

        if [[ "$http_code" -eq 200 ]]; then
            ((accepted++))
        elif [[ "$http_code" -eq 429 ]]; then
            ((rejected++))
        fi
    done

    result "$INFO" "Accepted" "$accepted" "requests"
    result "$INFO" "Rejected (429)" "$rejected" "requests"

    if [[ $rejected -gt 0 ]]; then
        result "$PASS" "Rate limiter active" "YES" ""
    else
        result "$WARN" "Rate limiter active" "NO (may be disabled)" ""
    fi
}

test_sustained_throughput() {
    banner "Test 5: Sustained Throughput (30 seconds, ${CONCURRENCY} concurrent)"

    local duration=30
    local timing_file="$RESULTS_DIR/sustained_times.txt"
    local error_file="$RESULTS_DIR/sustained_errors.txt"

    > "$timing_file"
    > "$error_file"

    local start_time
    start_time=$(date +%s)
    local request_num=0

    while true; do
        local now
        now=$(date +%s)
        if (( now - start_time >= duration )); then
            break
        fi

        # Fire concurrent batch
        for j in $(seq 1 "$CONCURRENCY"); do
            ((request_num++))
            local ip="10.5.$((request_num % 255)).$((request_num / 255 % 255 + 1))"
            (
                local result_line
                result_line=$(send_hit "$ip" "$((request_num % 100 + 1))" /dev/null)
                local http_code time_total
                http_code=$(echo "$result_line" | awk '{print $1}')
                time_total=$(echo "$result_line" | awk '{print $2}')

                if [[ "$http_code" -eq 200 ]]; then
                    echo "$time_total" >> "$timing_file"
                else
                    echo "$http_code" >> "$error_file"
                fi
            ) &
        done
        wait
    done

    local end_time
    end_time=$(date +%s)
    local actual_duration=$((end_time - start_time))

    local successes
    successes=$(wc -l < "$timing_file" | tr -d ' ')
    local errors
    errors=$(wc -l < "$error_file" | tr -d ' ')
    local throughput
    throughput=$(echo "scale=1; $successes / ($actual_duration + 1)" | bc)

    result "$INFO" "Duration" "${actual_duration}s" ""
    result "$INFO" "Total requests" "$request_num" ""
    result "$INFO" "Successful" "$successes" ""
    result "$INFO" "Errors" "$errors" ""
    result "$INFO" "Throughput" "$throughput" "req/s"

    if [[ -s "$timing_file" ]]; then
        local avg
        avg=$(awk '{s+=$1}END{printf "%.4f", s/NR}' "$timing_file")
        result "$INFO" "Avg latency" "${avg}s" ""

        # Check for degradation: compare first vs last quarter
        local count
        count=$(wc -l < "$timing_file" | tr -d ' ')
        local quarter=$((count / 4))
        if [[ $quarter -gt 5 ]]; then
            local first_avg
            first_avg=$(head -n "$quarter" "$timing_file" | awk '{s+=$1}END{printf "%.4f", s/NR}')
            local last_avg
            last_avg=$(tail -n "$quarter" "$timing_file" | awk '{s+=$1}END{printf "%.4f", s/NR}')

            result "$INFO" "First quarter avg" "${first_avg}s" ""
            result "$INFO" "Last quarter avg" "${last_avg}s" ""
        fi
    fi
}

# ─── Runner ───────────────────────────────────────────────────────────────────

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║   WP Statistics v15 — HTTP Load Test Suite                ║"
echo "╠════════════════════════════════════════════════════════════╣"
echo "║  Target: $BASE_URL"
echo "║  Concurrency: $CONCURRENCY | Requests: $TOTAL_REQUESTS"
echo "╚════════════════════════════════════════════════════════════╝"

test_connectivity

if [[ -n "$SELECTED_TEST" ]]; then
    case "$SELECTED_TEST" in
        latency)     test_single_hit_latency ;;
        concurrent)  test_concurrent_load ;;
        batch)       test_batch_endpoint ;;
        rate_limit)  test_rate_limit_burst ;;
        sustained)   test_sustained_throughput ;;
        *)
            echo "Unknown test: $SELECTED_TEST"
            echo "Available: latency, concurrent, batch, rate_limit, sustained"
            exit 1
            ;;
    esac
else
    test_single_hit_latency
    test_concurrent_load
    test_batch_endpoint
    test_rate_limit_burst
    test_sustained_throughput
fi

echo ""
printf '%.0s═' {1..60}; echo ""
echo "  All tests complete."
printf '%.0s═' {1..60}; echo ""
echo ""
