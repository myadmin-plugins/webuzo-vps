<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_add_domain($host=null, $user=null, $pass=null)
{
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	add_output('<h2>Add Domain</h2>');
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	if (isset($GLOBALS['tf']->variables->request['domain']) && verify_csrf_referrer(__LINE__, __FILE__)) {
		$service = get_service($vps_id, 'vps');
		$db = get_module_db('vps');
		$query = "select * from history_log where history_owner = {$service['vps_custid']} and history_old_value = 'Webuzo Details'";
		$db->query($query);
		$user = 'admin';
		$host = $service['vps_ip'];
		while ($db->next_record(MYSQL_ASSOC)) {
			if (isset($db->Record['history_new_value'])) {
				$pass = $db->Record['history_new_value'];
			}
		}
		$domain = $GLOBALS['tf']->variables->request['domain'];
		$domain_path = $GLOBALS['tf']->variables->request['domain_path'];
		$new = new Webuzo_API($user, $pass, $host);
		$res = $new->add_domain($domain, $domain_path);
		$res = myadmin_unstringify($res);
		// Done/Error
		if (!empty($res['done'])) {
			add_output('Domain added successfully!');
		} else {
			add_output('Error in adding domain<br/>');
			if (!empty($res['error'])) {
				add_output('Error details: '.$res['error']);
			}
		}
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_domains&vps_id='.$vps_id.'">Back to Domains</a>');
	} else {
		$tableObj = new TFTable;
		$tableObj->set_options('cellpadding="10"');
		$tableObj->csrf('webuzo_add_domain');
		$tableObj->set_title('Add Domain');
		$tableObj->set_post_location('iframe.php');
		$tableObj->set_options('cellpadding=10');
		$tableObj->set_choice('none.webuzo_add_domain');
		$tableObj->add_hidden('vps_id', "$vps_id");

		$tableObj->add_field('Domain', 'l');
		$tableObj->add_field($tableObj->make_input('domain', '', '40', false, 'autocomplete ="off"'), 'l');
		$tableObj->add_row();
		$tableObj->add_field('Domain Path', 'l');
		$tableObj->add_field('<input value="/home/admin/" size="10" readonly/>'.$tableObj->make_input('domain_path', 'www/', '30'), 'l');
		$tableObj->add_row();
		$tableObj->set_colspan(2);
		$tableObj->add_field($tableObj->make_submit('Add Domain'));
		$tableObj->add_row();
		add_output($tableObj->get_table());
		add_output('<script type="text/javascript">
				$(function(){
					var first_val = $("input[name=domain_path]").val();
					$("input[name=domain]").keyup(function(){
						var domain = $(this).val();
						var finalvalue = first_val + domain;
						$("input[name=domain_path]").val(finalvalue);
					});
				});
				</script>');
	}
}
