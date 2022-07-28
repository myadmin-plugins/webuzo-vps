<?php

/**
 * @param null $host
 * @param null $user
 * @param null $pass
 */
function webuzo_list_domains($host = null, $user = null, $pass = null)
{
    include_once __DIR__.'/webuzo_sdk.php';
    $vps_id = isset($GLOBALS['tf']->variables->request['vps_id']) ? $GLOBALS['tf']->variables->request['vps_id'] : '';
    $new = new Webuzo_API($user, $pass, $host);
    $result = $new->list_domains();
    $response = (!empty($result)) ? myadmin_unstringify($result) : '';
    add_output('<h2 style="display:inline-block;width: 85%;">Domains</h2>
		<a target="SERVICEFrame1" href="iframe.php?choice=none.webuzo_scripts&action=webuzo_add_domain&vps_id='.$vps_id.'" style="display:inline-block;text-decoration: underline;">Add Domain</a>');
    if (!empty($response['domains_list'])) {
        $table = '<table class="sai_divroundshad" cellpadding="26px;" border="0">
					<tr>
						<th style="text-align: left;">Domain</th>
						<th>Path</th>
						<th>Type</th>
						<th>IP Address</th>
						<th>Options</th>
					</tr>';
        foreach ($response['domains_list'] as $domain => $details) {
            $table .= '<tr><td><a target="__blank" href="http://'.$domain.'">'.$domain.'</a></td>';
            $table .= '<td>'.$details['path'].'</td>';
            if (isset($details['addon']) && $details['addon'] == 1) {
                $type_string = 'Addon';
            } elseif (isset($details['addon']) && $details['addon'] == 0) {
                $type_string = 'Parked';
            } elseif ($response['primary_domain'] == $domain) {
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
