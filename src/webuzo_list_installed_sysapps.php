<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 */
function webuzo_list_installed_sysapps($host = null, $user = null, $pass = null) {
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	add_output('<h2>System Applications</h2>');
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$new = new Webuzo_API($user, $pass, $host);
	$response_installed_app = $new->list_installed_apps();
	$res = $new->list_apps();
	$script_list = $new->apps;
	if(!empty($response_installed_app)) {
		$table = '';
		$table .= '<table cellpadding = "10" width="100%" class="table table-hover">';
		$table .= '<thead style="background-color: #333333; color: #fff;"><tr>
				<th style="text-align: left;">Path</th>
				<th style="text-align: left;">Installation Time</th>
				<th style="text-align: left;">Version</th>
				<th style="text-align: left;">Options</th>
			</tr></thead><tbody>';
		foreach ($response_installed_app as $app_id => $details) {
			$table .= '<tr>';
			$table .= "<th style='background-color: #EFEFEF;text-align: left;' colspan='5'>".$script_list[$app_id]['fullname'].'</th>';
			$table .= '</tr>';
			foreach ($details as $value) {
				$base_path = isset($value['path']['base']) ? $value['path']['base'] : '';
				$table .= '<tr>';
				$table .= "<td height='40'>".$base_path.'</td>';
				$table .= "<td height='40'>".date('F d, Y H:i',$value['itime']).'</td>';
				$table .= "<td height='40'>".$value['version'].'</td>';
				$table .= '<td height="40" width=20><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_install_sysapp&script_id='.$app_id.'&vps_id='.$vps_id.'" title="Remove" onclick="return confirm(\'Are you sure want to remove '.$script_list[$app_id]['fullname'].' ?\');">Remove</a></td>';
				$table .= '</tr>';
			}
		}
		$table .= '</tbody></table>';
		add_output($table);
	} else {
		add_output('Installation in progress...');
	}
}
