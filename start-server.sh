#!/bin/bash
#
# Starts WP Playground as a local server with the workshop plugin mounted.
# The plugin directory is mounted live — edits to plugin/ are reflected immediately.
#
# Usage: bash start-server.sh
#
# Once running:
#   - WordPress admin: http://localhost:9400/wp-admin/
#   - REST API:        http://localhost:9400/wp-json/
#   - Abilities API:   http://localhost:9400/wp-json/wp/v2/abilities
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BLUEPRINT="$SCRIPT_DIR/blueprint-inline.json"
PLUGIN_DIR="$SCRIPT_DIR/plugin"

if [ ! -f "$BLUEPRINT" ]; then
	echo "Error: blueprint-inline.json not found"
	exit 1
fi

echo "Starting WP Playground server on http://localhost:9400 ..."
echo ""
echo "  Plugin dir mounted from: $PLUGIN_DIR"
echo "  Edit plugin/workshop-abilities.php locally — changes are live."
echo ""
echo "  Press Ctrl+C to stop."
echo ""

npx @wp-playground/cli server \
	--blueprint="$BLUEPRINT" \
	--mount="$PLUGIN_DIR:/wordpress/wp-content/plugins/workshop-abilities" \
	--login \
	--port=9400
