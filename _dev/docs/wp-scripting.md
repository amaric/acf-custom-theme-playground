# Scripting WordPress from the terminal

`_dev/tools/wp/run.sh` runs PHP with WordPress, ACF and the theme loaded, against the
[Local](https://localwp.com) site this theme lives in. You don't need wp-cli or a DB password.

```bash
_dev/tools/wp/run.sh -e 'echo home_url();'      # inline
_dev/tools/wp/run.sh path/to/script.php         # a file (keep one-off scripts out of the repo)
```

How it works: it reads `~/Library/Application Support/Local/sites.json` and finds the site
whose folder contains this theme. It then points PHP's `mysqli.default_socket` at
`…/Local/run/<site-id>/mysql/mysqld.sock` and loads `wp-load.php` through
`_dev/tools/wp/bootstrap.php`. The site must be **running** in Local.

Everything below is plain WP/ACF API, for use inside a script.

## Create an ACF field group
`acf_import_field_group()` saves the group to the DB **and** writes
`acf-json/group_<key>.json`, exactly like clicking *Save* in WP Admin. Don't hand-edit the
JSON instead.

```php
<?php
$key = fn ($name) => 'field_' . substr(md5('myblock_' . $name), 0, 13); // stable + unique

acf_import_field_group([
    'key'      => 'group_' . substr(md5('block-myblock'), 0, 13),
    'title'    => 'Block: My block',
    'fields'   => [
        ['key' => $key('title'), 'label' => 'Title', 'name' => 'myblock_title', 'type' => 'text'],
        ['key' => $key('items'), 'label' => 'Items', 'name' => 'myblock_items', 'type' => 'repeater',
         'layout' => 'block', 'button_label' => 'Add item',
         'sub_fields' => [
             ['key' => $key('item_text'), 'label' => 'Text', 'name' => 'text', 'type' => 'textarea',
              'parent_repeater' => $key('items')],
         ]],
    ],
    'location' => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/myblock']]],
    'active'   => true,
]);
```

- **Adding a field to an existing group** (e.g. Theme Settings, `group_68a9bb9c8bff5`): `json_decode` the group's JSON file, splice the field into `fields`, then `acf_import_field_group()` it.
  - If the group is already in the DB, **set its `ID` first**: `$group['ID'] = acf_get_field_group($group['key'])['ID'] ?? 0;`.
  - Without the `ID`, the import creates a **second** DB group with the same key, and Theme Settings then shows twice in WP Admin. This happened once already.
  - Check afterwards with `acf_get_field_groups()`: each key must appear once.
- **Groups that exist only as JSON** (ID 0) are imported into the DB the same way.
- **Option values:** `update_field('field_name', $value, 'option')`.

## Delete the block field groups (new project)
`acf_delete_field_group()` deletes the group from the DB **and** its `acf-json/` file (ACF's
local-json listens to `acf/delete_field_group`). Groups that exist only as JSON (never
synced, `ID` 0) have no DB row, so remove their file directly.

```php
<?php
foreach (acf_get_field_groups() as $group) {
    $is_block = false;
    foreach ($group['location'] as $rules) {
        foreach ($rules as $rule) {
            $is_block = $is_block || $rule['param'] === 'block';
        }
    }
    if (!$is_block) continue;                       // keep Theme Settings, Global blocks, …

    if (!empty($group['ID'])) {
        acf_delete_field_group($group['ID']);       // DB row + JSON file
    }
    $json = get_stylesheet_directory() . '/acf-json/' . $group['key'] . '.json';
    if (file_exists($json)) unlink($json);          // JSON-only groups
    echo "deleted {$group['key']} {$group['title']}\n";
}
```

Check afterwards: `acf_get_field_groups()` lists only the options groups, and
`grep -l '"param": "block"' acf-json/*.json` prints nothing.

## Create menus and assign locations
```php
<?php
$locations = get_nav_menu_locations();
$menu_id   = wp_create_nav_menu('Agency');           // the name is the footer column title
foreach (['Services', 'Work'] as $title) {
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title'  => $title,
        'menu-item-url'    => '#',                    // placeholder until real pages exist
        'menu-item-type'   => 'custom',
        'menu-item-status' => 'publish',
    ]);
}
$locations['footer-menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
```

## Insert a block into a page
An ACF block is stored as a comment with its data inline. Each value `x` needs a matching
`_x` holding its field key. A repeater stores its row count under its own name, with each
row as `<name>_<i>_<sub>`.

```php
<?php
$data = [
    'myblock_title' => 'Hello', '_myblock_title' => 'field_…',
    'myblock_items' => 1,       '_myblock_items' => 'field_…',
    'myblock_items_0_text' => 'Row', '_myblock_items_0_text' => 'field_…',
];
$block = serialize_block([
    'blockName' => 'acf/myblock',
    'attrs' => ['name' => 'acf/myblock', 'data' => $data, 'mode' => 'preview'],
    'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => [],
]);

$page_id = (int) get_option('page_on_front');
$out = [];
foreach (parse_blocks(get_post($page_id)->post_content) as $b) {
    $out[] = serialize_block($b);                       // round-trips the existing blocks untouched
    if ($b['blockName'] === 'acf/faq') $out[] = "\n\n" . $block;   // insert after FAQ
}
wp_update_post(['ID' => $page_id, 'post_content' => wp_slash(implode('', $out))]);
```

- **Export a block's demo content:** loop over `parse_blocks()` and write `serialize_block($b)` to a file, as `_dev/examples/blocks/*/demo-content.html` was made.

## Read state quickly
```bash
_dev/tools/wp/run.sh -e 'print_r(get_nav_menu_locations());'
_dev/tools/wp/run.sh -e 'foreach (acf_get_field_groups() as $g) echo $g["key"], " ", $g["title"], "\n";'
_dev/tools/wp/run.sh -e 'echo get_post(get_option("page_on_front"))->post_content;'
```
