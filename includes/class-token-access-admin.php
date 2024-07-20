<?php
/**
 * Admin plugin class
 * 
 * @package		token_access
 */

class Token_Access_Admin {

    /**
     * Constructor
     */
    function __construct() {
        // Settings API
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . TOKEN_ACCESS_PLUGIN_BASE, array($this,'plugin_action_links') );

    }

    public function plugin_action_links( $actions ) {
        $actions = array_merge( $actions, array(
            '<a href="' .admin_url( 'options-reading.php' ). '">Settings > Reading</a>'
        ));
        return $actions;
    }


    /**
	 * Use the WP Settings API to register our settings.
	 *
	 * @return void
	 */
	public function settings_init() {
		// register a new section in the "reading" page.
		add_settings_section(
			'tokenaccess_settings_section', // id.
			'Token Access', // Section heading.
			// array( $this, 'settings_section_callback' ), // callback.
			function() {
				echo '<p>Added by the Token Access plugin.';
			},
			'reading' // page slug.
		);

		// Array of fields to register.
		$settings = array(
			array(
				'name'        => 'tokenaccess_add_token',
				'title'       => 'Add Token',
				'default'     => Token_Access::$add_token,
				'description' => 'The URL to visit: <code>' . home_url( '?VALUE' ) . '</code>',
			),
			array(
				'name'        => 'tokenaccess_remove_token',
				'title'       => 'Remove Token',
				'default'     => Token_Access::$remove_token,
				'description' => 'The URL to visit: <code>' . home_url( '?VALUE' ) . '</code>',
			),
			array(
				'name'              => 'tokenaccess_expiry_hours',
				'title'             => 'Token Expires',
				'type'              => 'number',
				'default'           => Token_Access::$expire_hours,
				'description'       => 'Hours until your cookie expires, and you\'ll have to visit the link again.',
				'sanitize_callback' => 'absint', // converts value to a non-negative integer.
			),
			array(
				'name'        => 'tokenaccess_token_key',
				'title'       => 'Token Key',
				'default'     => Token_Access::$cookie_key,
				'description' => 'Changing this will invalidate any existing cookies, so people must refresh.',
			),
			array(
				'name'        => 'tokenaccess_placeholder_html',
				'title'       => 'Placeholder HTML',
				'default'     => '',
				'description' => 'You can supply custom HTML here to overwrite the default placeholder page.',
			),
		);

		// Now set each one up.
		foreach ( $settings as $setting ) {
			// Register the setting.
			register_setting( 'reading', $setting['name'], $setting );
			// Add the field.
			add_settings_field(
				$setting['name'], // id.
				$setting['title'], // title.
				// Callbacks will use the setting name as the function name.  Must create function below.
				array( $this, $setting['name'] ), // callback.
				'reading', // page slug.
				'tokenaccess_settings_section' // section.
			);
		}
	}


	/**
	 * Draw field for registered setting Add Token
	 */
	public function tokenaccess_add_token() {
		$this->setting_field_text( __FUNCTION__ );
	}

	/**
	 * Draw field for registered setting Remove Token
	 */
	public function tokenaccess_remove_token() {
		$this->setting_field_text( __FUNCTION__ );
	}

	/**
	 * Draw field for registered setting Expiry Hours
	 */
	public function tokenaccess_expiry_hours() {
		$this->setting_field_text( __FUNCTION__ );
	}

	/**
	 * Draw field for registered setting Token Key
	 */
	public function tokenaccess_token_key() {
		$this->setting_field_text( __FUNCTION__ );
	}

	/**
	 * Draw field for registered setting Placeholder HTML
	 */
	public function tokenaccess_placeholder_html() {
		$this->setting_editor_text( __FUNCTION__ );
	}


	/**
	 * Generic function to draw the Setting Field for Text inputs.
	 *
	 * @param string $key Registered Setting Key.
	 * @return void
	 */
	public function setting_field_text( $key = '' ) {
		if ( ! $key ) {
			return;
		}
		global $wp_registered_settings;
		if ( ! array_key_exists( $key, $wp_registered_settings ) ) {
			return false;
		}
		$setting      = $wp_registered_settings[ $key ];
		$option_value = get_option( $key );
		$field_value  = isset( $option_value ) ? esc_attr( $option_value ) : '';
		$output       = '<input type="text" name="' . $setting['name'] . '" value="' . esc_attr( $field_value ) . '" />';
		if ( $setting['description'] ) {
			$setting['description'] = str_replace( 'VALUE', $field_value, $setting['description'] );
			$output                .= '<p class="description">' . $setting['description'] . '</p>';
		}
		// Sanitize.
		$allowed_html = array(
			'p'     => array(
				'class' => array(),
			),
			'code'  => array(),
			'input' => array(
				'type'  => array(),
				'name'  => array(),
				'value' => array(),
			),
		);
		echo wp_kses( $output, $allowed_html );
	}

	/**
	 * Draw the settings field for the code editor
	 *
	 * @param string $key Registered Setting Key.
	 * @return void
	 */
	public function setting_editor_text( $key = '' ) {
		if ( ! $key ) {
			return;
		}
		global $wp_registered_settings;
		if ( ! array_key_exists( $key, $wp_registered_settings ) ) {
			return false;
		}
		$setting      = $wp_registered_settings[ $key ];
		$option_value = get_option( $key );
		$field_value  = isset( $option_value ) ? esc_attr( $option_value ) : '';
		$output       = '<textarea class="editor" name="' . $setting['name'] . '">' . esc_textarea( htmlspecialchars_decode( $field_value ) ) . '</textarea>';
		if ( $setting['description'] ) {
			$setting['description'] = str_replace( 'VALUE', $field_value, $setting['description'] );
			$output                .= '<p class="description">' . $setting['description'] . '</p>';
		}
		// Sanitize.
		$allowed_html = array(
			'p'        => array(
				'class' => array(),
			),
			'code'     => array(),
			'textarea' => array(
				'class' => array(),
				'name'  => array(),
			),
		);
		echo wp_kses( $output, $allowed_html );

		// Use the Code Editor JS on this textarea.
		$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_localize_script( 'wp-theme-plugin-editor', 'cm_settings', $cm_settings );
		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );
		wp_add_inline_script( 'wp-theme-plugin-editor', 'jQuery(document).ready(function($) { wp.codeEditor.initialize($("textarea.editor"), cm_settings); })' );
	}

}