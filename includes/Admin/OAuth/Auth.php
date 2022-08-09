<?php
namespace digitalcube\SimpleGA4Ranking\Admin\OAuth;

class Auth {

	const AUTH_URL  = 'https://accounts.google.com/o/oauth2/auth';
	const TOKEN_URL = 'https://oauth2.googleapis.com/token';
	const API_SCOPE = 'https://www.googleapis.com/auth/analytics';

	const ACCESS_TOKEN_TRANSIENT_KEY = 'sga4ranking-access-token';
	const REFRESH_TOKEN_OPTION_KEY   = 'sga4ranking-refresh-token';

	public function authorize() {
		delete_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
		delete_option( self::REFRESH_TOKEN_OPTION_KEY );
		$code = $this->get_auth_code();
		$this->get_access_token( $code, false );
		$auth_url = admin_url( '/options-general.php?page=gapiwp-analytics' );
		wp_safe_redirect( $auth_url, 301 );
		exit;
	}

	public function get_auth_code() {
		if ( ! empty( filter_input( INPUT_GET, 'code' ) ) ) {
			// phpcs:ignore WordPress.VIP.SessionVariableUsage.SessionVarsProhibited
			if ( $_SESSION['oauth_state'] !== filter_input( INPUT_GET, 'state' ) ) {
				wp_die( 'Bad Request' );
				exit;
			}
			// phpcs:ignore WordPress.VIP.SessionVariableUsage.SessionVarsProhibited
			unset( $_SESSION['oauth_state'] );
			return filter_input( INPUT_GET, 'code' );
		}

		$state = base64_encode( wp_generate_password( 12, true, true ) );

		// phpcs:ignore WordPress.VIP.SessionVariableUsage.SessionVarsProhibited
		$_SESSION['oauth_state'] = $state;

		$client_id    = Admin::option( 'client_id' );
		$callback_url = Admin::option( 'callback_url' );
		$auth_url     = add_query_arg(
			[
				'response_type'   => 'code',
				'client_id'       => $client_id,
				'redirect_uri'    => rawurlencode( $callback_url ),
				'scope'           => self::API_SCOPE,
				'state'           => $state,
				'approval_prompt' => 'force',
				'access_type'     => 'offline',
			],
			self::AUTH_URL
		);
		add_filter(
			'allowed_redirect_hosts', function ( $allowed ) {
				$allowed[] = wp_parse_url( self::AUTH_URL, PHP_URL_HOST );
				return $allowed;
			}
		);
		wp_safe_redirect( $auth_url, 301 );
		exit;
	}

	public function get_access_token( $code = '', $use_cache = true ) {
		if ( $use_cache ) {
			$token = get_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
			if ( ! empty( $token ) ) {
				return $token;
			}
		}

		$client_id     = Admin::option( 'client_id' );
		$client_secret = Admin::option( 'client_secret' );
		$callback_url  = Admin::option( 'callback_url' );

		$refresh_token = get_option( self::REFRESH_TOKEN_OPTION_KEY );
		if ( empty( $refresh_token ) ) {
			$res = wp_remote_post(
				self::TOKEN_URL,
				[
					'headers' => [
						'Content-Type: application/x-www-form-urlencoded',
					],
					'body' => [
						'grant_type'    => 'authorization_code',
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
						'code'          => $code,
						'redirect_uri'  => $callback_url,
					],
				]
			);
		} else {
			$res = wp_remote_post(
				self::TOKEN_URL,
				[
					'headers' => [
						'Content-Type: application/x-www-form-urlencoded',
					],
					'body' => [
						'grant_type'    => 'refresh_token',
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
						'refresh_token' => $refresh_token,
						'redirect_uri'  => $callback_url,
					],
				]
			);
		}
		if ( 200 === wp_remote_retrieve_response_code( $res ) ) {
			$json  = json_decode( wp_remote_retrieve_body( $res ) );
			$token = $json->access_token;
			set_transient( self::ACCESS_TOKEN_TRANSIENT_KEY, $token, $json->expires_in );
			if ( property_exists( $json, 'refresh_token' ) ) {
				update_option( self::REFRESH_TOKEN_OPTION_KEY, $json->refresh_token );
			}
			return $token;
		} else {
			return null;
		}
	}

	public function get_refresh_token() {
		return get_option( self::REFRESH_TOKEN_OPTION_KEY );
	}

	public function authorized() {
		return ( true !== empty( get_transient( self::ACCESS_TOKEN_TRANSIENT_KEY ) ) );
	}

}
