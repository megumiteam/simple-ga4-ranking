<?php
namespace digitalcube\SimpleGA4Ranking\Admin\Cache;

class Admin {

	public static function delete_cache() {
		$cache_key = filter_input( INPUT_POST, 'cache_key' );
		if ( false === strpos( $cache_key, 'sga_' ) ) {
			wp_die( 'Invalid Cache Key' );
		}
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! wp_verify_nonce( $nonce, 'delete_cache_' . $cache_key ) ) {
			wp_die( 'Nonce Error' );
		}
		delete_transient( $cache_key );

		$cache         = get_transient( 'sga_ranking_result_keys' );
		$cache_results = isset( $cache['results'] ) ? $cache['results'] : [];
		if ( array_key_exists( $cache_key, $cache_results ) ) {
			$options = get_option( SGA_RANKING_OPTION_NAME );
			if ( ! $options || ! is_array( $options ) ) {
				$options = [];
			}
			$cache_expires = (int) apply_filters( 'sga_ranking_cache_expire', $options['cache_expire'] );
			unset( $cache_results[ $cache_key ] );
			$cache['results'] = $cache_results;
			set_transient(
				'sga_ranking_result_keys',
				$cache,
				(int) ( $cache_expires / 2 )
			);
		}
	}

}
