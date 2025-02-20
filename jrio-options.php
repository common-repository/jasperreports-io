<?php


/**
 *
 */
class JrioSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'JasperReports IO', 
			'manage_options', 
			'jrio-settings-admin', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'jrio_option_name' );
		?>
		<div class="wrap">
			<h1>JasperReports IO Settings</h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'jrio_option_group' );
				do_settings_sections( 'jrio-settings-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{		
		register_setting(
			'jrio_option_group', // Option group
			'jrio_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'JasperReports IO Service', // Title
			array( $this, 'print_section_info' ), // Callback
			'jrio-settings-admin' // Page
		);  

/*
		add_settings_field(
			'id_number', // ID
			'ID Number', // Title 
			array( $this, 'id_number_callback' ), // Callback
			'jrio-settings-admin', // Page
			'setting_section_id' // Section		   
		);
*/	  

		add_settings_field(
			'jrio_url', 
			'JRIO Address (URL)', 
			array( $this, 'jrio_url_callback' ), 
			'jrio-settings-admin', 
			'setting_section_id'
		);	  
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['id_number'] ) )
			$new_input['id_number'] = absint( $input['id_number'] );

		if( isset( $input['jrio_url'] ) )
			$new_input['jrio_url'] = sanitize_text_field( $input['jrio_url'] );

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		print 'Enter your settings below:';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function id_number_callback()
	{
		printf(
			'<input type="text" id="id_number" name="jrio_option_name[id_number]" value="%s" />',
			isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function jrio_url_callback()
	{
		printf(
			'<input type="text" id="jrio_url" name="jrio_option_name[jrio_url]" value="%s" class="regular-text"/>',
			isset( $this->options['jrio_url'] ) ? esc_attr( $this->options['jrio_url']) : ''
		);
		printf(
			'<p class="description" id="home-description">Enter the address of your own JasperReports IO service instance or the address of the public demo instance available at <a href="https://demo.jaspersoft.com/jrio">https://demo.jaspersoft.com/jrio</a>.</p>'
		);
	}
}


/**
 *
 */
function jrio_add_plugin_page_settings_link( $links ) 
{
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=jrio-settings-admin' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}


if( is_admin() )
	$jrio_settings_page = new JrioSettingsPage();

?>
