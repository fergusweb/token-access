<?php
/**
 * Main plugin class
 * 
 * @package		token_access
 */

class Token_Access {

	/**
	 * Default Cookie Key
	 *
	 * @var string
	 */
	public static $cookie_key = 'Access_Token';

	/**
	 * Default Cookie Key
	 *
	 * @var string
	 */
	public static $add_token = 'letmein';

	/**
	 * Default Cookie Key
	 *
	 * @var string
	 */
	public static $remove_token = 'takemeoff';

	/**
	 * Default Cookie Expires Hours
	 *
	 * @var string
	 */
	public static $expire_hours = 730; // 730 = 1 month.

	/**
	 *  Constructor
	 */
	public function __construct() {
		// Load some settings
		self::$cookie_key = get_option( 'tokenaccess_token_key', self::$cookie_key );
		self::$add_token = get_option( 'add_token', self::$add_token );
		self::$remove_token = get_option( 'remove_token', self::$remove_token );
		self::$expire_hours = get_option( 'expire_hours', self::$expire_hours );

		// Load front or back end
		if (is_admin()) {
			new Token_Access_Admin();
		} else {
			new Token_Access_Public();
		}
	}


}