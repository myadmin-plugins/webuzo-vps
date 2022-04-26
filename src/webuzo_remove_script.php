<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_remove_script($host, $user, $pass, $script_id)
{
	include_once __DIR__.'/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'remove';
	$last_params = "&insid=$script_id";
	$post = [
		'removeins' => '1',
		'remove_dir' => '1', // Pass this if you want the directory to be removed
		'remove_datadir' => '1', // Pass this if you want the data directory to be removed
		'remove_db' => '1', // Pass this if you want the database to be removed
		'remove_dbuser' => '1' // Pass this if you want the database user to be removed
	];
	function_requirements('webuzo_api_call');
	$response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
	$response = myadmin_unstringify($response);
	add_output('<h2>Remove Software</h2>');
	if (!empty($response['done'])) {
		add_output('Software removed successfully!');
	} else {
		foreach ($response['error'] as $error_details) {
			$final_error .= $error_details.'<br />';
		}
		add_output('Error occurred! Please try again later!<br />Error Details: '.$final_error);
	}
	add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_installed_scripts&vps_id='.$vps_id.'">Back</a>');
}
