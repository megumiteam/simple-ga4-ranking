<?php
namespace digitalcube\SimpleGA4Ranking\Admin\OAuth;

class View {

	public static function register_setting_fields() {

		$key     = Admin::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section';
		register_setting( $group, $key, [ View::class, 'filter_setting' ] );

		add_settings_section(
			$section,
			__( 'Settings', 'sga4ranking' ),
			[ View::class, 'settings_section' ], $key
		);

		add_settings_field(
			'client_id',
			__( 'client ID', 'sga4ranking' ),
			[ View::class, 'field_client_id' ],
			$key,
			$section
		);

		add_settings_field(
			'client_secret',
			__( 'client secret', 'sga4ranking' ),
			[ View::class, 'field_client_secret' ],
			$key,
			$section
		);

		add_settings_field(
			'callback_url',
			__( 'redirect URI', 'sga4ranking' ),
			[ View::class, 'field_callback_url' ],
			$key,
			$section
		);

		add_settings_field(
			'property_id',
			__( 'GA4 property id', 'sga4ranking' ),
			[ View::class, 'field_property_id' ],
			$key,
			$section
		);
	}

	public static function filter_setting( $input ) {
		foreach ( array_keys( Admin::OPTIONS_DEFUALT ) as $option_key ) {
			if ( ! isset( $input[ $option_key ] ) || empty( trim( $input[ $option_key ] ) ) ) {
				$input[ $option_key ] = '';
			}
		}
		return $input;
	}

	public static function option_page() {
		$admin = new Admin();
		$auth  = new Auth();
		?>
	<div class="wrap">

		<h2><?php _e( 'GA4 settings', 'sga4ranking' ) ?></h2>
		<hr />

		<form method="POST" action="options.php">
			<?php do_settings_sections( Admin::OPTIONS_KEY ); ?>
			<?php settings_fields( Admin::OPTIONS_KEY . '_group' ); ?>			
			<?php submit_button(); ?>
		</form>
		<hr />

		<?php
		if ( $admin->saved_options() ) :
			$callbak_url = Admin::option( 'callback_url' );
			?>
			<form method="POST" action="<?php echo esc_url( $callbak_url ) ?>" >
			<?php wp_nonce_field( 'sga4ranking_ga_auth', 'sga4ranking_ga_auth' ); ?>			
			<?php if ( $auth->authorized() ) : ?>
			<h2><?php _e( 'Google API Authorization (Authorized)', 'sga4ranking' ) ?></h2>
			<button class="button button-primary"><?php _e( 'Reauthorization', 'sga4ranking' ) ?></button>
			<h3><?php _e( 'access token', 'sga4ranking' ) ?></h3>
			<p><code><?php echo get_transient( Auth::ACCESS_TOKEN_TRANSIENT_KEY ) ?></code></p>
			<?php else : ?>
			<h2><?php _e( 'Google API Authorization (Unauthorized)', 'sga4ranking' ) ?></h2>
			<button class="button button-primary"><?php _e( 'Authorization', 'sga4ranking' ) ?></button>
			<?php endif; ?>
			</form>
		<?php endif; ?>
	</div>		
		<?php
	}

	public static function settings_section() {

	}

	public static function field_client_id() {
		?>
		<input 
			type="text" 
			id="client_id" 
			name="<?php echo Admin::OPTIONS_KEY ?>[client_id]" 
			class="regular-text" 
			value="<?php echo esc_attr( Admin::option( 'client_id' ) ) ?>" 
		/>
		<?php
	}

	public static function field_client_secret() {
		?>
		<input 
			type="text" 
			id="client_secret" 
			name="<?php echo Admin::OPTIONS_KEY ?>[client_secret]" 
			class="regular-text" 
			value="<?php echo esc_attr( Admin::option( 'client_secret' ) ) ?>" 
		/>
		<?php
	}

	public static function field_callback_url() {

		$auth_url = admin_url( '/options-general.php?page=gapiwp-analytics' );

		?>
		<code><?php echo esc_html( $auth_url ); ?></code>
		<input
			type="hidden" 
			readonly
			id="callback_url"
			name="<?php echo Admin::OPTIONS_KEY ?>[callback_url]" 
			class="regular-text" 
			value="<?php echo esc_url( $auth_url ); ?>" 
		/>
		<button onclick="return copy_callback_url()"><?php _e( 'Copy', 'sga4ranking' ); ?></button>
		<script>
			function copy_callback_url() {
				navigator.clipboard.writeText(document.getElementById("callback_url").value);
				return false;
			}
		</script>
		<?php
	}

	public static function field_property_id() {
		?>
		<input 
			type="text" 
			id="property_id" 
			name="<?php echo Admin::OPTIONS_KEY ?>[property_id]" 
			class="regular-text" 
			value="<?php echo esc_attr( Admin::option( 'property_id' ) ) ?>" 
		/>
		<?php
	}

}
