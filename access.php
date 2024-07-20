<?php
/*
Plugin Name:	Token Access
Plugin URI:		http://www.fergusweb.net/software/token-access/
Description:	Limit access to the site to those with a cookie token.  Visitors without the cookie see a customisable "coming soon" style of page.  To remove protection, simply disable this plugin.
Version:		1.6.3
Author:			Anthony Ferguson
Author URI:		http://www.fergusweb.net
*/


add_action('init', function() {
	new TokenAccess();
}, 1);

class TokenAccess {
	private $option_key = 'token-access';
	private $content_file = '/placeholder.php';
	
	/**
	 *	Constructor
	 */
	function __construct() {
		$this->content_file = untrailingslashit(plugin_dir_path(__FILE__)).$this->content_file;
		// Pre-load Options
		$this->load_options();
		// Action Hooks
		add_action('init', array($this,'hijack_public_pages'), 2);
		add_action('admin_menu', array($this,'admin_menu'));
	}
	
	/**
	 *	Option helper functions
	 */
	function load_default_options() {
		$this->opts = array(
			'token_add'		=> 'letmein',
			'token_drop'	=> 'takemeoff',
			'cookie_key'	=> 'Access_Token',
			'lifetime'		=> '168',			// Hours; 168 = 1 week
			'content_from'	=> 'file',			// TODO: change to (int) PostID to use content from database
		);
	}
	function load_options() {
		$this->opts = get_option($this->option_key);
		if (!$this->opts)	$this->load_default_options();
	}
	function save_options($options = false) {
		if (!$options) { $options = $this->opts; }
		update_option($this->option_key, $options);
	}
	
	/**
	 *	Token helpers
	 */
	function check_token() {
		if (@$_COOKIE[$this->opts['cookie_key']] == md5($_SERVER['REMOTE_ADDR'])) {	return true; }
		return false;
	}
	function add_token() {
		$expires = time() + ($this->opts['lifetime']*60*60);
		setcookie($this->opts['cookie_key'], md5($_SERVER['REMOTE_ADDR']), $expires, '/');
	}
	function drop_token() {
		setcookie($this->opts['cookie_key'], md5($_SERVER['REMOTE_ADDR']), time()-3600, '/');
	}
	
	/**
	 *	Handle any ?token commands
	 */
	function token_handler() {
		$redirect = false;
		if (isset($_GET[$this->opts['token_add']])) {
			$this->add_token();
			$redirect = str_replace('?'.$this->opts['token_add'], '', $_SERVER['REQUEST_URI']);
		}
		if (isset($_GET[$this->opts['token_drop']])) {
			$this->drop_token();
			$redirect = str_replace('?'.$this->opts['token_drop'], '', $_SERVER['REQUEST_URI']);
		}
		if ($redirect) {
			header('Location: '.$redirect);
			exit;
		}
	}
	
	/**
	 *	Hijack Page - if visitor doesn't have cookie, serve up placeholder
	 */
	function hijack_public_pages() {
		// If WP CLI don't run!
		if (defined('WP_CLI'))	return false;
		// If admin or login, don't run this.
		if (is_admin()) 		return false;
		if (stristr($_SERVER['SCRIPT_FILENAME'], 'wp-login.php'))	return false;
		// Handle any ?token commands
		$this->token_handler();
		// Are we allowed access?
		$access = $this->check_token();
		// If user logged in, allow access.
		if (is_user_logged_in())	$access = true;

		if ($access) {
			add_action('wp_footer', array($this,'public_visual_cue'));
		} else {
			echo file_get_contents($this->content_file);
			echo "\n\n".'<!-- Visitor IP: '.$_SERVER['REMOTE_ADDR'].' -->'."\n";
			exit;
		}
	}
	
	/**
	 *	Provide a visual cue to public pages, to remind visitor that plugin is active.
	 *	This code is inserted in the footer
	 */
	function public_visual_cue() {
		?>
		<div class="token-access-alert">
			<p><strong>Alert:</strong> <a href="<?php echo admin_url('tools.php?page=token-access'); ?>">Site access is limited</a>.</p>
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
	 *	Admin Loader
	 */
	function admin_menu() {
		$hook = add_submenu_page('tools.php', 'Configure Token Access to Site', 'Token Access', 'manage_options', 'token-access', array($this,'settings_page'));
		add_action("load-$hook", array($this,'admin_enqueue'));	
	}
	
	function admin_enqueue() {
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_style('token-access', plugins_url('admin.css', __FILE__));
	}
	
	function settings_page() {
		echo '<div class="wrap">'."\n";
		echo '<h2>Token Access Settings</h2>'."\n";
		
		if (isset($_POST['SaveTokenSettings'])) {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'token_access')) { echo '<p class="alert">Invalid Security</p></div>'; return; }
			$this->opts = array_merge($this->opts, array(
				'token_add'	=> $_POST['token_add'],
				'token_drop'=> $_POST['token_remove'],
				'lifetime'	=> $_POST['token_expires'],
				'cookie_key'=> $_POST['token_key'],
			));
			$this->save_options();
			echo '<div id="message" class="updated fade"><p><strong>Token Settings options have been saved.</strong></p></div>';
		}
		if (isset($_POST['SaveFileContent'])) {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'token_access')) { echo '<p class="alert">Invalid Security</p></div>'; return; }
			file_put_contents($this->content_file, stripslashes($_POST['file_content']));
			echo '<div id="message" class="updated fade"><p><strong>Changes to placeholder content saved.</strong></p></div>';
		}
		?>
<script type="text/javascript"><!--
jQuery(document).ready(function($){
	$('#tabContainer').tabs();
});
--></script>
<div id="tabContainer">
  <ul id="tabMenu">
	<li><a href="#tabSettings"><span>Token Settings</span></a></li>
	<li><a href="#tabContent"><span>Placeholder Content</span></a></li>
  </ul><!-- tcTabMenu -->
  <div id="tabSettings">
    <form id="TokenAccess" class="tokens" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
      <?php wp_nonce_field('token_access'); ?>
      <div class="notes">
        <p>When you visit <code><?php echo trailingslashit(home_url()); ?>?<?php echo $this->opts['token_add']; ?></code> a cookie will be set on your computer, allowing you full access to view the site.</p>
        <p>Without this cookie, visitors will only see the placeholder page.</p>
        <p>To remove this protection and publish your site, simply disable this plugin.</p>
        <p>Changing the Token Key will expire all existing tokens, and require visitors to visit that link again to set a new cookie.</p>
      </div>
        <p><label for="token_add">Add Token</label>
            ?<input type="text" name="token_add" id="token_add" value="<?php echo $this->opts['token_add']; ?>" /></p>
        <p><label for="token_remove">Remove Token</label>
            ?<input type="text" name="token_remove" id="token_remove" value="<?php echo $this->opts['token_drop']; ?>" /></p>
        <p><label for="token_expires">Token expires</label>
            <input type="text" name="token_expires" id="token_expires" value="<?php echo $this->opts['lifetime']; ?>" /> hours</p>
        <p><label for="token_key">Token key</label>
            <input type="text" name="token_key" id="token_key" value="<?php echo $this->opts['cookie_key']; ?>" /></p>
        <p><label>&nbsp;</label>
            <input type="submit" name="SaveTokenSettings" value="Save Changes" id="Save" class="button-primary SaveButton" /></p>
        <div class="cb"></div>
        
        
    </form>
  </div><!-- tab -->
  <div id="tabContent">
    <form id="template" class="TokenContent" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>#tabContent">
      <?php wp_nonce_field('token_access'); ?>
      <?php
        if (!is_writable($this->content_file)) {
            echo '<div class="warning"><p><strong>Warning: <code>'.str_replace(TOKENACCESS_PLUGIN_DIR, '', $this->content_file).'</code> is not writable.</strong><br />'.
                'It <strong>MUST</strong> be writable before you can make any changes to your content file!</p></div>';
            echo '</form></div></div></div><!-- tab container wrap -->';
            return;
        }
        $fileContent = file_get_contents($this->content_file);
      ?>
        <textarea id="file_content" name="file_content"><?php echo $fileContent; ?></textarea>
        <p class="save"><input type="submit" name="SaveFileContent" value="Save Content" id="Save" class="button-primary SaveButton" /></p>
        <div class="cb"></div>
    </form>
  </div><!-- tab -->
</div><!-- tabContainer -->
        <?php
		echo '</div>'."\n";
	}
	
}





?>