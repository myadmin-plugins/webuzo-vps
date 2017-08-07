<?php

/**
 * @param int $length
 * @return string
 */
function webuzo_randomPassword($length = 8) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = []; //remember to declare $pass as an array
		$alphaLength = mb_strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = mt_rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_remove_script($host, $user, $pass, $script_id) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
		$response = api_call($host, $user, $pass, $act, $last_params, $post);
		$response = myadmin_unstringify($response);
		add_output('<h2>Remove Software</h2>');
		if(!empty($response['done'])) {
			add_output('Software removed successfully!');
		} else {
			foreach($response['error'] as $error_details) {
					$final_error .= $error_details.'<br />';
				}
				add_output('Error occurred! Please try again later!<br />Error Details: '.$final_error);
		}
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_installed_scripts&vps_id='.$vps_id.'">Back</a>');
	}

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_view_script($host, $user, $pass, $script_id) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$response1 = get_all_scripts( $user, $pass, $host);
		$script_details = $response1[$script_id];
		if($script_details['type'] === 'js') $act = 'js'; elseif ($script_details['type'] === 'perl') $act = 'perl'; else $act = 'software';
		$act = "&act=$act&soft=$script_id";
		$response = api_call($host, $user, $pass, $act);
		$response = myadmin_unstringify($response);

		$table = '<table width="700px;" cellspacing="1" cellpadding="4" border="0">
		<tbody><tr>
			<td width="10%" align="center"><img src="https://images.softaculous.com/top15/48/'.$script_details['softname'].'.png"></td>
			<td width="90%" colspan="4" class="sai_process_heading">'.$script_details['name'].'</td>
		</tr>
		<tr>
			<td width="20%" valign="top" align="left">
				Version : <font size="2"><b>'.$script_details['ver'].'</b></font>
			</td>
			<td width="60%" align="left">
				Release Date : <font size="1"><b>'.$response['info']['release_date'].'</b></font>
			</td>
			<td width="10%">

			</td>
		</tr>
	</tbody></table>';
		$table .= '<div id="tabs">
	<ul>
		<li><a href="#tabs-2">Overview</a></li>
		<li><a href="#tabs-3">Features</a></li>
		<li><a href="#tabs-1">Install</a></li>
		<li class="link"><a href="'.$response['info']['support'].'" target="_blank">Support</a></li>
	</ul>
	<div id="tabs-1">';
		$tableObj1 = new TFTable;
		$tableObj1->csrf('webuzo_install_software');
		$tableObj1->set_title('Install Software');
		$tableObj1->set_post_location('iframe.php');
		$tableObj1->set_options('cellpadding=10 width=500px;');
		$tableObj1->add_hidden('softsubmit', '1');
		$tableObj1->set_choice('none.webuzo_scripts');
		$tableObj1->add_hidden('vps_id', "$vps_id");
		$tableObj1->add_hidden('soft', "$script_id");
		$tableObj1->add_field('Choose Domain','l');
		$new = new Webuzo_API($user, $pass, $host);
		$result = $new->list_domains();
		$result = myadmin_unstringify($result);

		$select_domain = '<select name="softdomain" style="width:250px;">';
		foreach ($result['domains_list'] as $domain => $details) {
			$select_domain .= '<option value="'.$domain.'">'.$domain.'</option>';
		}
		$select_domain .= '</select>';
		$tableObj1->add_field($select_domain,'l');
		$tableObj1->add_row();

		$tableObj1->add_field('In Directory','l');
		$tableObj1->add_field($tableObj1->make_input('softdirectory', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->set_colspan(2);
		$tableObj1->add_field('The directory is relative to your domain and <span style="font-weight:bold;">should not exist</span>. e.g. To install at http://mydomain/dir/ just type <span style="font-weight:bold;">dir</span>. To install only in http://mydomain/ leave this empty.
','l');
		$tableObj1->add_row();

		$tableObj1->add_field('Site Name','l');
		$tableObj1->add_field($tableObj1->make_input('site_name', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->add_field('Site Description','l');
		$tableObj1->add_field($tableObj1->make_input('site_desc', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->add_field('Admin Username','l');
		$tableObj1->add_field($tableObj1->make_input('admin_username', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->add_field('Admin Password','l');
		$tableObj1->add_field($tableObj1->make_input('admin_pass', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->add_field('Admin Email','l');
		$tableObj1->add_field($tableObj1->make_input('admin_email', '', 25),'l');
		$tableObj1->add_row();

		$tableObj1->set_colspan(2);
		$tableObj1->add_field($tableObj1->make_submit('Install'));
		$tableObj1->add_row();
		$table .= $tableObj1->get_table();
	$table .= '
	</div>
	<div id="tabs-2">
	<table>
		<tr>
			<td><img width="500" alt="" src="https://images.softaculous.com/softimages/screenshots/'.$script_id.'_screenshot1.gif"></td>
			<td valign="top" style="padding:10px;">'.preg_replace("/<img[^>]+\>/i", ' ', $response['info']['overview']).'</td>
		</tr>
	</table>
	</div>
	<div id="tabs-3">
		<div style="text-align:left;">'.$response['info']['features'].'</div>
	</div>
	<div id="tabs-4">

	</div>
	<div id="tabs-5">';
	$table .= '</div>
</div>
<div id="tabid"></div>';

		add_output($table);
		add_output('
			<style type="text/css">
				span.stars, span.stars span {
				display: block;
				background: url(images/stars.png) 0 -16px repeat-x;
				width: 80px;
				height: 16px;
			}

			span.stars span {
				background-position: 0 0;
			}
			#tabs {
				width: 95%;
				margin-left: auto;
				margin-right: auto;
				margin-top: 10px;
			}
		</style>
		<script type="text/javascript">
			$(function() {
				$("span.stars").stars();
				$("#tabs").tabs({
					activate: function (event, ui) {
					var active = $("#tabs").tabs("option", "active");
					}
				});
				$("li.link a").unbind("click").each();
			});

			$.fn.stars = function() {
				return $(this).each(function() {
					$(this).html($("<span />").width(Math.max(0, (Math.min(5, parseFloat($(this).html())))) * 16));
				});
			}
		</script>
		'
		);
	}

/**
 * @param $host
 * @param $user
 * @param $pass
 */
function webuzo_list_installed_scripts($host, $user, $pass) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$act = 'installations';
		$response = api_call($host, $user, $pass, $act);
		$response = (!empty($response)) ? myadmin_unstringify($response) : '';
		add_output('<h2>Installed Softwares</h2>');
		if(!empty($response['installations'])) {
			$installations = $response['installations'];
			$softs = get_all_scripts($user, $pass, $host);
			if(!empty($response['installations'])) {
				$table = '<table class="sai_divroundshad" width="100%">
							<tr>
								<th style="padding-left: 10px; height: 50px;" align="left" width="40" align="left">Link</th>
								<th align="left" width="10">Admin</th>
								<th align="left" width="15">Installation Time</th>
								<th align="left" width="10">Version</th>
								<th align="left" width="25">Options</th>
							</tr>';
				foreach($installations as $soft_id => $installation) {
					$softw = $softs[$soft_id];
					$table .= "<tr><td style='padding-left:10px;' class='sai_heading_full' colspan='5' align='left'>{$softw['name']}</td></tr>";
					foreach($installation as $install_id => $details) {
						$adminurl = (!empty($details['adminurl'])) ? $details['adminurl'] : $details['softurl'];
						$time = date('d M Y, H:i:s',$details['itime']);
						$table .= "<tr>
								<td style='padding-left:10px;' width='40'><a href='{$details['softurl']}' target='_blank' title='".htmlentities($softw['desc'], ENT_QUOTES, 'UTF-8')."'>{$details['softurl']}</a></td>
								<td width='10'><a href='$adminurl' target='_blank' title='Admin link'>Admin Link</a></td>
								<td width='15'>$time</td>
								<td width='10'>{$details['ver']}</td>
							";
						$table .= "<td width='25' >";
						$table .= '<a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=add_backup&script_id='.$install_id.'&vps_id='.$vps_id.'" title="Back Up Software">Backup</a>';
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

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 */
function webuzo_import_script($host, $user, $pass, $script_id) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		if(isset($GLOBALS['tf']->variables->request['soft'])) {
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
			$response = api_call($host, $user, $pass, $act, $last_params, $post);
			$response = (!empty($response)) ? myadmin_unstringify($response) : '';
			if(!empty($response['done'])) {
				add_output('Script imported successfully');
			} else {
				add_output('Error can\'t import script. <br />Error details:<br />');
				$error_details = null;
				foreach($response['error'] as $error_code => $details) {
					$error_details .= $details.'<br />';
				}
				add_output($error_details);
			}

		} else {
			add_output('Oops! something went wrong!');
		}
		add_output('<br /><br /><br /><br /><a href="index.php?choice=none.webuzo_scripts&action=webuzo_view_script&script_id='.$script_id.'&vps_id='.$vps_id.'">Back</a>');
	}

/**
 * @param $host
 * @param $user
 * @param $pass
 */
function webuzo_list_backups($host, $user, $pass) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$act = 'backups';
		$response = api_call($host, $user, $pass, $act);
		$response = myadmin_unstringify($response);
		add_output('<h2>Backups</h2>');
		if(!empty($response['backups'])) {
			$table = '<table class="sai_divroundshad" cellpadding="12px;" border="0">
						<tr>
							<th align="left">Backup</th>
							<th>File Name</th>
							<th>Size</th>
							<th>Version</th>
							<th>Options</th>
						</tr>';
			$all_softs = get_all_scripts($user, $pass, $host);
			foreach ($response['backups'] as $softid => $details) {
				foreach ($details as $install_id => $backups) {
					$table .= '<tr><td class="sai_heading_full" colspan="5"><span style="float:left;">'.$all_softs[$softid]['name'].'</span></td></tr>';
					foreach ($backups as $key => $backup) {
						$table .= '<tr><td>';
						$table .= ($key == 0) ? '<a href="'.$backup['softurl'].'">'.$backup['softurl'].'</a>' : '&nbsp;';
						$table .= '</td><td>'.$backup['name'].'</td>
							<td>'.formatSizeUnits($backup['size']).'</td>
							<td>'.$backup['ver'].'</td>
							<td>
								<a title="Download Backup" href="index.php?choice=none.webuzo_scripts&action=download_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Download</a>
								<a target="SERVICEFrame1" title="Restore Backup" href="iframe.php?choice=none.webuzo_scripts&action=restore_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Restore</a>
								<a onclick="return confirm(\'Are you sure want to delete '.$backup['name'].' ?\');" target="SERVICEFrame1" title="Delete Backup" href="iframe.php?choice=none.webuzo_scripts&action=remove_backup&script_id='.$backup['name'].'&vps_id='.$vps_id.'">Delete</a>
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

/**
 * @param $user
 * @param $pass
 * @param $host
 * @return mixed
 */
function get_all_scripts($user, $pass, $host) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
function add_backup($host, $user, $pass, $install_id) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
		$response = api_call($host, $user, $pass, $act, $last_params,$post);
		$response = myadmin_unstringify($response);
		if(!empty($response['done'])) {
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
function download_backup($host, $user, $pass, $back_up_name) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$new = new Webuzo_API($user, $pass, $host);
		$new->download_backup($back_up_name,'/home/my/public_html/webuzo_file_downloads/');
		$file = "/home/my/public_html/webuzo_file_downloads/$back_up_name";
		if(file_exists($file)) {
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
function remove_backup($host, $user, $pass, $back_up_name) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
		$response = api_call($host, $user, $pass, $act, $last_params, $post);
		$response = myadmin_unstringify($response);
		if(!empty($response['done'])) {
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
function restore_backup($host, $user, $pass, $back_up_name) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
		$response = api_call($host, $user, $pass, $act, $last_params, $post);
		$response = (!empty($response)) ? myadmin_unstringify($response) : '';
		if(!empty($response['done'])) {
			add_output('Restored backup successfully');
		} else {
			add_output('Error can\'t restore backup.');
		}
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_backups&vps_id='.$vps_id.'">Back to Backups</a>');
	}

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 */
function webuzo_list_domains($host = null, $user = null, $pass = null) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$new = new Webuzo_API($user, $pass, $host);
		$result = $new->list_domains();
		$response = (!empty($result)) ? myadmin_unstringify($result) : '';
		add_output('<h2 style="display:inline-block;width: 85%;">Domains</h2>
			<a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_add_domain&vps_id='.$vps_id.'" style="display:inline-block;text-decoration: underline;">Add Domain</a>');
		if(!empty($response['domains_list'])) {
			$table = '<table class="sai_divroundshad" cellpadding="26px;" border="0">
						<tr>
							<th align="left">Domain</th>
							<th>Path</th>
							<th>Type</th>
							<th>IP Address</th>
							<th>Options</th>
						</tr>';
			foreach ($response['domains_list'] as $domain => $details) {
				$table .= '<tr><td><a target="__blank" href="http://'.$domain.'">'.$domain.'</a></td>';
				$table .= '<td>'.$details['path'].'</td>';
				if(isset($details['addon']) && $details['addon'] == 1) {
					$type_string = 'Addon';
				} elseif(isset($details['addon']) && $details['addon'] == 0) {
					$type_string = 'Parked';
				} elseif($response['primary_domain'] == $domain) {
					$type_string = 'Primary';
				}
				$table .= '<td>'.$type_string.'</td>';
				$table .= (isset($details['ip']) && !empty($details['ip'])) ? '<td>'.$details['ip'].'</td>' : '<td>-</td>'.'</td>';
				$table .= ($type_string != 'Primary') ?
					'<td>
						<a onclick="return confirm(\'Are you sure want to delete '.$domain.' ?\');" target="SERVICEFrame1" title="Delete" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_remove_domain&script_id='.$domain.'&vps_id='.$vps_id.'">Delete</a>

					</td>' : '<td>-</td>';
				$table .= '</tr>';
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
			add_output('No primary & other domains available!');
		}
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
function api_call($host, $user, $pass, $act, $last_params = null, $post = []) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
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
		if(!empty($post)) {
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
function formatSizeUnits($bytes) {
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

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $domain_name
 */
function webuzo_remove_domain($host, $user, $pass, $domain_name) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		add_output('<h2>Delete Domain</h2>');

		$new = new Webuzo_API($user,$pass,$host);
		$res= $new->delete_domain($domain_name);
		$response = myadmin_unstringify($res);

		if(!empty($response['done'])) {
			add_output('Domain Deleted  successfully');
		} else {
			add_output('Error can\'t delete Domain.');
		}
		add_output('<br /><br /><br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_list_domains&vps_id='.$vps_id.'">Back to Domains</a>');
	}

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 * @param null $app_id
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_view_sysapps($host = null, $user = null, $pass = null, $app_id = null) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		add_output('<h2>Application Details</h2>');
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$new = new Webuzo_API($user,$pass,$host);
		$res= $new->list_apps();
		$response = $new->apps;
		$response_installed_app = $new->list_installed_apps();
		$script_details = $response[$app_id];
		$table = '<table width="700px;" cellspacing="1" cellpadding="4" border="0">
		<tbody><tr>
			<td width="10%" align="center"><img alt="" style="width:100px;height:100px;" src="https://images.softaculous.com/webuzo/softimages/'.$app_id.'__'.$script_details['logo'].'"/></td>
			<td width="90%" colspan="4" class="sai_process_heading">'.$script_details['name'].'</td>
		</tr>
		<tr>
			<td width="20%" valign="top" align="left">
				Version : <font size="2"><b>'.$script_details['version'].'</b></font>
			</td>
			<td width="60%" align="left">
				Release Date : <font size="1"><b>'.$script_details['release_date'].'</b></font>
			</td>
			<td width="10%">

			</td>
		</tr>
	</tbody></table>';
		$table .= '<div id="tabs">
	<ul>
		<li><a href="#tabs-2">Overview</a></li>
		<li><a href="#tabs-1">Install / Remove</a></li>
		<li class="link"><a href="'.$script_details['support'].'" target="_blank">Support</a></li>
	</ul>
	<div id="tabs-1">';
		$tableObj1 = new TFTable;
		$tableObj1->csrf('webuzo_install_sys_software');
		$tableObj1->set_post_location('iframe.php');
		$tableObj1->set_options('cellpadding=10 width=320px;');
		$tableObj1->set_choice('none.webuzo_install_sysapp');
		$tableObj1->add_hidden('vps_id', "$vps_id");
		$tableObj1->add_hidden('soft', "$app_id");
		//$tableObj1->add_hidden('host', "$host");
		//$tableObj1->add_hidden('user', "$user");
		//$tableObj1->add_hidden('pass', "$pass");
		$tableObj1->set_colspan(2);
		if(empty($response_installed_app[$app_id])){
			$title_msg = 'Install Software';
			$confirm_msg = 'Further no confirmation will be asked. Are you sure want to install ?';

		} else {
			$title_msg = 'Software Already Installed';
			$confirm_msg = 'Further no confirmation will be asked. Are you sure want to remove ?';
		}
		$tableObj1->set_title($title_msg);
		$tableObj1->add_field($confirm_msg,'l');
		$tableObj1->add_row();


		$tableObj1->set_colspan(2);
		if(empty($response_installed_app[$app_id]))
			$tableObj1->add_field($tableObj1->make_submit('Install'));
		else
			$tableObj1->add_field($tableObj1->make_submit('Remove'));

		$tableObj1->add_row();
		$table .= $tableObj1->get_table();
	$table .= '
	</div>
	<div id="tabs-2">
	<table>
		<tr>
			<td></td>
			<td valign="top" style="padding:10px;">'.preg_replace("/<img[^>]+\>/i", ' ', $script_details['overview']).'</td>
		</tr>
	</table>
	</div>';
	$table .= '</div>
<div id="tabid"></div>';

		add_output($table);
		add_output('
		<script type="text/javascript">
			$(function() {
				$("#tabs").tabs({
					activate: function (event, ui) {
					var active = $("#tabs").tabs("option", "active");
					}
				});
			$("li.link a").unbind("click").each();
			});
		</script>
		'
		);

	}

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

/**
 * @param null $host
 * @return mixed
 */
function webuzo_update_logo($host = null) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$url = "http://$host:2004/install.php?preparelogo=https://my.interserver.net/templates/my/logo.png&sitename=Bread Basket";
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
		if(!empty($post)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		// Get response from the server.
		$resp = curl_exec($ch);
		return $resp;
	}

