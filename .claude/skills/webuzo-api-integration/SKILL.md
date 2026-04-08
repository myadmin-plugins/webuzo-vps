---
name: webuzo-api-integration
description: Adds new Webuzo/Softaculous API integration using `webuzo_api_call()` or `Webuzo_API` class methods. Handles curl calls, `myadmin_unstringify()` response parsing, error checking, and `myadmin_log()`. Use when user says 'add API call', 'call webuzo', 'integrate softaculous', or works with `src/webuzo.functions.inc.php` or `src/webuzo_sdk.php`. Do NOT use for UI/page functions.
---
# Webuzo API Integration

## Critical

- **Two API approaches exist — pick the right one:**
  - `webuzo_api_call()` — low-level procedural curl to `http://$user:$pass@$host:2002/index.php`. Use for simple one-off actions (backup, remove, restore). Defined in `src/webuzo.functions.inc.php`.
  - `Webuzo_API` class — OOP wrapper extending `Softaculous_API` → `Softaculous_SDK`. Connects on port `2003` via HTTPS. Use for multi-step operations or when an SDK method already exists (domains, apps, passwords, FTP, certificates). Defined in `src/webuzo_sdk.php`.
- **Always deserialize responses with `myadmin_unstringify()`** — the API returns PHP-serialized strings. Never use `unserialize()` directly in page functions.
- **Always include the SDK before use:** `include_once __DIR__.'/webuzo_sdk.php';` at the top of every function that touches the API.
- **Never hardcode credentials.** Credentials come from `get_service($id, 'vps')` + `history_log` DB lookup for the Webuzo password, or are passed as function parameters (`$host`, `$user`, `$pass`).
- **Log every API interaction** with `myadmin_log('vps', $level, $message, __LINE__, __FILE__)` — at minimum log the call being made and whether it succeeded or failed.

## Instructions

### Approach A: Using `webuzo_api_call()` (procedural)

Use this when you need a raw API call with a specific `act` parameter and optional POST data.

1. **Include the SDK and get request context.**
   ```php
   include_once __DIR__.'/webuzo_sdk.php';
   $vps_id = $GLOBALS['tf']->variables->request['vps_id'] ?? '';
   ```
   Verify: `webuzo_sdk.php` is included before any API class or function use.

2. **Define the action, URL parameters, and POST data.**
   ```php
   $act = 'remove';                          // the Webuzo action name
   $last_params = "&insid=$script_id";        // additional URL query params
   $post = [
       'removeins' => '1',
       'remove_dir' => '1',
       'remove_datadir' => '1',
       'remove_db' => '1',
       'remove_dbuser' => '1',
   ];
   ```
   The `$act` maps to Webuzo's `act=` URL parameter. Common values: `backup`, `backups`, `remove`, `restore`, `services`, `domainmanage`, `domainadd`.
   Verify: The `$act` value matches a known Webuzo API endpoint.

3. **Make the API call and parse the response.**
   ```php
   function_requirements('webuzo_api_call');
   $response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
   $response = myadmin_unstringify($response);
   ```
   `webuzo_api_call()` signature: `($host, $user, $pass, $act, $last_params = null, $post = [])`
   It builds: `http://$user:$pass@$host:2002/index.php?&api=serialize&act=$act$last_params`
   Verify: Response is passed through `myadmin_unstringify()` before checking keys.

4. **Check success/failure using the `done` key.**
   ```php
   if (!empty($response['done'])) {
       // success path
       myadmin_log('vps', 'info', 'Operation completed successfully', __LINE__, __FILE__);
   } else {
       // error path — errors in $response['error'] (may be array)
       myadmin_log('vps', 'error', 'Operation failed: ' . json_encode($response), __LINE__, __FILE__);
       if (!empty($response['error'])) {
           // $response['error'] can be an array of error strings
           foreach ((array)$response['error'] as $error_detail) {
               // handle each error
           }
       }
   }
   ```
   Verify: Both success and failure branches include `myadmin_log()` calls.

### Approach B: Using `Webuzo_API` class (OOP)

Use this when an SDK method already exists or for multi-step operations.

1. **Include the SDK and instantiate.**
   ```php
   include_once __DIR__.'/webuzo_sdk.php';
   $new = new Webuzo_API($user, $pass, $host);
   ```
   Constructor sets: `$this->login = 'https://'.$user.':'.$pass.'@'.$host.':2003/index.php';`
   Verify: Credentials are valid strings, not null.

2. **Call the appropriate SDK method.**
   Available `Webuzo_API` methods (defined in `src/webuzo_sdk.php`):
   - `list_domains()` — returns serialized domain list
   - `add_domain($domain, $domainpath, $ip)` — adds domain
   - `delete_domain($domain)` — removes domain
   - `install_app($appid)` — installs system app by ID
   - `remove_app($appid)` — removes system app
   - `list_apps()` — populates `$this->apps`
   - `list_installed_apps()` — populates `$this->installed_apps`
   - `list_services()` — lists running services
   - `change_password($pass, $user)` — change root/enduser password
   - `add_ftpuser($user, $pass, $directory, $quota_limit)` — add FTP user
   - `webuzo_configure($ip, $user, $email, $pass, $host, $ns1, $ns2, $license, $data)` — initial setup

   Inherited from `Softaculous_SDK` (in `src/sdk.php`):
   - `install($sid, $data, $autoinstall)` — install script
   - `remove($insid, $data)` — remove script
   - `backup($insid, $data)` — backup installation
   - `restore($name, $data)` — restore backup
   - `list_backups()` — list backups
   - `list_installed_scripts()` — populates `$this->iscripts`
   - `installations($showupdates)` — list all installations

   ```php
   $res = $new->add_domain($domain, $domain_path);
   ```
   Verify: The method exists in `src/webuzo_sdk.php` or `src/sdk.php`.

3. **Parse the response.**
   SDK methods return raw serialized strings. Always deserialize:
   ```php
   $res = myadmin_unstringify($res);
   ```
   For methods that may return empty/false, guard first:
   ```php
   $response = (!empty($result)) ? myadmin_unstringify($result) : '';
   ```
   Verify: Never access array keys on the raw string response.

4. **Handle done/error and log.**
   ```php
   if (!empty($res['done'])) {
       myadmin_log('vps', 'info', "Domain added for {$host}", __LINE__, __FILE__);
   } else {
       myadmin_log('vps', 'error', 'Domain add failed: ' . json_encode($res), __LINE__, __FILE__);
       if (!empty($res['error'])) {
           // handle error display
       }
   }
   ```

### Adding a new SDK method to `Webuzo_API`

If no existing method covers your use case:

1. **Add the method to `src/webuzo_sdk.php`** in the `Webuzo_API` class, following the existing pattern:
   ```php
   /**
    * Description
    *
    * @category	 Category
    * @param     type $param Description
    * @return    string $resp Response of Action. Default: Serialize
    */
   public function new_method($param)
   {
       $act = 'act=actionname';

       $data['field'] = $param;
       $data['submitfield'] = 1;

       $resp = $this->curl_call($act, $data);
       $this->chk_error();
       return $resp;
   }
   ```
   Verify: Method calls `$this->curl_call($act, $data)` and `$this->chk_error()`. Return the raw response.

2. **Do NOT deserialize inside SDK methods.** The caller handles `myadmin_unstringify()`. Exception: `list_installed_apps()` and `list_apps()` call `curl_unserialize()` internally because they store results in class properties.

### Adding a new helper function to `webuzo.functions.inc.php`

1. **Follow the existing function signature pattern:**
   ```php
   /**
    * @param $host
    * @param $user
    * @param $pass
    * @param $specific_param
    */
   function webuzo_new_action($host, $user, $pass, $specific_param)
   {
       include_once __DIR__.'/webuzo_sdk.php';
       $vps_id = $GLOBALS['tf']->variables->request['vps_id'] ?? '';
       $act = 'actionname';
       $last_params = "&paramname=$specific_param";
       $post = [
           'field' => '1',
       ];
       $response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
       $response = myadmin_unstringify($response);
       if (!empty($response['done'])) {
           // success
       } else {
           // error
       }
   }
   ```

2. **Register the function in `src/Plugin.php`** `getRequirements()` method:
   ```php
   $loader->add_page_requirement('webuzo_new_action', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
   ```
   Verify: The function name in `add_page_requirement()` matches the actual function name exactly.

## Examples

### Example 1: Add a new "list services" helper using `Webuzo_API`

**User says:** "Add a function to list Webuzo services for a VPS"

**Actions taken:**
1. Check `src/webuzo_sdk.php` — `list_services()` method already exists, returns serialized response.
2. Add helper function in `src/webuzo.functions.inc.php`:

```php
/**
 * @param $user
 * @param $pass
 * @param $host
 * @return mixed
 */
function webuzo_list_services($user, $pass, $host)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $new = new Webuzo_API($user, $pass, $host);
    $result = $new->list_services();
    $response = (!empty($result)) ? myadmin_unstringify($result) : '';
    myadmin_log('vps', 'info', 'Listed Webuzo services for ' . $host, __LINE__, __FILE__);
    return $response;
}
```

3. Register in `src/Plugin.php` `getRequirements()`:
```php
$loader->add_page_requirement('webuzo_list_services', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
```

**Result:** New function available via `function_requirements('webuzo_list_services')`, returns deserialized service data.

### Example 2: Add a raw API call for a custom action

**User says:** "Add a function to restart a Webuzo service by name"

**Actions taken:**
1. No existing SDK method for service restart. Use `webuzo_api_call()` directly.
2. Add to `src/webuzo.functions.inc.php`:

```php
/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $service_name
 */
function webuzo_restart_service($host, $user, $pass, $service_name)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $act = 'services';
    $last_params = "&restart=$service_name";
    $post = [];
    myadmin_log('vps', 'info', "Restarting Webuzo service $service_name on $host", __LINE__, __FILE__);
    $response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
    $response = myadmin_unstringify($response);
    if (!empty($response['done'])) {
        myadmin_log('vps', 'info', "Service $service_name restarted on $host", __LINE__, __FILE__);
    } else {
        myadmin_log('vps', 'error', "Failed to restart $service_name on $host: " . json_encode($response), __LINE__, __FILE__);
    }
    return $response;
}
```

3. Register in `src/Plugin.php`.

## Common Issues

### `myadmin_unstringify()` returns empty string or false
1. The API call may have failed silently. Check curl connectivity: `curl -v http://$host:2002/` from the server.
2. The response may be empty. Always guard: `$response = (!empty($result)) ? myadmin_unstringify($result) : '';`
3. Port mismatch: `webuzo_api_call()` uses port **2002** (HTTP). `Webuzo_API` class uses port **2003** (HTTPS). Using the wrong port returns no data.

### `Webuzo_API` constructor — class not found
- Missing include. Add `include_once __DIR__.'/webuzo_sdk.php';` at the top of your function. The SDK file itself includes `sdk.php`, so you only need to include `webuzo_sdk.php`.

### Response has no `done` key but no `error` key either
- Some list endpoints (like `list_domains()`) return data under different keys (e.g., `domains_list`, `primary_domain`). Check the Webuzo API docs or inspect `$response` keys with `myadmin_log('vps', 'debug', json_encode(array_keys($response)), __LINE__, __FILE__)`.
- The `done`/`error` pattern applies to **mutating** operations (add, remove, backup, restore, install). **Read** operations return data directly.

### `function_requirements('webuzo_api_call')` fails with "unknown function"
- The function is not registered in `src/Plugin.php` `getRequirements()`. Add the `$loader->add_page_requirement()` line pointing to the correct file.

### curl timeout or connection refused
1. Webuzo panel may not be running. Verify: `curl -sk https://$host:2003/` (for SDK class) or `curl -s http://$host:2002/` (for `webuzo_api_call`).
2. Firewall may block ports 2002/2003. Check `iptables -L -n | grep 200`.
3. `webuzo_api_call()` has a 1000-second timeout (`CURLOPT_CONNECTTIMEOUT` and `CURLOPT_TIMEOUT`). If the VPS is unreachable, the function blocks for a long time.

### App install via `install_app()` returns error "App Not Found"
- The `$appid` must match a key in `$this->apps`. Call `$new->list_apps()` first and inspect `$new->apps` to find valid IDs. Example IDs: `125` (Apache 2.4), `128` (MySQL 5.6), `124` (PHP 5.6).

### Credentials lookup pattern
When credentials aren't passed as parameters, retrieve them from the database:
```php
$service = get_service($vps_id, 'vps');
$db = get_module_db('vps');
$db->query("select * from history_log where history_owner = '{$service['vps_custid']}' and history_old_value = 'Webuzo Details' limit 1");
$user = 'admin';
$host = $service['vps_ip'];
if ($db->num_rows() > 0) {
    $db->next_record(MYSQL_ASSOC);
    $pass = $db->Record['history_new_value'];
}
```
The username is always `'admin'`. The password is stored in `history_log` with `history_old_value = 'Webuzo Details'`.