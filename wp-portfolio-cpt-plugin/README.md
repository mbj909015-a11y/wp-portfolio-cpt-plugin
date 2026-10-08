# Portfolio Showcase (WordPress Plugin)

A small custom WordPress plugin that adds a **Project** custom post type, a **Technology** taxonomy, a custom meta field, REST API support, and a shortcode to display projects.

## Features
- Custom post type `psc_project` (archive at `/projects`)
- Custom taxonomy `psc_technology`
- Meta box for a live project URL (nonce check, capability check, sanitized and escaped)
- Exposed in the REST API (`/wp-json/wp/v2/psc_project`)
- Shortcode: `[projects limit="6" technology="wordpress"]`

## Installation
1. Download this folder as a ZIP.
2. In WordPress, go to **Plugins > Add New > Upload Plugin** and upload the ZIP.
3. Activate the plugin, then add projects from the **Projects** menu.

## Usage
Place the shortcode in any page or post:

```
[projects limit="6" technology="wordpress"]
```

## What it demonstrates
WordPress hooks (actions and shortcodes), custom post types and taxonomies, post meta, REST API exposure, WP_Query, and WordPress security practices (nonces, capability checks, sanitization, output escaping).

## Stack
PHP · WordPress
