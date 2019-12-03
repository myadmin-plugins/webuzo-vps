<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $domain_name
 */
function webuzo_remove_domain($host, $user, $pass, $domain_name)
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	add_output('<h2>Delete Domain</h2>');

	$new = new Webuzo_API($user, $pass, $host);
	$res = $new->delete_domain($domain_name);
	$response = myadmin_unstringify($res);

	if (!empty($response['done'])) {
		add_output('Domain Deleted  successfully');
	} else {
		add_output('Error can\'t delete Domain.');
	}
	add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_domains&vps_id='.$vps_id.'">Back to Domains</a>');
}
