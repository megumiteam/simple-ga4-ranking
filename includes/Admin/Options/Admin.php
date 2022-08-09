<?php
namespace digitalcube\SimpleGA4Ranking\Admin\Options;

class Admin {

	const OPTIONS_KEY = 'sga4-ranking-options';

	const OPTIONS_DEFUALT = [
		'period'        => 30,
		'cache_expire'  => 24 * HOUR_IN_SECONDS,
		'display_count' => 10,
		'debug_mode'    => false,
	];

	public static function option( $key ) {
		$option = get_option( self::OPTIONS_KEY, self::OPTIONS_DEFUALT );
		if ( is_array( $option ) && array_key_exists( $key, $option ) ) {
			return $option[ $key ];
		} else {
			return '';
		}
	}

	public function saved_options() {
		$options = get_option( self::OPTIONS_KEY, self::OPTIONS_DEFUALT );
		if ( ! is_array( $options ) || empty( $options ) ) {
			return false;
		}
		foreach ( array_keys( self::OPTIONS_DEFUALT ) as $key ) {
			if ( ! array_key_exists( $key, $options ) ) {
				return false;
			}
			if ( empty( $options[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}

}
