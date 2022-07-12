<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $installation_id
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_edit_installation($host, $user, $pass, $installation_id)
{
	include_once __DIR__.'/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] :'';
	if (isset($GLOBALS['tf']->variables->request['installation_id'])) {
		$installation_id = $GLOBALS['tf']->variables->request['installation_id'];
	}
	$act = 'editdetail';
	$last_params = "&insid=$installation_id";
	function_requirements('webuzo_api_call');
	$response = webuzo_api_call($host, $user, $pass, $act, $last_params);
	$response = myadmin_unstringify($response);
	add_output('<h2>Edit Installation Details</h2>');
	if (isset($GLOBALS['tf']->variables->request['editins']) && verify_csrf_referrer(__LINE__, __FILE__)) {
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
		$post = [
			'editins'     => $GLOBALS['tf']->variables->request['editins'],
			'edit_dir'    => $GLOBALS['tf']->variables->request['edit_dir'], // Must be the path to installation
			'edit_url'    => $GLOBALS['tf']->variables->request['edit_url'], // Must be the URL to installation
			'edit_dbname' => $GLOBALS['tf']->variables->request['edit_dbname'],
			'edit_dbuser' => $GLOBALS['tf']->variables->request['edit_dbuser'],
			'edit_dbpass' => $GLOBALS['tf']->variables->request['edit_dbpass'],
			'edit_dbhost' => $GLOBALS['tf']->variables->request['edit_dbhost']
		];

		$response_update = webuzo_api_call($host, $user, $pass, $act, $last_params, $post);
		$response_update = myadmin_unstringify($response_update);
		if (isset($response_update['done'])) {
			add_output('Details has been updated successfully!');
		} else {
			foreach ($response_update['error'] as $error_details) {
				$final_error .= $error_details.'<br />';
			}
			add_output('Error occurred! Please try again later!<br />Error Details: '.$final_error);
		}
		add_output('<br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_installed_scripts&vps_id='.$vps_id.'">Back</a>');
	} else {
		$table = new TFTable;
		$table->set_post_location('iframe.php');
		$table->set_title('Installation Details');
		$table->csrf('webuzo_edit_install');
		$table->set_options('cellpadding=10');
		$table->add_hidden('editins', '1');
		$table->set_choice('none.webuzo_edit_installation');
		$table->add_hidden('installation_id', "$installation_id");
		$table->add_hidden('vps_id', "$vps_id");
		$table->add_field('Directory', 'l');
		$table->add_field($table->make_input('edit_dir', $response['userins']['softpath'], 65), 'l');
		$table->add_row();
		$table->add_field('URL', 'l');
		$table->add_field($table->make_input('edit_url', $response['userins']['softurl'], 65), 'l');
		$table->add_row();
		$table->add_field('Database Name', 'l');
		$table->add_field($table->make_input('edit_dbname', $response['userins']['softdb'], 65), 'l');
		$table->add_row();
		$table->add_field('Database User', 'l');
		$table->add_field($table->make_input('edit_dbuser', $response['userins']['softdbuser'], 65), 'l');
		$table->add_row();
		$table->add_field('Database Password', 'l');
		$table->add_field($table->make_input('edit_dbpass', $response['userins']['softdbpass'], 65), 'l');
		$table->add_row();
		$table->add_field('Database Host', 'l');
		$table->add_field($table->make_input('edit_dbhost', $response['userins']['softdbhost'], 65), 'l');
		$table->add_row();
		$table->set_colspan(2);
		$table->add_field($table->make_submit('Save Installation Details'));
		$table->add_row();
		add_output($table->get_table());
	}
}
