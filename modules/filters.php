<?php

/**
 * Name: SGARanking Exclude post
 */
add_filter(
	'sga_ranking_exclude_post',
	function ( $exclude = false, $post_id = 0, $url = '' ) {
		if ( false !== strpos( $url, 'preview=true' ) ) {
			$exclude = true;
		}
		if ( 0 === (int) $post_id ) {
			$exclude = true;
		}
		return $exclude;
	},
	1, 3
);

/**
 * Name: SGARanking Debug Mode
 */
add_filter(
	'sga_ranking_debug_mode',
	function ( $debug_mode = false ) {
		$options = get_option( SGA_RANKING_OPTION_NAME );
		if ( defined( 'SGA_RANKING_TEST_MODE' ) && true === SGA_RANKING_TEST_MODE ) {
			$debug_mode = true;
		}
		if ( isset( $options['debug_mode'] ) && 1 === (int) $options['debug_mode'] ) {
			$debug_mode = true;
		}
		return $debug_mode;
	},
	1
);

/**
 * Name: SGARanking Get Dummy Data
 */
add_filter(
	'sga_ranking_dummy_data',
	function ( $ids, $args = [], $options = [] ) {
		global $wpdb;
		$display_count = apply_filters( 'sga_ranking_default_display_count', 10 );
		if ( isset( $options['display_count'] ) ) {
			$display_count = (int) $options['display_count'];
		}
		if ( !empty( $options['post_type'] ) ) {
			$post_type = implode( ',', array_map( fn( $v ) => $wpdb->prepare( '%s', trim( $v ) ), explode( ',', $options['post_type'] ) ) );
		} else {
			$post_type = $wpdb->prepare( '%s', 'post' );
		}
		$rets     = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( {$post_type} ) AND post_status = %s ORDER BY RAND() LIMIT 0, %d",
				'publish',
				$display_count * 10
			)
		);
		$post_ids = [];
		$cnt      = 0;
		foreach ( $rets as $ret ) {
			if ( $cnt >= $display_count ) {
				break;
			}
			$exclude = apply_filters( 'sga_ranking_exclude_post', false, $ret->ID, '' );
			if ( ! $exclude ) {
				$post_ids[] = $ret->ID;
				$cnt++;
			}
		}
		return $post_ids;
	},
	1, 3
);

/**
 * Name: SGARanking URL to PostID
 */
add_filter(
	'sga_ranking_url_to_postid',
	function ( $post_id, $url ) {
		if ( 0 === $post_id ) {
			$post_id = url_to_postid( esc_url( $url ) );
		}
		return $post_id;
	},
	10, 2
);
