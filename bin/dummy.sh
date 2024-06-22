#!/bin/bash

# Function to generate a random IP address
generate_random_ip() {
  echo "$((RANDOM % 256)).$((RANDOM % 256)).$((RANDOM % 256)).$((RANDOM % 256))"
}

# Array of random user agents
user_agents=(
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36"
  "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1"
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0"
  "Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Mobile Safari/537.36"
)

# Array of random request URIs
request_uris=(
  "/privacy-policy"
  "/terms-of-service"
  "/about-us"
  "/contact"
  "/blog/post-1"
  "/blog/post-2"
  "/blog/post-3"
)

# Array of random referrers
referrers=(
  "https://referrer1.com"
  "https://referrer2.com"
  "https://referrer3.com"
  "https://referrer4.com"
  "https://referrer5.com"
)

# Check if the number of records is passed as an argument
if [ -z "$1" ]; then
  echo "Usage: bash tools/dummy.sh <number_of_records>"
  exit 1
fi

# Number of records to insert
num_records=$1

# Loop to insert multiple records
for i in $(seq 1 $num_records)
do
  random_ip=$(generate_random_ip)
  random_user_agent=${user_agents[$RANDOM % ${#user_agents[@]}]}
  random_request_uri=${request_uris[$RANDOM % ${#request_uris[@]}]}
  random_referrer=${referrers[$RANDOM % ${#referrers[@]}]}
  
  wp statistics record \
    --url="https://example.com" \
    --ip="$random_ip" \
    --user_agent="$random_user_agent" \
    --referrer="$random_referrer" \
    --user_id="$((RANDOM % 100 + 1))" \
    --request_uri="$random_request_uri"

  echo "Inserted record $i with IP $random_ip, User Agent $random_user_agent, Request URI $random_request_uri, Referrer $random_referrer"
done

echo "Inserted $num_records records."
