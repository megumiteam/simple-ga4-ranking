<?php
namespace digitalcube\SimpleGA4Ranking\Admin\Options;

class View {

	public static function register_setting_fields() {

		$key     = Admin::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section';
		register_setting( $group, $key, [ View::class, 'filter_setting' ] );

		add_settings_section(
			$section,
			'設定',
			[ View::class, 'settings_section' ], $key
		);

		add_settings_field(
			'period',
			__( '本日から何日前までのランキングを表示させるか', 'sga4ranking' ),
			[ View::class, 'field_period' ],
			$key,
			$section
		);

		add_settings_field(
			'cache_expire',
			__( 'キャッシュ時間（秒）', 'sga4ranking' ),
			[ View::class, 'field_cache_expire' ],
			$key,
			$section
		);

		add_settings_field(
			'display_count',
			__( '表示数', 'sga4ranking' ),
			[ View::class, 'field_display_count' ],
			$key,
			$section
		);

		add_settings_field(
			'debug_mode',
			__( 'Debug Mode', 'sga4ranking' ),
			[ View::class, 'field_debug_mode' ],
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

	public static function option_page() { ?>
	<div class="wrap">
		<form method="POST" action="options.php">
			<?php do_settings_sections( Admin::OPTIONS_KEY ); ?>
			<?php settings_fields( Admin::OPTIONS_KEY . '_group' ); ?>			
			<?php submit_button(); ?>
		</form>
	</div>
		<?php
	}

	public static function settings_section() {
		?>
		<?php
		if ( 'true' === filter_input( INPUT_GET, 'settings-updated' ) ) {
			?>
		<div class="updated"><p><?php _e( '設定を保存しました。', 'sga4ranking' ) ?></p></div>
			<?php
		}
	}

	public static function field_period() {
		?>
		<input 
			type="number" 
			id="period" 
			name="<?php echo Admin::OPTIONS_KEY ?>[period]" 
			value="<?php echo esc_attr( Admin::option( 'period' ) ) ?>" 
		/>
		<?php
	}

	public static function field_cache_expire() {
		?>
		<input 
			type="number" 
			id="cache_expire" 
			name="<?php echo Admin::OPTIONS_KEY ?>[cache_expire]" 
			value="<?php echo esc_attr( Admin::option( 'cache_expire' ) ) ?>" 
		/>
		<?php
	}

	public static function field_display_count() {
		?>
		<input 
			type="number" 
			id="display_count" 
			name="<?php echo Admin::OPTIONS_KEY ?>[display_count]" 
			value="<?php echo esc_attr( Admin::option( 'display_count' ) ) ?>" 
		/>
		<?php
	}

	public static function field_debug_mode() {
		?>
		<input 
			type="checkbox" 
			id="debug_mode" 
			name="<?php echo Admin::OPTIONS_KEY ?>[debug_mode]" 
			value="1"
			<?php checked( Admin::option( 'debug_mode' ), '1' ) ?>
		/>
		<?php
	}

}
