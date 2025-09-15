#!/bin/bash

# Function to generate a random IP address
generate_random_ip() {
  echo "$((RANDOM % 256)).$((RANDOM % 256)).$((RANDOM % 256)).$((RANDOM % 256))"
}

# Array of random user agents
user_agents=(
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/91.0.4472.124 Safari/537.36"
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/91.0.4472.114 Safari/537.36"
  "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 Version/14.1.1 Mobile/15E148 Safari/604.1"
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0"
)

# Array of random referrers
referrers=(
  "https://www.google.com/"
  "https://www.bing.com/"
)

# Check if the number of records is passed as an argument
if [ -z "$1" ]; then
  echo "Usage: bash $0 <number_of_records>"
  exit 1
fi

num_records=$1

# Loop to insert multiple records
for i in $(seq 1 $num_records)
do
  random_ip=$(generate_random_ip)
  random_user_agent=${user_agents[$RANDOM % ${#user_agents[@]}]}
  random_referrer=${referrers[$RANDOM % ${#referrers[@]}]}
  random_user_id=$((RANDOM % 100 + 1))

  # Get a random post ID and type using wp post list
  post_info=$(wp post list --post_type=post,page --post_status=publish --format=ids | tr ' ' '\n' | shuf -n1)
  random_post_id=$post_info

  # Get the post type and relative URI
  random_post_type=$(wp post get $random_post_id --field=post_type)
  random_post_uri=$(wp post get $random_post_id --field=post_name)

  # Record the hit using WP-CLI
  wp statistics record \
    --ip="$random_ip" \
    --user_agent="$random_user_agent" \
    --referrer="$random_referrer" \
    --user_id="$random_user_id" \
    --request_uri="$random_post_uri" \
    --resource_type="$random_post_type" \
    --resource_id="$random_post_id"

  echo "Inserted record $i | IP: $random_ip | UA: $random_user_agent | URI: $random_post_uri | Referrer: $random_referrer"
done

echo "Inserted $num_records records successfully."