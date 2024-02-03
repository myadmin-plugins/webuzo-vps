<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $app_id
 */
function webuzo_install_sysapp($host, $user, $pass, $app_id)
{
    include_once __DIR__.'/webuzo_sdk.php';
    if (isset($GLOBALS['tf']->variables->request['submitbutton']) || $app_id) {
        $vps_id = $GLOBALS['tf']->variables->request['vps_id'];
        if (!$app_id) {
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
        }
        add_output('<h2>Application Details</h2>');
        include_once __DIR__.'/webuzo_sdk.php';
        if (isset($app_id)) {
            $GLOBALS['tf']->variables->request['submitbutton'] = 'Remove';
        }
        $app_id ??= $GLOBALS['tf']->variables->request['soft'];
        $new = new Webuzo_API($user, $pass, $host);
        if ($GLOBALS['tf']->variables->request['submitbutton'] === 'Install') {
            $res = $new->install_app($app_id);
        } else {
            $res = $new->remove_app($app_id);
        }
        $result = myadmin_unstringify($res);
        if (($GLOBALS['tf']->variables->request['submitbutton'] === 'Install' && $result['done']) || ($GLOBALS['tf']->variables->request['submitbutton'] === 'Remove' && !isset($result['error']))) {
            $Outputstring = ($GLOBALS['tf']->variables->request['submitbutton'] === 'Install') ? 'Application is installed successfully!' : 'Application is removed successfully!';
            add_output($Outputstring);
        } else {
            add_output('Error occurred!');
            myadmin_log('vps', 'info', 'Webuzo error: While removing system app. Details: VPS - '.$vps_id.' Error - '.json_encode($result['error']), __LINE__, __FILE__);
        }
        add_output('<br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_installed_sysapps&vps_id='.$vps_id.'" title="Back">Back</a>');
    }
}
