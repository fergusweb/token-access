<?php
/**
 * Main plugin class
 * 
 * @package		token_access
 */

class Token_Access_Public {

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'hijack_public_pages' ), 2 );
	}


	/**
	 * Run before any output, and determine if we need to hijack to page to show the 'coming soon' message
	 */
	public function hijack_public_pages() {
		// If WP CLI don't run!
		if ( defined( 'WP_CLI' ) ) {
			return false;
		}

		// If admin area, don't run this.
		if ( is_admin() ) {
			return false;
		}

		// If login page, don't run this.
		if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
			return false;
		}

		// Handle any ?token commands.
		$this->token_handler();

		// Are we allowed access?
		$access = $this->check_token();
		// If user logged in, always allow access.
		if ( is_user_logged_in() ) {
			$access = true;
		}

		if ( ! $access ) {
			// Check to see if we have HTML saved, or show default.
			$custom_html = get_option( 'tokenaccess_placeholder_html' );
			if ( $custom_html ) {
				echo $custom_html; // phpcs:ignore
			} else {
				$placeholder_file = plugin_dir_path( __FILE__ ) . 'placeholder.html';
				readfile( $placeholder_file );  // phpcs:ignore
			}
			echo PHP_EOL . PHP_EOL . '<!-- Visitor IP: ' . $_SERVER['REMOTE_ADDR'] . ' -->' . PHP_EOL;  // phpcs:ignore
			exit;
		}
		// Add visual cue for people seeing this page, to remind them that site is not public.
		add_action( 'wp_footer', array( $this, 'public_visual_cue' ) );
	}


	/**
	 * Provide a visual cue on public pages, to remind visitor that the site is not public.
	 * Inserted into the footer.
	 *
	 * @return void
	 */
	public function public_visual_cue() {
		?>
		<div class="token-access-alert">
			<p><strong>Alert:</strong> <a href="<?php echo esc_attr( admin_url( 'options-reading.php' ) ); ?>">Site access is limited</a>.</p>
		</div>
		<style><!--
		.token-access-alert {
			color:#3B0000; background:#FAF5F5; border:2px solid #933; border-radius:0.5em;
			font-size:12px; text-align:center; padding:0.75em; line-height:1;
			position:fixed; top:0.5em; right:0.5em; opacity:0.8; z-index:99;
		}
		.admin-bar .token-access-alert { top:3.5em; }
		.token-access-alert p { margin:0; padding:0; }
		.token-access-alert a { color:inherit; text-decoration:underline; }
		.token-access-alert a:hover { color:#C00; }
		--></style>
		<?php
	}


	/**
	 * Check the cookie, to see if user has an access token
	 *
	 * @return bool
	 */
	private function check_token() {
		$cookie_key = get_option( 'tokenaccess_token_key', Token_Access::$cookie_key );

		if ( ! isset( $_COOKIE[ $cookie_key ] ) ) {
			return false;
		}
		if ( md5( $_SERVER['REMOTE_ADDR'] ) === $_COOKIE[ $cookie_key ] ) {  // phpcs:ignore
			return true;
		}
		return false;
	}

	/**
	 * Set the cookie, so visitor has the access token
	 */
	private function add_token() {
		$cookie_key    = get_option( 'tokenaccess_token_key', Token_Access::$cookie_key );
		$expires_hours = get_option( 'tokenaccess_expiry_hours', Token_Access::$expires_hours );
		$expires       = time() + ( $expires_hours * 60 * 60 );
		setcookie( $cookie_key, md5( $_SERVER['REMOTE_ADDR'] ), $expires, '/' );  // phpcs:ignore
	}

	/**
	 * Wipe the cookie, so visitor no longer has the access token
	 */
	private function drop_token() {
		$cookie_key = get_option( 'tokenaccess_token_key', Token_Access::$cookie_key );
		setcookie( $cookie_key, md5( $_SERVER['REMOTE_ADDR'] ), time() - 3600, '/' );  // phpcs:ignore
	}

	/**
	 *  Handle any ?token commands
	 */
	private function token_handler() {
		// Load the add/remove token keys.
		$add_token    = get_option( 'tokenaccess_add_token', Token_Access::$add_token );
		$remove_token = get_option( 'tokenaccess_remove_token', Token_Access::$remove_token );

		$redirect = false;

		if ( isset( $_GET[ $add_token ] ) ) {  // phpcs:ignore
			$this->add_token();
			$redirect = remove_query_arg( $add_token );
		}
		if ( isset( $_GET[ $remove_token ] ) ) {  // phpcs:ignore
			$this->drop_token();
			$redirect = remove_query_arg( $remove_token );
		}
		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}
}