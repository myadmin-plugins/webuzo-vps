<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_list_sysapps($host=null, $user=null, $pass=null) {
		include_once INCLUDE_ROOT.'/../vendor/softaculous/webuzo_sdk/webuzo_sdk.php';
		$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
		$user = isset($GLOBALS['tf']->variables->request['user']) ? $GLOBALS['tf']->variables->request['user'] : $user;
		$pass = isset($GLOBALS['tf']->variables->request['pass']) ? $GLOBALS['tf']->variables->request['pass'] : $pass;
		$host = isset($GLOBALS['tf']->variables->request['host']) ? $GLOBALS['tf']->variables->request['host'] : $host;
		$new = new Webuzo_API($user,$pass,$host);
		$res= $new->list_apps();
		$response = $new->apps;
		$search_string = '';
		if(isset($GLOBALS['tf']->variables->request['search_script']) && !empty($GLOBALS['tf']->variables->request['search_script']) && verify_csrf_referrer(__LINE__,__FILE__)) {
			$search_string = $GLOBALS['tf']->variables->request['search_script'];
			$softfilter = $response;
			$response = null;
			if(!empty($softfilter)) {
				foreach ($softfilter as $key1 => $value1) {
					if(stripos($value1['fullname'], $search_string) !== false) {
						$response[$key1] = $value1;
					}
				}
			}
		}
		add_output('<h2>List of Available System Applications</h2>');
		$searchTable = new TFTable;
		$searchTable->set_title('Search Applications');
		$searchTable->csrf('webuzo_search_applications');
		$searchTable->set_post_location('iframe.php');
		$searchTable->set_choice('webuzo_list_sysapps');
		$searchTable->add_field($searchTable->make_input('search_script',$search_string,'35'),'l');
		$searchTable->add_hidden('vps_id',$vps_id);
		$searchTable->add_hidden('user',$user);
		$searchTable->add_hidden('pass',$pass);
		$searchTable->add_hidden('host',$host);
		$searchTable->add_field($searchTable->make_submit('Search'));
		$searchTable->add_row('Search');
		add_output($searchTable->get_table());
		if(!empty($response)) {
			foreach ($response as $install_id => $details) {
				$semi_str = str_replace('_', ' ', $details['category']);
				$final_str = ucwords($semi_str);
				$final['apps'][$final_str][$install_id] = $details;
			}
		}
		if(!empty($final['apps'])) {
			foreach($final['apps'] as $sof_cat => $softs) {
				add_output('<h2 class="subcategory">'.$sof_cat.'</h2><table cellpadding="26px;" style="width:500px;">');$next =0;
				foreach($softs as $sid => $softw) {
					++$next;
					if($next == 1) {add_output('<tr>');}
					add_output('<td><a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_view_sysapps&script_id='.$sid.'&vps_id='.$vps_id.'" title="'.htmlentities($softw['desc'], ENT_QUOTES, 'UTF-8').'"><img alt="" style="width:100px;height:100px;" src="https://images.softaculous.com/webuzo/softimages/'.$sid.'__'.$softw['logo'].'"/><div align="left">'.$softw['name'].'</div></a></td>');
					if($next == 4) {
						add_output('</tr>');
						$next =0;
					}
				}
				add_output('</table><br /><br /><br /><br />');
			}
		}

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

