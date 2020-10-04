<?php

class Builder_Tiles_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_options' ), 100 );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function setup_options() {
		global $ThemifyBuilder;

		if( isset( $ThemifyBuilder ) ) {
			$parent_page = $ThemifyBuilder->is_themify_theme() ? 'themify' : 'themify-builder';
			add_submenu_page( $parent_page, __( 'Tiles Module', 'builder-tiles' ), __( 'Builder Tiles', 'builder-tiles' ), 'manage_options', 'builder-tiles', array( $this, 'create_admin_page' ) );
		}
	}

    public function create_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Builder Tiles Module', 'builder-tiles' ); ?></h2>           
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'builder_tiles' );   
				do_settings_sections( 'builder-tiles' );
				submit_button(); 
				?>
			</form>
		</div>
		<?php
    }

	/**
	 * Register and add settings
	 */
	public function page_init() {        
		register_setting(
			'builder_tiles', // Option group
			'builder_tiles' // Option name
		);

		add_settings_section(
			'builder-tiles-responsive', // ID
			__( 'Responsive Tiles', 'builder-tiles' ), // Title
			null, // Callback
			'builder-tiles' // Page
		);

		add_settings_field(
			'fluid_tiles', // ID
			__( 'Responsive Tiles', 'builder-contact' ), // Title 
			array( $this, 'render_option' ), // Callback
			'builder-tiles', // Page
			'builder-tiles-responsive' // Section           
		);

		add_settings_field(
			'tiles_gutter', // ID
			__( 'Tile Spacing', 'builder-contact' ), // Title 
			array( $this, 'render_spacing_field' ), // Callback
			'builder-tiles', // Page
			'builder-tiles-responsive' // Section           
		);
    }

	public function render_option() {
		?>
		<select name="builder_tiles[fluid_tiles]">
			<option value="1" <?php selected( 1, Builder_Tiles::get_instance()->get_option( 'fluid_tiles' ) ); ?>><?php _e( 'Enabled', 'builder-tiles' ); ?></option>
			<option value="0" <?php selected( 0, Builder_Tiles::get_instance()->get_option( 'fluid_tiles' ) ); ?>><?php _e( 'Disabled', 'builder-tiles' ); ?></option>
		</select>
		<?php
	}

	public function render_spacing_field() {
		?><input type="text" class="small" value="<?php echo esc_attr( Builder_Tiles::get_instance()->get_option( 'gutter', 0 ) ) ?>" name="builder_tiles[gutter]" />px
		<?php
	}
}
new Builder_Tiles_Admin;