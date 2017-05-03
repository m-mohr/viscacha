<?php

namespace Viscacha\Model;

/**
 * Facade for UserSelf that contains the current user data for the logged in user.
 */
class My {

	private static $instance = null;

	public static function __callStatic($name, $arguments) {
		if (self::$instance == null) {
			global $my;
			self::$instance = new UserSelf($my->id);
		}
		return call_user_func_array(array(self::$instance, $name), $arguments);
	}

}
