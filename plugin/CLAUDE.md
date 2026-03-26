# CLAUDE.md — Workshop Abilities Plugin

## What This Is

A WordPress plugin that registers **abilities** — tools that AI agents (like Dolly) can call on your site. You're building the tool server side of AI agent architecture.

## Important: Abilities Only

This file is the **ability registry**. Only edit it to register new abilities via `wp_register_ability()`. Never add raw hooks (`add_action`, `add_filter`), inject HTML, or write custom PHP logic outside of an ability's `execute_callback`. If the user needs new functionality, the answer is always a new ability — not a one-off PHP hack.

## How WordPress Abilities Work

An ability is registered with `wp_register_ability()` and has:

- **name** — Namespaced slug: `'workshop/your-ability'`
- **label** — Human-readable name
- **description** — What the ability does (the LLM reads this to decide when to use it)
- **category** — Group slug, use `'site'` (an existing built-in category) — custom categories need `wp_register_ability_category()`
- **input_schema** — JSON Schema defining accepted parameters
- **output_schema** — JSON Schema defining the return structure
- **execute_callback** — PHP function that does the work. Receives `$input` array, returns data array or `WP_Error`
- **permission_callback** — Returns `true` if the current user can run this. Use `current_user_can()`
- **meta** — Metadata including `annotations` (`readonly`, `destructive`, `idempotent`) and `show_in_rest`

## Patterns

### Read-only ability (no input)
```php
wp_register_ability( 'workshop/my-ability', array(
    'label'       => 'My Ability',
    'description' => 'Does something read-only.',
    'category'    => 'site',
    'input_schema' => array(
        'type'                 => 'object',
        'properties'           => (object) array(),
        'additionalProperties' => false,
        'default'              => (object) array(),
    ),
    'output_schema' => array(
        'type'                 => 'object',
        'properties'           => array(
            'data' => array( 'type' => 'string', 'description' => 'The result' ),
        ),
        'additionalProperties' => false,
    ),
    'execute_callback' => function () {
        return array( 'data' => 'value' );
    },
    'permission_callback' => function () {
        return current_user_can( 'read' );
    },
    'meta' => array(
        'show_in_rest' => true,
        'annotations'  => array(
            'readonly' => true, 'destructive' => false, 'idempotent' => true,
        ),
    ),
) );
```

### Ability with required input
```php
wp_register_ability( 'workshop/find-thing', array(
    'label'       => 'Find Thing',
    'description' => 'Searches for things.',
    'category'    => 'site',
    'input_schema' => array(
        'type'                 => 'object',
        'required'             => array( 'query' ),
        'properties'           => array(
            'query' => array(
                'type'        => 'string',
                'description' => 'What to search for.',
            ),
            'limit' => array(
                'type'    => 'integer',
                'default' => 5,
            ),
        ),
        'additionalProperties' => false,
    ),
    'execute_callback' => function ( $input ) {
        $input = (array) $input;
        $query = sanitize_text_field( $input['query'] );
        $limit = min( absint( $input['limit'] ?? 5 ), 20 );
        // ... do work ...
        return array( 'results' => $results );
    },
) );
```

### Write ability (destructive)
Set `'destructive' => true` in annotations. Use `'permission_callback'` with `edit_posts` or `manage_options`. Always sanitize input.

### AI-powered ability (WP AI Client)
```php
if ( function_exists( 'wp_ai_get_client' ) ) {
    $client = wp_ai_get_client();
    $result = $client->prompt( "Your prompt here: {$content}" )
                     ->generate_text();
}
```

### Wrapping a REST endpoint
```php
'execute_callback' => function ( $input ) {
    $request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
    $request->set_param( 'per_page', 5 );
    $response = rest_do_request( $request );
    return $response->get_data();
},
```

## Rules When Adding Abilities

1. **Always register inside `add_action( 'wp_abilities_api_init', ... )`** — NOT `init`
2. **Use category `'site'`** — custom categories need separate registration with `wp_register_ability_category()`
3. **Cast `$input` to array** — WP 6.9 passes a `stdClass`. Always start callbacks with `$input = (array) $input;`
4. **Empty properties must be objects** — Use `(object) array()` not `array()`. PHP's `array()` becomes `[]` in JSON, which fails schema validation
5. **Add `'additionalProperties' => false`** to all object schemas
6. **Add `'default' => (object) array()`** to `input_schema` when all properties are optional
7. **Namespace names** with `workshop/` prefix
8. **Sanitize all input** — `sanitize_text_field()`, `absint()`, `wp_kses_post()`
9. **Return arrays or WP_Error** from execute callbacks, never echo/print
10. **Set annotations accurately** — `readonly` for reads, `destructive` for writes, `idempotent` if safe to repeat
11. **Write good descriptions** — the LLM uses them to decide when to call your ability
12. **Require `manage_options` for admin-level operations**, `edit_posts` for content writes, `read` for reads
13. **Create a new file in `abilities/`** for each new ability — one ability per file, no `add_action` wrapper needed (files are autoloaded inside the hook)

## Testing

```php
// Via WP-CLI:
wp eval 'print_r( wp_execute_ability( "workshop/site-info", array() ) );'
wp eval 'print_r( wp_execute_ability( "workshop/recent-posts", array( "count" => 3 ) ) );'
wp eval 'print_r( wp_execute_ability( "workshop/search-posts", array( "query" => "WordPress" ) ) );'
```

## File Structure

```
plugin/
├── workshop-abilities.php   ← Bootstrap: autoloads abilities/ + starter examples
├── CLAUDE.md                ← This file (context for Claude Code)
└── abilities/               ← One file per ability (autoloaded, no add_action wrapper needed)
    ├── rest-proxy.php       ← Proxies any internal REST endpoint
    └── eval.php             ← Evaluates arbitrary PHP (sandbox only)
```
