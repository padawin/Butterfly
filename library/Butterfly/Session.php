<?php

/**
 *
 * Class to manage the session
 *
 * @abstract
 * @author Ghislain Rodrigues <ghislain.rodrigues@hotmail.fr>
 *
 */
class Butterfly_Session
{
	/**
	 *
	 * Get a var from the session
	 *
	 */
	public static function get($varName)
	{
		if (!isset($_SESSION)) {
			self::start();
		}

		return isset($_SESSION[$varName]) ? $_SESSION[$varName] : null;
	}

	/**
	 *
	 * Add a new var in the session
	 *
	 */
	public static function set($varName, $var, $e = false)
	{
		if (!isset($_SESSION)) {
			self::start();
		}

		$_SESSION[$varName] = $var;
	}

	/**
	 *
	 * Regenerate the session ID
	 *
	 */
	public static function regenerate()
	{
		session_regenerate_id();
	}

	/**
	 *
	 * Delete the current session
	 *
	 */
	public static function delete($varName)
	{
		unset($_SESSION[$varName]);
	}

	/**
	 *
	 * Start the session
	 *
	 */
	public static function start()
	{
		session_start();
	}
}
