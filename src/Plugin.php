<?php

namespace Detain\MyAdminWebuzo;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminWebuzo
 */
class Plugin {

	public static $name = 'Webuzo Plugin';
	public static $description = 'Allows handling of Webuzo HTML5 VNC Connections';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return array
	 */
	public static function getHooks() {
		return [
			//'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
			'function.requirements' => [__CLASS__, 'getRequirements'],
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
					if (has_acl('client_billing'))
							$menu->add_link('admin', 'choice=none.abuse_admin', '//my.interserver.net/bower_components/webhostinghub-glyphs-icons/icons/development-16/Black/icon-spam.png', 'Webuzo');
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event) {
		$loader = $event->getSubject();
		$loader->add_requirement('webuzo_configure', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_configure.php');
		$loader->add_requirement('webuzo_scripts', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_scripts.php');
		$loader->add_requirement('webuzo_edit_installation', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_edit_installation.php');
		$loader->add_requirement('webuzo_install_sysapp', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_install_sysapp.php');
		$loader->add_requirement('webuzo_add_domain', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_add_domain.php');
		$loader->add_requirement('webuzo_list_sysapps', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_sysapps.php');
		$loader->add_requirement('webuzo_randomPassword', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_randomPassword.php');
		$loader->add_requirement('webuzo_remove_script', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_remove_script.php');
		$loader->add_requirement('webuzo_view_script', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_view_script.php');
		$loader->add_requirement('webuzo_list_installed_scripts', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_installed_scripts.php');
		$loader->add_requirement('webuzo_import_script', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_import_script.php');
		$loader->add_requirement('webuzo_list_backups', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_backups.php');
		$loader->add_requirement('webuzo_list_domains', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_domains.php');
		$loader->add_requirement('webuzo_remove_domain', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_remove_domain.php');
		$loader->add_requirement('webuzo_view_sysapps', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_view_sysapps.php');
		$loader->add_requirement('webuzo_list_installed_sysapps', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_list_installed_sysapps.php');
		$loader->add_requirement('webuzo_update_logo', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo_update_logo.php');
		$loader->add_requirement('webuzo_get_all_scripts', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_add_backup', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_download_backup', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_remove_backup', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_restore_backup', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_api_call', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
		$loader->add_requirement('webuzo_format_units_size', '/../vendor/detain/myadmin-webuzo-vps/src/webuzo.functions.inc.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_text_setting('General', 'Webuzo', 'abuse_imap_user', 'Webuzo IMAP User:', 'Webuzo IMAP Username', ABUSE_IMAP_USER);
		$settings->add_text_setting('General', 'Webuzo', 'abuse_imap_pass', 'Webuzo IMAP Pass:', 'Webuzo IMAP Password', ABUSE_IMAP_PASS);
	}

}
