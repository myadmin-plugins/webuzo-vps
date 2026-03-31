---
name: webuzo-page-function
description: Creates a new Webuzo page handler function following the project's procedural pattern. Scaffolds a file in `src/` with `add_output()`, `TFTable` forms, CSRF, `get_service()`, `get_module_db()`, and registers it in `src/Plugin.php` `getRequirements()`. Use when user says 'add page', 'new webuzo function', 'create handler', or adds files to `src/webuzo_*.php`. Do NOT use for SDK methods or test files.
---
# Webuzo Page Function

## Critical

- Every page function file MUST be registered in `src/Plugin.php` `getRequirements()` — unregistered files are unreachable.
- All form submissions MUST call `verify_csrf_referrer(__LINE__, __FILE__)` before processing input.
- CSRF tokens MUST be set on TFTable forms via `$tableObj->csrf('unique_form_name')`.
- Never use PDO. Use `$db = get_module_db('vps')` and `$db->query()` / `$db->next_record(MYSQL_ASSOC)` / `$db->Record`.
- Never interpolate raw `$_GET`/`$_POST` — always read from `$GLOBALS['tf']->variables->request` and escape with `$db->real_escape()` for SQL.
- The Webuzo password is stored in `history_log` where `history_old_value = 'Webuzo Details'` — retrieve it via DB query, never hardcode.

## Instructions

### Step 1: Create the page function file

Create a new page function file in `src/` following the naming convention (e.g., `src/webuzo_list_domains.php`, `src/webuzo_add_domain.php`). The file contains a single procedural function named identically to the filename without the extension.

**File skeleton — two function signatures exist in this project:**

**Type A: Standalone page** (called directly by router, retrieves credentials itself — see `src/webuzo_list_domains.php` for reference):
```php
<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_list_domains($host = null, $user = null, $pass = null)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $vps_id = $GLOBALS['tf']->variables->request['vps_id'] ?? '';
    // ... function body
}
```

**Type B: Sub-action page** (called from `webuzo_scripts()` dispatcher with credentials pre-filled — see `src/webuzo_view_script.php` for reference):
```php
<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_view_script($host, $user, $pass, $script_id)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $vps_id = $GLOBALS['tf']->variables->request['vps_id'] ?? '';
    // ... function body
}
```

Choose Type A if the page is a top-level navigation target. Choose Type B if it's dispatched from `webuzo_scripts()` via the `action` parameter.

Verify: The function name matches the filename exactly (e.g., `webuzo_list_domains` in `src/webuzo_list_domains.php`).

### Step 2: Implement the credential retrieval pattern (Type A pages only)

Type A pages must fetch Webuzo credentials from the database:

```php
$service = get_service($vps_id, 'vps');
$db = get_module_db('vps');
$query = "select * from history_log where history_owner = '{$service['vps_custid']}' and history_old_value = 'Webuzo Details'";
$db->query($query);
$user = 'admin';
$host = $service['vps_ip'];
while ($db->next_record(MYSQL_ASSOC)) {
    if (isset($db->Record['history_new_value'])) {
        $pass = $db->Record['history_new_value'];
    }
}
```

Verify: `get_service()` is called with `'vps'` as the module. The username is always `'admin'`.

### Step 3: Implement the Webuzo API call

Instantiate `Webuzo_API` and call the relevant SDK method:

```php
$new = new Webuzo_API($user, $pass, $host);
$res = $new->some_method($args);
$res = myadmin_unstringify($res);
```

Alternatively, for raw API calls use the helper:
```php
function_requirements('webuzo_api_call');
$response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
$response = myadmin_unstringify($response);
```

Verify: `include_once __DIR__.'/webuzo_sdk.php';` is at the top of the function body. If using `webuzo_api_call()` or other helpers, load them with `function_requirements()`.

### Step 4: Build the form (if the page has user input)

Use `TFTable` for all forms. Follow this exact pattern:

```php
$tableObj = new TFTable();
$tableObj->set_options('cellpadding="10"');
$tableObj->csrf('webuzo_add_domain');
$tableObj->set_title('Page Title');
$tableObj->set_post_location('iframe.php');
$tableObj->set_choice('none.webuzo_add_domain');
$tableObj->add_hidden('vps_id', "$vps_id");

$tableObj->add_field('Label', 'l');
$tableObj->add_field($tableObj->make_input('field_name', '', '40'), 'l');
$tableObj->add_row();

$tableObj->set_colspan(2);
$tableObj->add_field($tableObj->make_submit('Submit Label'));
$tableObj->add_row();
add_output($tableObj->get_table());
```

Verify: `csrf()` is called with a unique form identifier. `set_post_location('iframe.php')` is set. `vps_id` is passed as a hidden field.

### Step 5: Handle form submission with CSRF check

Wrap all form processing in a CSRF verification check:

```php
if (isset($GLOBALS['tf']->variables->request['submit_field']) && verify_csrf_referrer(__LINE__, __FILE__)) {
    // Process the submission
    // Call Webuzo API
    // Show success/error via add_output()
} else {
    // Show the form (Step 4)
}
```

Verify: `verify_csrf_referrer(__LINE__, __FILE__)` is present in the condition.

### Step 6: Output results and navigation

All output goes through `add_output()`. Success/error messages follow this pattern:

```php
if (!empty($res['done'])) {
    add_output('Operation completed successfully!');
} else {
    add_output('Error in operation<br/>');
    if (!empty($res['error'])) {
        add_output('Error details: '.$res['error']);
    }
}
add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_domains&vps_id='.$vps_id.'">Back</a>');
```

Verify: Back links use `target="SERVICEFrame1"` and route through `iframe.php?choice=none.webuzo_scripts&action=...`.

### Step 7: Register the page in Plugin.php

Add a `$loader->add_page_requirement()` line in `src/Plugin.php` inside the `getRequirements()` method:

```php
$loader->add_page_requirement('webuzo_list_dns', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_dns.php');
```

Insert it alphabetically or grouped with related pages (domain pages together, backup pages together, etc.).

Verify: The first argument matches the function name exactly. The path starts with `'/../vendor/detain/myadmin-webuzo-vps/src/'`.

### Step 8: Add logging (if the page performs mutations)

For any create/update/delete operations, add logging:

```php
myadmin_log('vps', 'info', 'Description of what happened', __LINE__, __FILE__);
```

Verify: Module is `'vps'`. Level is `'info'` for normal operations, `'error'` for failures, `'debug'` for verbose output.

### Step 9: Run tests

```bash
phpunit
```

Verify: All existing tests pass. `tests/FileExistenceTest.php` may need updating if it checks for specific files in `src/`.

## Examples

### Example: Creating a "List DNS Records" page

**User says:** "Add a page to list DNS records for a domain"

**Actions taken:**

1. Create `src/webuzo_list_dns.php`:
```php
<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $domain_name
 */
function webuzo_list_dns($host, $user, $pass, $domain_name)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $vps_id = $GLOBALS['tf']->variables->request['vps_id'] ?? '';
    $new = new Webuzo_API($user, $pass, $host);
    $result = $new->list_dns($domain_name);
    $response = (!empty($result)) ? myadmin_unstringify($result) : '';
    add_output('<h2>DNS Records</h2>');
    if (!empty($response['records'])) {
        $table = '<table class="sai_divroundshad" cellpadding="12px;" border="0">
            <tr>
                <th style="text-align: left;">Type</th>
                <th>Name</th>
                <th>Value</th>
            </tr>';
        foreach ($response['records'] as $record) {
            $table .= '<tr>';
            $table .= '<td>'.$record['type'].'</td>';
            $table .= '<td>'.$record['name'].'</td>';
            $table .= '<td>'.$record['value'].'</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';
        add_output($table);
    } else {
        add_output('No DNS records found.');
    }
    add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_domains&vps_id='.$vps_id.'">Back to Domains</a>');
}
```

2. Register in `src/Plugin.php` `getRequirements()`:
```php
$loader->add_page_requirement('webuzo_list_dns', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_dns.php');
```

3. Run `phpunit` to verify no regressions.

**Result:** New page accessible at `iframe.php?choice=none.webuzo_scripts&action=webuzo_list_dns&script_id={domain}&vps_id={id}`

## Common Issues

**"Call to undefined function webuzo_{name}"**
1. Verify the function name in the file matches what's registered in `src/Plugin.php` `getRequirements()`.
2. Verify the path in `add_page_requirement()` starts with `'/../vendor/detain/myadmin-webuzo-vps/src/'`.
3. Check that the filename matches the function name.

**"CSRF validation failed" / form submission silently shows the form again**
1. Verify `$tableObj->csrf('unique_name')` is called when building the form.
2. Verify `verify_csrf_referrer(__LINE__, __FILE__)` is in the submission condition.
3. Ensure the CSRF name in `csrf()` is unique across all forms in the project.

**"Class 'Webuzo_API' not found"**
1. Verify `include_once __DIR__.'/webuzo_sdk.php';` is at the top of the function body (inside the function, not outside).

**"Call to undefined function webuzo_api_call()"**
1. Add `function_requirements('webuzo_api_call');` before calling the helper. This lazy-loads from `src/webuzo.functions.inc.php`.

**Empty API response / connection timeout**
1. The Webuzo API runs on port 2002. Verify the VPS IP is correct via `$service['vps_ip']`.
2. Check that credentials were retrieved from `history_log` — if `$pass` is empty, the query returned no rows.

**Back link doesn't work / 404**
1. Back links must use `iframe.php` (not `index.php`) for pages rendered in the service frame.
2. The `choice` parameter must be `none.webuzo_scripts` for sub-action pages routed through the dispatcher.
3. The `action` parameter must match an existing registered function name.

**Page not appearing in navigation**
1. This plugin does not auto-generate menu entries. Menu links must be added manually in the calling page's HTML output or in a parent page's navigation section.