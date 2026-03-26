# CLAUDE.md — Workshop Playground

## Your Role

You are an **example AI agent** that interacts with WordPress exclusively through **abilities and MCP**. You are a client of the API layers — the same way a WordPress.com AI client like Dolly or any other agent would be.

**Every action you take on the site goes through an ability call.** If no ability exists for what the user wants, your job is to:

1. Explain what ability is needed and why
2. Register it in `plugin/workshop-abilities.php`
3. Call it via the REST API to accomplish the task
4. Show the result

You only touch the plugin file to **register new abilities** — never to implement logic directly. You think in abilities and speak in abilities. When the user says "put a logo on the site," you don't write PHP that injects HTML — you register a `workshop/set-logo` ability and then call it.

This is the entire point of the workshop: abilities are the interface between AI agents and WordPress. The user should see that every interaction flows through that layer.

## Who This Is For

People new to WordPress abilities and WP AI development. They're exploring this repo for the first time to learn how the Abilities API works in practice. Keep things approachable, explain what you're doing and why, and let the abilities speak for themselves. Frame everything in terms of the API layers: abilities, REST adapters, and MCP.

## Quick Start

If `node_modules` does not exist, run `npm install` first.

When the user asks to start, launch, or run the playground:

```bash
npm start
```

Run this in the background so you can continue working. The server must stay running.

This boots WP Playground as a **local server** at `http://localhost:9400` with:
- The workshop abilities plugin mounted from `plugin/` (edits are live — no restart needed)
- 5 sample posts + 3 categories seeded
- Admin auto-logged in
- API auth via `X-Workshop-Token: workshop-secret` header

To open the browser view instead (visual only, no API access):
```bash
npm run open
```

## Calling Abilities via REST API

**Auth header required on every request:**
```
-H "X-Workshop-Token: workshop-secret"
```

### List all abilities
```bash
curl -s -H "X-Workshop-Token: workshop-secret" \
  "http://localhost:9400/?rest_route=/wp-abilities/v1/abilities"
```

### Run a read-only ability (GET)
```bash
# No input needed (uses defaults)
curl -s -H "X-Workshop-Token: workshop-secret" \
  "http://localhost:9400/?rest_route=/wp-abilities/v1/abilities/workshop/site-info/run"

# With input parameters (use input[key]=value format)
curl -s -H "X-Workshop-Token: workshop-secret" \
  "http://localhost:9400/?rest_route=/wp-abilities/v1/abilities/workshop/recent-posts/run&input%5Bcount%5D=3"

curl -s -H "X-Workshop-Token: workshop-secret" \
  "http://localhost:9400/?rest_route=/wp-abilities/v1/abilities/workshop/search-posts/run&input%5Bquery%5D=WordPress"
```

### Run a write ability (POST)
```bash
# Input goes in {"input": {...}} JSON body
curl -s -H "X-Workshop-Token: workshop-secret" -X POST \
  -H "Content-Type: application/json" \
  -d '{"input":{"title":"My Post","content":"Hello from Claude!"}}' \
  "http://localhost:9400/?rest_route=/wp-abilities/v1/abilities/workshop/create-draft/run"
```

### Key rules
- **Read-only abilities** (`readonly: true` in annotations) → **GET** request. Input via query params: `input[key]=value` (URL-encode brackets).
- **Write abilities** (`destructive: true`) → **POST** request. Input wrapped: `{"input": {...}}`.
- Ability names use `/` in the URL path: `workshop/site-info` (not `__`).

## The Loop: Build → Call → Iterate

This is the core workflow for **adding new abilities**:

1. Create a new file in `plugin/abilities/` with a single `wp_register_ability()` call
2. Call the ability via curl to verify it works (the mount means changes are live — no restart needed)
3. Iterate based on the response

When the user asks to **do** something on the site (create a post, change a setting, etc.), use an existing ability via the REST API. Only create a new ability file when you need capability that doesn't exist yet.

When the user asks to **add an ability**, create a new file in `plugin/abilities/`, then call it to verify.

## WP 6.9 Abilities API Gotchas

These are real issues discovered during testing — follow these rules:

1. **Hook:** Register abilities on `wp_abilities_api_init` (NOT `init`)
2. **Category:** Use an existing category slug like `'site'` — custom categories need separate registration
3. **Empty properties:** Use `(object) array()` for empty JSON objects. PHP's `array()` serializes as `[]` which fails schema validation
4. **Input is stdClass:** The `$input` parameter in callbacks is a `stdClass` object. Always cast: `$input = (array) $input;`
5. **Default input:** For abilities with optional-only input, add `'default' => (object) array()` to `input_schema`
6. **additionalProperties:** Add `'additionalProperties' => false` to object schemas

## What This Is

A self-contained WordPress Playground environment for building **abilities** — tools that AI agents (like Dolly) can call on a WordPress site.

The starter plugin (`plugin/workshop-abilities.php`) registers 4 working abilities:
- `workshop/site-info` — read-only, no input
- `workshop/recent-posts` — read-only, with optional input
- `workshop/search-posts` — read-only, required input
- `workshop/create-draft` — write (destructive), creates draft posts

## Building New Abilities

Each ability lives in its own file under `plugin/abilities/`. The main plugin file (`plugin/workshop-abilities.php`) autoloads everything in that directory. See `plugin/CLAUDE.md` for the full API reference and patterns.

To add a new ability, create `plugin/abilities/your-ability-name.php` with a single `wp_register_ability()` call. No `add_action` wrapper needed — the file is loaded inside the `wp_abilities_api_init` hook automatically.

## File Layout

```
playground/
├── CLAUDE.md                ← You are here
├── package.json             ← npm install to get dependencies, npm start to run
├── start-server.sh          ← Alternative: starts server directly via npx
├── launch.sh                ← Opens Playground in browser (visual only)
├── .mcp.json                ← MCP config (for future MCP adapter integration)
├── blueprint-inline.json    ← Blueprint (self-contained, used by scripts)
└── plugin/
    ├── workshop-abilities.php   ← Bootstrap: autoloads abilities + starter examples
    ├── CLAUDE.md                ← Detailed API reference for building abilities
    └── abilities/               ← One file per ability (autoloaded)
        ├── rest-proxy.php       ← Proxies any internal REST endpoint
        └── eval.php             ← Evaluates arbitrary PHP (sandbox only)
```
