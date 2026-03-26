# Path B: Build the Tool Server

Turn your WordPress site into an MCP server and talk to it from Claude Code. One exercise, 30 minutes.

## Prerequisites

- A WordPress 6.9+ site (local dev environment is fine)
- Composer
- Claude Code installed

## The Exercise

### 1. Activate the starter plugin (~5 min)

Copy `applied-wp-ai-workshop.php` to `wp-content/plugins/`. Activate it.

The plugin registers a `workshop/site-info` ability. Test it works:

```bash
wp eval 'print_r( wp_execute_ability( "workshop/site-info", array() ) );'
```

### 2. Make it yours (~10 min)

Modify the example or add a new ability. Ideas:

- Recent posts with title, date, link
- Category/tag list
- Search posts by keyword
- Something useful for your actual workflow

The starter plugin has a commented-out `workshop/recent-posts` example you can uncomment and customize.

### 3. Connect Claude Code (~10 min)

Install the MCP Adapter:

```bash
composer require wordpress/mcp-adapter
```

Add the provided `.mcp.json` to your project root (see below). Start Claude Code.

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "wp",
      "args": ["mcp-server"]
    }
  }
}
```

Ask Claude Code something that requires your abilities: "What are the recent posts on this site?" or "Tell me about this WordPress site."

**The payoff: Claude Code just called your WordPress site.**

### Done early?

Write a second ability. Or try the AI-calling-AI pattern: uncomment the `workshop/summarize-post` example in the starter plugin that uses `wp_ai_client_prompt()` inside a callback.

## Tips

- **Use Claude Code to help you build.** That's the whole point.
- Start simple. `workshop/site-info` is already working — build from there.
- Everything you build here works on any WordPress 6.9+ site. Same foundation Dolly uses.
