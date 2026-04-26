<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_import_script($host, $user, $pass, $script_id)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $vps_id = \MyAdmin\App::variables()->request['vps_id'] ?? '';
    if (isset(\MyAdmin\App::variables()->request['soft'])) {
        $host = \MyAdmin\App::variables()->request['host'] ?? '';
        $user = \MyAdmin\App::variables()->request['user'] ?? '';
        $pass = \MyAdmin\App::variables()->request['pass'] ?? '';
        $script_id = \MyAdmin\App::variables()->request['soft'];
        $act = 'import';
        $last_params = "&soft=$script_id";
        $post = [
            'softsubmit'    => \MyAdmin\App::variables()->request['softsubmit'],
            'softdomain'    =>\MyAdmin\App::variables()->request['softdomain'],
            'softdirectory' =>\MyAdmin\App::variables()->request['softdirectory']
        ];
        function_requirements('webuzo_api_call');
        $response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
        $response = (!empty($response)) ? myadmin_unstringify($response) : '';
        if (!empty($response['done'])) {
            add_output('Script imported successfully');
        } else {
            add_output('Error can\'t import script. <br />Error details:<br />');
            $error_details = null;
            foreach ($response['error'] as $error_code => $details) {
                $error_details .= $details.'<br />';
            }
            add_output($error_details);
        }
    } else {
        add_output('Oops! something went wrong!');
    }
    add_output('<br /><br /><br /><br /><a href="index.php?choice=none.webuzo_scripts&action=webuzo_view_script&script_id='.$script_id.'&vps_id='.$vps_id.'">Back</a>');
}
