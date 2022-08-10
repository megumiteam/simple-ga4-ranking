<?php
namespace digitalcube\SimpleGA4Ranking\Admin\OAuth;

class Admin {

	const OPTIONS_KEY = 'sga4-ranking-oauth';

	const OPTIONS_DEFUALT = [
		'client_id'     => '',
		'client_secret' => '',
		'callback_url'  => '',
	];

	public function admin_init() {

		$request_to_google_auth = false;
		global $pagenow;
		if ( isset( $pagenow ) && 'options-general.php' === $pagenow && 'gapiwp-analytics' === filter_input( INPUT_GET, 'page' ) ) {
			$request_to_google_auth = true;
		}
		if ( ! $request_to_google_auth ) {
			return;
		}

		$do_auth = false;

		$nonce_check = (
			! empty( filter_input( INPUT_POST, 'sga4ranking_ga_auth' ) )
			&&
			wp_verify_nonce( filter_input( INPUT_POST, 'sga4ranking_ga_auth' ), 'sga4ranking_ga_auth' )
		);
		if ( $nonce_check ) {
			$do_auth = true;
		}

		$call_back = (
			! empty( filter_input( INPUT_GET, 'page' ) )
			&&
			! empty( filter_input( INPUT_GET, 'state' ) )
			&&
			! empty( filter_input( INPUT_GET, 'code' ) )
			&&
			! empty( filter_input( INPUT_GET, 'scope' ) )
		);
		if ( $call_back ) {
			$do_auth = true;
		}

		if ( $do_auth ) {
			// phpcs:ignore WordPress.VIP.SessionFunctionsUsage.session_session_status
			if ( PHP_SESSION_ACTIVE !== session_status() ) {
				// phpcs:ignore WordPress.VIP.SessionFunctionsUsage.session_session_start
				session_start();
			}
			$auth = new Auth();
			$auth->authorize();
		}
	}

	public function admin_menu() {
		add_options_page(
			__( 'Google Authentication', 'sga4ranking' ),
			__( 'Google Authentication', 'sga4ranking' ),
			'manage_options',
			'gapiwp-analytics',
			[ View::class, 'option_page' ]
		);
	}

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
