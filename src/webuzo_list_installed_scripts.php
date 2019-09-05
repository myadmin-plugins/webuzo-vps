<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 */
function webuzo_list_installed_scripts($host, $user, $pass)
{
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'installations';
	function_requirements('webuzo_api_call');
	function_requirements('webuzo_get_all_scripts');
	$response = webuzo_api_call($host, $user, $pass, $act);
	$response = (!empty($response)) ? myadmin_unstringify($response) : '';
	add_output('<h2>Installed Softwares</h2>');
	if (!empty($response['installations'])) {
		$installations = $response['installations'];
		$softs = webuzo_get_all_scripts($user, $pass, $host);
		if (!empty($response['installations'])) {
			$table = '<table class="sai_divroundshad" width="100%">
						<tr>
							<th style="padding-left: 10px; height: 50px;" style="text-align: left;" width="40" style="text-align: left;">Link</th>
							<th style="text-align: left;" width="10">Admin</th>
							<th style="text-align: left;" width="15">Installation Time</th>
							<th style="text-align: left;" width="10">Version</th>
							<th style="text-align: left;" width="25">Options</th>
						</tr>';
			foreach ($installations as $soft_id => $installation) {
				$softw = $softs[$soft_id];
				$table .= "<tr><td style='padding-left:10px;' class='sai_heading_full' colspan='5' align='left'>{$softw['name']}</td></tr>";
				foreach ($installation as $install_id => $details) {
					$adminurl = (!empty($details['adminurl'])) ? $details['adminurl'] : $details['softurl'];
					$time = date('d M Y, H:i:s', $details['itime']);
					$table .= "<tr>
							<td style='padding-left:10px;' width='40'><a href='{$details['softurl']}' target='_blank' title='".htmlentities($softw['desc'], ENT_QUOTES, 'UTF-8')."'>{$details['softurl']}</a></td>
							<td width='10'><a href='{$adminurl}' target='_blank' title='Admin link'>Admin Link</a></td>
							<td width='15'>$time</td>
							<td width='10'>{$details['ver']}</td>
						";
					$table .= "<td width='25' >";
					$table .= '<a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_add_backup&script_id='.$install_id.'&vps_id='.$vps_id.'" title="Back Up Software">Backup</a>';
					$table .= '<a title="Remove Software" onclick="return confirm(\'Are you sure want to remove '.$softw['name'].' ?\');" target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_remove_script&script_id='.$install_id.'&vps_id='.$vps_id.'"> Remove </a>';
					$table .= "<a target='SERVICEFrame1' href='iframe.php?choice=none.webuzo_scripts&action=webuzo_edit_installation&script_id=".$install_id.'&vps_id='.$vps_id . "' title='Edit Installation'>Edit</a>";
					$table .= "</td></tr><tr><td colspan='5'>&nbsp;</td></tr>";
				}
			}
			$table .= '</table><br />';
		}
		add_output($table);
		add_output('<style type="text/css">
			.sai_divroundshad {
				background: none repeat scroll 0 0 #fff;
			}
			.sai_altrowstable, .sai_divroundshad {
				border: 1px solid #ccc;
				border-radius: 3px;
				box-shadow: 0 1px 2px #d1d1d1;
				color: #040c19;
				/*font-size: 12px;*/
			}
			.sai_heading_full {
				background: none repeat scroll 0 0 #eaebec;
				color: #333333;
				/*font-size: 13px;*/
				font-weight: bold;
				padding-top: 5px;
				padding-bottom: 5px;
			}
			</style>');
	} else {
		add_output('You have not installed any softwares yet!');
	}
}
