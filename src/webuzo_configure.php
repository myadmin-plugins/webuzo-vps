<?php

/**
 * @param $id
 * @return false|null
 */
function webuzo_configure($id)
{
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	if (isset($GLOBALS['tf']->variables->request['vps_id'])) {
		$id = $GLOBALS['tf']->variables->request['vps_id'];
	}
	$service = get_service($id, 'vps');
	if (!$id) {
		myadmin_log('vps', 'info', 'VPS ID is not provided!', __LINE__, __FILE__);
		return false;
	}
	function_requirements('webuzo_update_logo');
	$logo_update_resp = webuzo_update_logo($service['vps_ip']);
	$msg = (!empty($logo_update_resp)) ? 'Logo and text change is completed successfully!' : 'Logo and text change is not completed failed!';
	myadmin_log('vps', 'info', $msg, __LINE__, __FILE__);
	$email = $GLOBALS['tf']->accounts->cross_reference($service['vps_custid']);
	$ns1 = 'cdns1.interserver.net';
	$ns2 = 'cdns2.interserver.net';

	//Webuzo license
	$license_key = null;
	$noc = new \Detain\MyAdminSoftaculous\SoftaculousNOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
	$license_details = $noc->webuzoLicenses('', $service['vps_ip']);
	if ($license_details['num_results'] > 0) {
		foreach ($license_details['licenses'] as $license_detail) {
			if ($service['vps_ip'] == $license_detail['ip']) {
				myadmin_log('vps', 'info', "Webuzo License found for {$service['vps_ip']} details as follows ".json_encode($license_detail), __LINE__, __FILE__);
				$license_key = $license_detail['license'];
			}
		}
	} else {
		myadmin_log('vps', 'info', "Webuzo License not found for $email for {$service['vps_ip']}", __LINE__, __FILE__);
	}

	$db = get_module_db('vps');
	$db->query("select * from history_log where history_owner = {$service['vps_custid']} and history_old_value = 'Webuzo Details' limit 1");
	$user = 'admin';
	function_requirements('webuzo_randomPassword');
	$pass = webuzo_randomPassword();

	$new = new Webuzo_API($user, $pass, $service['vps_ip']);
	$res = $new->webuzo_configure($service['vps_ip'], $user, $email, $pass, $service['vps_hostname'], $ns1, $ns2, $license_key);
	myadmin_log('vps', 'info', "webuzo_configure({$service['vps_ip']},  $user, $email, $pass, {$service['vps_hostname']}, $ns1, $ns2, $license_key)", __LINE__, __FILE__);
	$res = myadmin_unstringify($res);

	// Installing Apache , Mysql, PHP
	$install_lamp = array('125'=> 'Apache 2.4', '128' => 'Mysql 5.6', '124' => 'PHP 5.6');
	foreach ($install_lamp as $app_id => $desc) {
		try {
			$res_install_app = $new->install_app($app_id);
			myadmin_log('vps', 'info', "Webuzo - Installing $desc", __LINE__, __FILE__);
			myadmin_log('vps', 'debug', "Response: ".myadmin_unstringify($res_install_app), __LINE__, __FILE__);
		} catch (Exception $e) {
			myadmin_log('vps', 'error', "Error ocurred Installing $desc. Error message: ".$e->getMessage(), __LINE__, __FILE__);
		}
	}
		
	if (isset($res['done'])) {
		if ($db->num_rows() == 0) {
			$GLOBALS['tf']->history->add('vps', 'webuzo_pass', $pass, 'Webuzo Details');
			myadmin_log('vps', 'info', "Webuzo password added to history_log successfully! for $email for vps id {$service['vps_ip']}", __LINE__, __FILE__);
		} else {
			$data['history_new_value'] = $pass;
			$db->next_record(MYSQL_ASSOC);
			$history_id = $db->Record['history_id'];
			$GLOBALS['tf']->history->update($history_id, $data);
			myadmin_log('vps', 'info', "Webuzo password updated to history_log id - $history_id successfully! for $email for vps id {$service['vps_ip']}", __LINE__, __FILE__);
		}
		$url = 'http://my.interserver.net/index.php?choice=none.view_vps&id='.$id;
		$body = 'Welcome! '.PHP_EOL;
		$body .= 'Your VPS has been created successfully. Here are some details for you to get started.'.PHP_EOL;
		$body .= 'You can manage your VPS through '.$url . PHP_EOL;
		$body .= 'Bread Basket Control Panel '.PHP_EOL;
		$body .= 'Url: http://'.$service['vps_ip'].':2002/'.PHP_EOL;
		$body .= 'Username: admin'.PHP_EOL;
		$body .= 'Password: '.$pass . PHP_EOL;
		$body .= 'Documentation: https://www.interserver.net/tips/knb-category/breadbasket/'.PHP_EOL;
		$body .= 'Thank You, '.PHP_EOL;
		$body .= 'Regards, '.PHP_EOL;
		$body .= 'Interserver Team';
		$subject = 'InterServer Bread Basket Details';
		$headers = '';
		$headers .= 'MIME-Version: 1.0'.PHP_EOL;
		$headers .= 'Content-Type: text/plain; charset=UTF-8'.PHP_EOL;
		$headers .= 'From: admin@interserver.net'.PHP_EOL;
		$headers .= 'To: '.$email . PHP_EOL;
		mail($email, $subject, $body, $headers);
		myadmin_log('vps', 'info', "Webuzo configuration email has been sent to $email", __LINE__, __FILE__);
		myadmin_log('vps', 'info', "Webuzo configured successfully! for $email for vps id {$service['vps_ip']}", __LINE__, __FILE__);
	} else {
		myadmin_log('vps', 'info', "Error while configuring webuzo! for $email for vps id {$service['vps_ip']}", __LINE__, __FILE__);
		myadmin_log('vps', 'info', 'Error details : '.json_encode($res), __LINE__, __FILE__);
	}
}
