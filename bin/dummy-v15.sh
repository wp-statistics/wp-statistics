#!/bin/bash
# WP Statistics v15 Dummy Data Generator
# Bash wrapper for easy execution

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

php "$SCRIPT_DIR/dummy-v15.php" "$@"
