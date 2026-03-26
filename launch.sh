#!/bin/bash
#
# Launches WP Playground in the browser with the workshop abilities plugin pre-installed.
# Usage: bash launch.sh
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BLUEPRINT="$SCRIPT_DIR/blueprint-inline.json"

if [ ! -f "$BLUEPRINT" ]; then
	echo "Error: blueprint-inline.json not found at $BLUEPRINT"
	exit 1
fi

# Base64-encode the blueprint (no line wraps) for the Playground URL fragment
ENCODED=$(base64 < "$BLUEPRINT" | tr -d '\n')

URL="https://playground.wordpress.net/#${ENCODED}"

echo "Opening WP Playground with workshop abilities plugin..."
echo ""
echo "Once it loads:"
echo "  - Plugin is pre-activated (Plugins page)"
echo "  - 5 sample posts + 3 categories are seeded"
echo "  - 4 abilities registered: site-info, recent-posts, search-posts, create-draft"
echo ""

# Open in default browser (macOS: open, Linux: xdg-open)
if command -v open &> /dev/null; then
	open "$URL"
elif command -v xdg-open &> /dev/null; then
	xdg-open "$URL"
else
	echo "Could not detect browser. Open this URL manually:"
	echo "$URL"
fi
