<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 * @param null $app_id
 * @throws \Exception
 * @throws \SmartyException
 */
function webuzo_view_sysapps($host = null, $user = null, $pass = null, $app_id = null)
{
    include_once __DIR__.'/webuzo_sdk.php';
    add_output('<h2>Application Details</h2>');
    $vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
    $new = new Webuzo_API($user, $pass, $host);
    $res = $new->list_apps();
    $response = $new->apps;
    $response_installed_app = $new->list_installed_apps();
    $script_details = $response[$app_id];
    $table = '<table width="700px;" cellspacing="1" cellpadding="4" border="0">
	<tbody><tr>
		<td width="10%" style="text-align: center;"><img style="width:100px;height:100px;" src="https://images.softaculous.com/webuzo/softimages/'.$app_id.'__'.$script_details['logo'].'"/></td>
		<td width="90%" colspan="4" class="sai_process_heading">'.$script_details['name'].'</td>
	</tr>
	<tr>
		<td width="20%" style="vertical-align: top;" style="text-align: left;">
			Version : <font size="2"><b>'.$script_details['version'].'</b></font>
		</td>
		<td width="60%" style="text-align: left;">
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
    $tableObj1 = new TFTable();
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
    if (empty($response_installed_app[$app_id])) {
        $title_msg = 'Install Software';
        $confirm_msg = 'Further no confirmation will be asked. Are you sure want to install ?';
    } else {
        $title_msg = 'Software Already Installed';
        $confirm_msg = 'Further no confirmation will be asked. Are you sure want to remove ?';
    }
    $tableObj1->set_title($title_msg);
    $tableObj1->add_field($confirm_msg, 'l');
    $tableObj1->add_row();


    $tableObj1->set_colspan(2);
    if (empty($response_installed_app[$app_id])) {
        $tableObj1->add_field($tableObj1->make_submit('Install'));
    } else {
        $tableObj1->add_field($tableObj1->make_submit('Remove'));
    }

    $tableObj1->add_row();
    $table .= $tableObj1->get_table();
    $table .= '
</div>
<div id="tabs-2">
<table>
	<tr>
		<td></td>
		<td style="vertical-align: top;" style="padding:10px;">'.preg_replace("/<img[^>]+\>/i", ' ', $script_details['overview']).'</td>
	</tr>
</table>
</div>';
    $table .= '</div>
<div id="tabid"></div>';

    add_output($table);
    add_output(
        '
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
