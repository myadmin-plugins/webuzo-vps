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
		$loader->add_requirement('class.Webuzo', '/../vendor/detain/myadmin-webuzo-vps/src/Webuzo.php');
		$loader->add_requirement('deactivate_kcare', '/../vendor/detain/myadmin-webuzo-vps/src/abuse.inc.php');
		$loader->add_requirement('deactivate_abuse', '/../vendor/detain/myadmin-webuzo-vps/src/abuse.inc.php');
		$loader->add_requirement('get_abuse_licenses', '/../vendor/detain/myadmin-webuzo-vps/src/abuse.inc.php');
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
