<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_import_script($host, $user, $pass, $script_id)
{
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	if (isset($GLOBALS['tf']->variables->request['soft'])) {
		$host = isset($GLOBALS['tf']->variables->request['host']) ? $GLOBALS['tf']->variables->request['host'] : '';
		$user = isset($GLOBALS['tf']->variables->request['user']) ? $GLOBALS['tf']->variables->request['user'] : '';
		$pass = isset($GLOBALS['tf']->variables->request['pass']) ? $GLOBALS['tf']->variables->request['pass'] : '';
		$script_id = $GLOBALS['tf']->variables->request['soft'];
		$act = 'import';
		$last_params = "&soft=$script_id";
		$post = [
			'softsubmit'    => $GLOBALS['tf']->variables->request['softsubmit'],
			'softdomain'    =>$GLOBALS['tf']->variables->request['softdomain'],
			'softdirectory' =>$GLOBALS['tf']->variables->request['softdirectory']
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
