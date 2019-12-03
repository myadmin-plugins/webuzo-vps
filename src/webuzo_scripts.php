<?php


function webuzo_scripts()
{
	include_once __DIR__.'/../../../softaculous/webuzo_sdk/webuzo_sdk.php';
	$id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	$service = get_service($id, 'vps');
	$db = get_module_db('vps');
	$query = "select * from history_log where history_owner = {$service['vps_custid']} and history_old_value = 'Webuzo Details'";
	$db->query($query);
	$webuzo_user = 'admin';
	$host = $service['vps_ip'];
	$webuzo_password = '';

	while ($db->next_record(MYSQL_ASSOC)) {
		if (isset($db->Record['history_new_value'])) {
			$webuzo_password = $db->Record['history_new_value'];
		}
	}
	$new = new Webuzo_API($webuzo_user, $webuzo_password, $host);
	$new->list_installed_scripts();
	$softs = $new->iscripts;
	if (isset($GLOBALS['tf']->variables->request['softsubmit']) && !empty($GLOBALS['tf']->variables->request['softsubmit']) && verify_csrf_referrer(__LINE__, __FILE__)) {
		$script_id = $GLOBALS['tf']->variables->request['soft'];
		myadmin_log('vps', 'info', 'Webuzo script Installation initialising install script id-'.$script_id, __LINE__, __FILE__);


		$data['softdomain'] = isset($GLOBALS['tf']->variables->request['softdomain']) ? $GLOBALS['tf']->variables->request['softdomain'] : $host; // OPTIONAL - By Default the primary domain will be used
		$data['softdirectory'] = isset($GLOBALS['tf']->variables->request['softdirectory']) ? $GLOBALS['tf']->variables->request['softdirectory'] : ''; // OPTIONAL - By default it will be installed in the /public_html folder
		$data['admin_pass'] = isset($GLOBALS['tf']->variables->request['admin_pass']) ? $GLOBALS['tf']->variables->request['admin_pass'] : 'pass';
		$data['admin_email'] = isset($GLOBALS['tf']->variables->request['admin_email']) ? $GLOBALS['tf']->variables->request['admin_email'] : "admin@$host";
		//$data['softdb'] = 'wp222';
		//$data['dbusername'] = 'wp222';
		//$data['dbuserpass'] = 'wp222';
		$data['site_name'] = isset($GLOBALS['tf']->variables->request['site_name']) ? $GLOBALS['tf']->variables->request['site_name'] : $softs[$script_id]['name'];
		$data['admin_username'] = isset($GLOBALS['tf']->variables->request['admin_username']) ? $GLOBALS['tf']->variables->request['admin_username'] : 'admin';
		$data['language'] = 'en';
		$data['site_desc'] = isset($GLOBALS['tf']->variables->request['site_desc']) ? $GLOBALS['tf']->variables->request['site_desc'] : $softs[$script_id]['desc'];

		myadmin_log('vps', 'info', 'Webuzo script Installation data-'.json_encode($data), __LINE__, __FILE__);
		$res = $new->install($script_id, $data); // 26 is the SCRIPT ID for Wordpress
		$res = myadmin_unstringify($res);


		/*
		// The URL
		$url = "http://$webuzo_user:$webuzo_password@$host:2002/index.php?".
					'&api=serialize'.
					'&act=software'.
					"&soft=$script_id";

		$post = array('softsubmit' => (isset($GLOBALS['tf']->variables->request['softsubmit'])) ? $GLOBALS['tf']->variables->request['softsubmit'] : '1',
					  'softdomain' => (isset($GLOBALS['tf']->variables->request['softdomain'])) ? $GLOBALS['tf']->variables->request['softdomain'] : $host , // Must be a valid Domain
					  'softdirectory' => (isset($GLOBALS['tf']->variables->request['softdirectory'])) ? $GLOBALS['tf']->variables->request['softdirectory'] : '',
					  'admin_username' => (isset($GLOBALS['tf']->variables->request['admin_username'])) ? $GLOBALS['tf']->variables->request['admin_username'] : 'admin',
					  'admin_pass' => (isset($GLOBALS['tf']->variables->request['admin_pass'])) ? $GLOBALS['tf']->variables->request['admin_pass'] : 'pass',
					  'admin_email' => (isset($GLOBALS['tf']->variables->request['admin_email'])) ? $GLOBALS['tf']->variables->request['admin_email'] : "admin@$host",
					  'language' => 'en',
					  'site_name' => (isset($GLOBALS['tf']->variables->request['site_name'])) ? $GLOBALS['tf']->variables->request['site_name'] : $softs[$script_id]['name'],
					  'site_desc' =>  (isset($GLOBALS['tf']->variables->request['site_desc'])) ? $GLOBALS['tf']->variables->request['site_desc'] : $softs[$script_id]['desc']
		);

		myadmin_log('vps', 'info', "Curl URL - ".$url,__LINE__,__FILE__);
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if(!empty($post)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}

		// Get response from the server.
		$resp = curl_exec($ch);

		// The response will hold a string as per the API response method. In this case its PHP Serialize
		$res = myadmin_unstringify($resp);*/
		myadmin_log('vps', 'info', 'Installation response - '.json_encode($res), __LINE__, __FILE__);
		// Done ?
		if (!empty($res['done'])) {
			add_output('<h2>Install Software</h2>');
			add_output('Installation success! <br /> Details:<br/>');
			$OutputTable = "<table cellpadding=10>
				<tr><td>Software Url</td><td><a style='color: color: #00aaff;' href='{$res['__settings']['softurl']}'>{$res['__settings']['softurl']}</a></td></tr>
				<tr><td>Software Path</td><td>{$res['__settings']['softpath']}</td></tr>
				<tr><td>Domain</td><td>{$res['__settings']['softdomain']}</td></tr>
				<tr><td>Directory</td><td>{$res['__settings']['softdirectory']}</td></tr>
				<tr><td>Software Database</td><td>{$res['__settings']['softdb']}</td></tr>
				<tr><td>Admin Url</td><td><a style='color: color: #00aaff;' href='{$res['__settings']['softurl']}/{$res['__settings']['adminurl']}'>{$res['__settings']['softurl']}/{$res['__settings']['adminurl']}</a></td></tr>
				<tr><td>Admin Username</td><td>{$res['__settings']['admin_username']}</td></tr>
				<tr><td>Admin Email</td><td>{$res['__settings']['admin_email']}</td></tr>
			</table>";
			add_output($OutputTable.'<br />Please check email for further details.');
		// Error
		} else {
			add_output('Installation Failed<br/>Error details:<br />');
			$error_details = null;
			foreach ($res['error'] as $errorKey => $errorValue) {
				$error_details .= $errorValue.'<br />';
			}
			add_output($error_details);
		}
		add_output('<br /><br /><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_view_script&script_id='.$script_id.'&vps_id='.$id.'" title="Back">Back</a>');
	} elseif (isset($GLOBALS['tf']->variables->request['action']) && !empty($GLOBALS['tf']->variables->request['action'])) {
		$script_idd = isset($GLOBALS['tf']->variables->request['script_id']) ? $GLOBALS['tf']->variables->request['script_id'] : '';
		function_requirements($GLOBALS['tf']->variables->request['action']);
		$GLOBALS['tf']->variables->request['action']($host, $webuzo_user, $webuzo_password, $script_idd);
	} else {
		add_output('<h2>List of Available Softwares</h2>');
		$search_string = '';
		if (isset($GLOBALS['tf']->variables->request['search_script']) && !empty($GLOBALS['tf']->variables->request['search_script']) && verify_csrf_referrer(__LINE__, __FILE__)) {
			$search_string = $GLOBALS['tf']->variables->request['search_script'];
			$softfilter = $softs;
			$softs = null;
			if (!empty($softfilter)) {
				foreach ($softfilter as $key1 => $value1) {
					if (stripos($value1['name'], $search_string) !== false) {
						$softs[$key1] = $value1;
					}
				}
			}
		}
		$searchTable = new TFTable;
		$searchTable->csrf('webuzo_search_software');
		$searchTable->set_title('Search Softwares');
		$searchTable->set_post_location('iframe.php');
		$searchTable->add_field($searchTable->make_input('search_script', $search_string, '35'), 'l');
		$searchTable->add_hidden('vps_id', $id);
		$searchTable->add_field($searchTable->make_submit('Search'));
		$searchTable->add_row('Search');
		add_output($searchTable->get_table());
		$filterlinks = '<ul style="margin-left:-48px;">
			<li><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&filter=php&vps_id='.$id.'">PHP</a></li>
			<li><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&filter=js&vps_id='.$id.'">Javascripts</a></li>
			<li><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&filter=perl&vps_id='.$id.'">Perl</a></li>
			<li><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&filter=java&vps_id='.$id.'">Java</a></li>
			<li><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&filter=python&vps_id='.$id.'">Python</a></li>
			</ul>
			<style type="text/css">
				ul {
					white-space:nowrap;
					margin-left: -56px;
				}
				li {
					display:inline;
					margin-left: 10px;;
				}
			</style>
		';
		add_output($filterlinks);
		if (isset($GLOBALS['tf']->variables->request['filter']) && empty($softs)) {
			add_output('No Softwares found!');
		} elseif (!isset($GLOBALS['tf']->variables->request['filter']) && empty($softs)) {
			add_output('Installation in progress... Building application database.');
		}
		if (isset($GLOBALS['tf']->variables->request['filter'])) {
			if (!empty($softs)) {
				foreach ($softs as $soft_id => $soft_det) {
					if ($soft_det['type'] === $GLOBALS['tf']->variables->request['filter']) {
						$soft_cat[$soft_det['cat']][$soft_id] = $soft_det;
					}
				}
			}
		} else {
			if (!empty($softs)) {
				foreach ($softs as $soft_id => $soft_det) {
					$soft_cat[$soft_det['cat']][$soft_id] = $soft_det;
				}
			}
		}
		if (!empty($soft_cat)) {
			foreach ($soft_cat as $sof_cat => $softs) {
				//$softw['type'] = (!empty($softw['type'])) ? $softw['type'] : 'php' ;
				add_output('<h2 class="subcategory"><img style="padding-right:5px;" src="http://www.softaculous.net/images/cats/php_'.$sof_cat.'.gif'.'" />'.$sof_cat.'</h2><table cellpadding="26px;" style="width:500px;">');
				$next =0;
				foreach ($softs as $sid => $softw) {
					++$next;
					if ($next == 1) {
						add_output('<tr>');
					}
					add_output('<td><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_view_script&script_id='.$sid.'&vps_id='.$id.'" title="'.htmlentities($softw['desc'], ENT_QUOTES, 'UTF-8').'"><img style="width:100px;height:100px;" src="https://images.softaculous.com/top15/'.$softw['softname'].'.png"/><div style="text-align: left;">'.$softw['name'].'</div></a></td>');
					if ($next == 4) {
						add_output('</tr>');
						$next =0;
					}
				}
				add_output('</table><br /><br /><br /><br />');
				add_output('
					<style type="text/css">
					 .subcategory {
						text-transform: capitalize;
						width: 560px;
						text-align: left;
						border-bottom: 1px solid black;
					 }
					</style>
					');
			}
		}
	}
}
