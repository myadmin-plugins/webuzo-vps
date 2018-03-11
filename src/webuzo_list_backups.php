<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 */
function webuzo_list_backups($host, $user, $pass) {
	include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$act = 'backups';
	function_requirements('webuzo_api_call');
	function_requirements('webuzo_format_units_size');
	function_requirements('webuzo_get_all_scripts');
	$response = webuzo_api_call($host, $user, $pass, $act);
	$response = myadmin_unstringify($response);
	add_output('<h2>Backups</h2>');
	if (!empty($response['backups'])) {
		$table = '<table class="sai_divroundshad" cellpadding="12px;" border="0">
					<tr>
						<th style="text-align: left;">Backup</th>
						<th>File Name</th>
						<th>Size</th>
						<th>Version</th>
						<th>Options</th>
					</tr>';
		$all_softs = webuzo_get_all_scripts($user, $pass, $host);
		foreach ($response['backups'] as $softid => $details) {
			foreach ($details as $install_id => $backups) {
				$table .= '<tr><td class="sai_heading_full" colspan="5"><span style="float:left;">'.$all_softs[$softid]['name'].'</span></td></tr>';
				foreach ($backups as $key => $backup) {
					$table .= '<tr><td>';
					$table .= ($key == 0) ? '<a href="'.$backup['softurl'].'">'.$backup['softurl'].'</a>' : '&nbsp;';
					$table .= '</td><td>'.$backup['name'].'</td>
						<td>'.webuzo_format_units_size($backup['size']).'</td>
						<td>'.$backup['ver'].'</td>
						<td>
							<a title="Download Backup" href="index.php?choice=none.webuzo_scripts&action=webuzo_download_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Download</a>
							<a target="SERVICEFrame1" title="Restore Backup" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_restore_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Restore</a>
							<a onclick="return confirm(\'Are you sure want to delete '.$backup['name'].' ?\');" target="SERVICEFrame1" title="Delete Backup" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_remove_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Delete</a>
						</td>
					</tr>';
				}
			}
		}

		$table .= '</table>';
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
			}
			.sai_heading_full {
				background: none repeat scroll 0 0 #eaebec;
				color: #333333;
				font-weight: bold;
				padding-top: 5px;
				padding-bottom: 5px;
			}
			</style>');
	} else {
		add_output('No backups available.');
	}
}
