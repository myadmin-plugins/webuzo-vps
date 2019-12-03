<?php

/**
 * @param $user
 * @param $pass
 * @param $host
 * @return mixed
 */
function webuzo_get_all_scripts($user, $pass, $host)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$new = new Webuzo_API($user, $pass, $host);
	$new->list_installed_scripts();
	$softs = $new->iscripts;
	return $softs;
}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $install_id
 */
function webuzo_add_backup($host, $user, $pass, $install_id)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	add_output('<h2>Create Backup</h2>');
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'backup';
	$last_params = "&insid=$install_id";
	$post = [
		'backupins' => '1',
		'backup_dir' => '1',
		'backup_datadir' => '1',
		'backup_db' => '1'
	];
	$response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
	$response = myadmin_unstringify($response);
	if (!empty($response['done'])) {
		add_output('Software back up created successfully!');
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_backups&vps_id='.$vps_id.'">Backups</a>');
	} else {
		add_output('Oops! something went wrong!');
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&vps_id='.$vps_id.'">Back</a>');
	}
}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $back_up_name
 */
function webuzo_download_backup($host, $user, $pass, $back_up_name)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$new = new Webuzo_API($user, $pass, $host);
	$new->download_backup($back_up_name, '/home/my/public_html/webuzo_file_downloads/');
	$file = "/home/my/public_html/webuzo_file_downloads/$back_up_name";
	if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-gzip');
		header('Content-Length: '.filesize($file));
		header('Content-Disposition: attachment; filename='.basename($back_up_name));
		readfile($file);
		unlink($file);
	} else {
		add_output('Oops! something went wrong!');
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&vps_id='.$vps_id.'&action=ebuzo_list_backups">Back</a>');
	}
}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $back_up_name
 */
function webuzo_remove_backup($host, $user, $pass, $back_up_name)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'backups';
	$last_params = "&remove=$back_up_name";
	$post = [
		'restore_ins' => '1',
		'backup_dir' => '1', // Pass this if you want to backup the directory
		'backup_datadir' => '1', // Pass this if you want to backup the data directory
		'backup_db' => '1', // Pass this if you want to backup the database
	];
	add_output('<h2>Backups</h2>');
	$response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
	$response = myadmin_unstringify($response);
	if (!empty($response['done'])) {
		add_output('Deleted backup successfully');
	} else {
		add_output('Error can\'t delete backup.');
	}
	add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuuzo_list_backups&vps_id='.$vps_id.'">Back to Backups</a>');
}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $back_up_name
 */
function webuzo_restore_backup($host, $user, $pass, $back_up_name)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'restore';
	$last_params = "&restore=$back_up_name";
	$post = [
		'restore_ins' => '1',
		'restore_dir' => '1', // Pass this if you want to backup the directory
		'restore_datadir' => '1', // Pass this if you want to backup the data directory
		'restore_db ' => '1', // Pass this if you want to backup the database
	];
	add_output('<h2>Backups</h2>');
	$response = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
	$response = (!empty($response)) ? myadmin_unstringify($response) : '';
	if (!empty($response['done'])) {
		add_output('Restored backup successfully');
	} else {
		add_output('Error can\'t restore backup.');
	}
	add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_backups&vps_id='.$vps_id.'">Back to Backups</a>');
}


/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $act
 * @param null $last_params
 * @param array $post
 * @return mixed
 */
function webuzo_api_call($host, $user, $pass, $act, $last_params = null, $post = [])
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	// The URL
	$url = "http://$user:$pass@$host:2002/index.php?".
				'&api=serialize'.
				"&act=$act".
				"$last_params";
	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
	if (!empty($post)) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	// Get response from the server.
	$resp = curl_exec($ch);
	return $resp;
}

/**
 * @param $bytes
 * @return string
 */
function webuzo_format_units_size($bytes)
{
	if ($bytes >= 1073741824) {
		$bytes = number_format($bytes / 1073741824, 2).' GB';
	} elseif ($bytes >= 1048576) {
		$bytes = number_format($bytes / 1048576, 2).' MB';
	} elseif ($bytes >= 1024) {
		$bytes = number_format($bytes / 1024, 2).' KB';
	} elseif ($bytes > 1) {
		$bytes .= ' bytes';
	} elseif ($bytes == 1) {
		$bytes .= ' byte';
	} else {
		$bytes = '0 bytes';
	}
	return $bytes;
}
