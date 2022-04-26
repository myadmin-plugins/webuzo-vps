<?php

/**
 * @param $host
 * @param $user
 * @param $pass
 * @param $script_id
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_view_script($host, $user, $pass, $script_id)
{
	include_once __DIR__.'/webuzo_sdk.php';
	$vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
	function_requirements('webuzo_api_call');
	function_requirements('webuzo_get_all_scripts');
	$response1 = webuzo_get_all_scripts($user, $pass, $host);
	$script_details = $response1[$script_id];
	if ($script_details['type'] === 'js') {
		$act = 'js';
	} elseif ($script_details['type'] === 'perl') {
		$act = 'perl';
	} else {
		$act = 'software';
	}
	$act = "&act=$act&soft=$script_id";
	$response = webuzo_api_call($host, $user, $pass, $act);
	$response = myadmin_unstringify($response);

	$table = '<table width="700px;" cellspacing="1" cellpadding="4" border="0">
	<tbody><tr>
		<td width="10%" style="text-align: center;"><img src="https://images.softaculous.com/top15/48/'.$script_details['softname'].'.png"></td>
		<td width="90%" colspan="4" class="sai_process_heading">'.$script_details['name'].'</td>
	</tr>
	<tr>
		<td width="20%" style="vertical-align: top;" style="text-align: left;">
			Version : <font size="2"><b>'.$script_details['ver'].'</b></font>
		</td>
		<td width="60%" style="text-align: left;">
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
	$tableObj1->add_field('Choose Domain', 'l');
	$new = new Webuzo_API($user, $pass, $host);
	$result = $new->list_domains();
	$result = myadmin_unstringify($result);

	$select_domain = '<select name="softdomain" style="width:250px;">';
	foreach ($result['domains_list'] as $domain => $details) {
		$select_domain .= '<option value="'.$domain.'">'.$domain.'</option>';
	}
	$select_domain .= '</select>';
	$tableObj1->add_field($select_domain, 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('In Directory', 'l');
	$tableObj1->add_field($tableObj1->make_input('softdirectory', '', 25), 'l');
	$tableObj1->add_row();

	$tableObj1->set_colspan(2);
	$tableObj1->add_field('The directory is relative to your domain and <span style="font-weight:bold;">should not exist</span>. e.g. To install at http://mydomain/dir/ just type <span style="font-weight:bold;">dir</span>. To install only in http://mydomain/ leave this empty.', 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('Site Name', 'l');
	$tableObj1->add_field($tableObj1->make_input('site_name', '', 25), 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('Site Description', 'l');
	$tableObj1->add_field($tableObj1->make_input('site_desc', '', 25), 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('Admin Username', 'l');
	$tableObj1->add_field($tableObj1->make_input('admin_username', '', 25), 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('Admin Password', 'l');
	$tableObj1->add_field($tableObj1->make_input('admin_pass', '', 25), 'l');
	$tableObj1->add_row();

	$tableObj1->add_field('Admin Email', 'l');
	$tableObj1->add_field($tableObj1->make_input('admin_email', '', 25), 'l');
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
		<td><img width="500" src="https://images.softaculous.com/softimages/screenshots/'.$script_id.'_screenshot1.gif"></td>
		<td style="vertical-align: top;" style="padding:10px;">'.preg_replace("/<img[^>]+\>/i", ' ', $response['info']['overview']).'</td>
	</tr>
</table>
</div>
<div id="tabs-3">
	<div style="text-align: left;">'.$response['info']['features'].'</div>
</div>
<div id="tabs-4">

</div>
<div id="tabs-5">';
	$table .= '</div>
</div>
<div id="tabid"></div>';

	add_output($table);
	add_output(
		'
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
