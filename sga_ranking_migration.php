<?php

	function sga_ranking_migration() {

		// Deactivate Simple GA Ranking if activated.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'simple-ga-ranking/simple-ga-ranking.php' ) ) {

			deactivate_plugins( 'simple-ga-ranking/simple-ga-ranking.php', true );

			// 基本設定の移行
			$sga_options = get_option( 'sga_ranking_options', array() );
			$options = array();
			$options['period']        = ( array_key_exists( 'period', $sga_options ) ) ? $sga_options['period'] : 30;
			$options['display_count'] = ( array_key_exists( 'display_count', $sga_options ) ) ? $sga_options['display_count'] : 10;
			$options['debug_mode']    = ( array_key_exists( 'debug_mode', $sga_options ) ) ? ( '1' == $sga_options['debug_mode'] ) : false;
			$options['cache_expire']  = 24 * HOUR_IN_SECONDS;
			update_option( 'sga4-ranking-options', $options );

			// OAuth設定の移行
			$gapiwp_key    = get_option( 'gapiwp_key', '' );
			$gapiwp_secret = get_option( 'gapiwp_secret', '' );
			if ( ! empty( $gapiwp_key ) && ! empty( $gapiwp_secret ) ) {
				$option = array();
				$option['client_id']     = $gapiwp_key;
				$option['client_secret'] = $gapiwp_secret;
				$option['callback_url']  = admin_url( 'options-general.php?page=gapiwp-analytics' );
				update_option( 'sga4-ranking-oauth', $option );
			}

		}

	}
