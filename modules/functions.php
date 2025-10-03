<?php

if ( ! function_exists( 'sga_ranking_ids' ) ) :
function sga_ranking_ids( $args = array(), $get_with_page_views = false ) {

	$options = get_option( SGA_RANKING_OPTION_NAME );
	$wp_date = function( $format, $timestamp = null, $timezone = null ) {
		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $format, $timestamp, $timezone );
		} else {
			if ( null === $timestamp ) {
				$timestamp = time();
			}
			return date_i18n( $format, $timestamp, ! isset( $timezone ) );
		}
	};
	if ( ! $options || ! is_array( $options ) ) {
		$options = array();
	}
	// get args
	$r = wp_parse_args( $args );
	foreach ( $r as $key => $value ) {
		$options[$key] = $value;
	}
	$options['debug_mode'] = isset( $options['debug_mode'] ) && 1 === (int) $options['debug_mode'];
	foreach ( SGA_RANKING_DEFAULT as $key => $default ) {
		if ( ! isset( $options[$key] ) || empty( $options[$key] ) ) {
			$options[$key] = apply_filters( 'sga_ranking_default_' . $key, $default );
		}
	}
	$force_update  = isset( $r['force_update'] ) ? $r['force_update'] : false;
	$display_count = (int) $options['display_count'];
	$date_now      = $wp_date( 'Y-m-d H:i:s' );
	// cache expire time
	$cache_expires = (int) apply_filters( 'sga_ranking_cache_expire', $options['cache_expire'] );
	// post limit
	$post_limit    = (int) apply_filters( 'sga_ranking_limit_filter', 100 );
	// get start date - end date
	$date_format = 'Y-m-d';
	$date_end    = $wp_date( $date_format );
	$date_start  = strtotime( $date_end . '-' . $options['period'] . 'day' );
	$options['start_date'] = $wp_date( $date_format, $date_start );
	$options['end_date']   = $date_end;
	// build transient key
	$transient_key = sprintf( 'sga_ranking_%d_%d', $options['period'], $display_count );
	if ( !empty($r) ) {
		if ( array_key_exists( 'post_type', $r ) ) {
			$transient_key .= '_post_type_' . $r['post_type'];
		}
		if ( array_key_exists( 'exclude_post_type', $r ) ) {
			$transient_key .= '_exclude_post_type_' . $r['exclude_post_type'];
		}

		foreach ( $r as $k => $v ) {
			if ( strpos( $k, '__in' ) !== false ) {
				$transient_key .= sprintf( '_%s_%s', $k , $r[$k] );
			}
			if ( strpos( $k, '__not_in' ) !== false ) {
				$transient_key .= sprintf( '_%s_%s', $k , $r[$k] );
			}
		}
	}
	$transient_key  = 'sga_' . substr( md5( $transient_key ), 0, 30 );
	if ( isset( $options['transient_key_suffix'] ) ) {
		$transient_key .= '_' . $options['transient_key_suffix'];
	}

	// Exclusive processing
	$processing = $force_update ? false : get_transient( "sga_ranking_{$transient_key}" );
	$ids = ( false !== $processing ) ? get_transient( $transient_key ) : false;
	if ( false === $processing || false === $ids ) {
		set_transient(
			"sga_ranking_{$transient_key}",
			[
				'key'     => $transient_key,
				'options' => $options,
				'args'    => $r,
				'limit'   => $post_limit,
				'date'    => $date_now,
				'expires' => $cache_expires,
			],
			$cache_expires
		);
	}
	// for debuging
	$transient_key_result_keys = 'sga_ranking_result_keys';
	$sga_ranking_result_keys_org = get_transient( $transient_key_result_keys );
	$sga_ranking_result_keys = [
		'results'    => [],
		'ga_results' => [],
		'update'     => $date_now,
	];
	$sga_ranking_result_keys_update = false;
	if ( $sga_ranking_result_keys_org && is_array( $sga_ranking_result_keys_org ) ) {
		if ( ! isset( $sga_ranking_result_keys_org['results'][$transient_key] ) ) {
			$sga_ranking_result_keys['results'][$transient_key] = (array) $r;
			$sga_ranking_result_keys['results'][$transient_key]['update'] = $date_now;
			$sga_ranking_result_keys_update = true;
		}
	} else {
		$sga_ranking_result_keys['results'][$transient_key] = (array) $r;
		$sga_ranking_result_keys['results'][$transient_key]['update'] = $date_now;
		$sga_ranking_result_keys_update = true;
	}

	// Debug Mode
	$debug_mode = apply_filters( 'sga_ranking_debug_mode', $options['debug_mode'] );
	if ( false === $ids && $debug_mode ) {
		$ids = apply_filters( 'sga_ranking_dummy_data', array(), $args, $options );
		if ( ! empty( $ids ) ) {
			set_transient( $transient_key, $ids, $cache_expires * 2 );
		}
	}

	// get GA ranking Data
	if ( false !== $ids ) {
		// from cache
		$post_ids = $ids;
	} else {
		// from Google Analytics API
		$transient_key_ga_fetch = sprintf(
			'%s_%s_%d',
			$options['start_date'],
			$options['end_date'],
			$post_limit
		);
		$transient_key_ga_fetch = 'ga_' . substr( md5( $transient_key_ga_fetch ), 0, 30 );
		if ( isset( $options['transient_key_suffix'] ) ) {
			$transient_key_ga_fetch .= '_' . $options['transient_key_suffix'];
		}
		$results = $force_update ? false : get_transient( $transient_key_ga_fetch );

		// for debugging
		$sga_ranking_result_keys['ga_results'][$transient_key_ga_fetch]['start']  = $options['start_date'];
		$sga_ranking_result_keys['ga_results'][$transient_key_ga_fetch]['end']    = $options['end_date'];

		if ( ! $results ) {
			$property_id = \digitalcube\SimpleGA4Ranking\Admin\OAuth\Admin::option( 'property_id' );
			$simple_ga_ranking = new \digitalcube\SimpleGA4Ranking\Analytics( $property_id );
			$ga_args = array(
				'start-index' => 1,
				'max-results' => $post_limit,
			);
			$results = $simple_ga_ranking->fetch(
				[
					$options['start_date'],
					$options['end_date'],
					$ga_args,
				]
			);

			$sga_ranking_result_keys['ga_results'][$transient_key_ga_fetch] = $ga_args;
			if ( ! empty( $results ) && ! is_wp_error( $results ) ) {
				set_transient( $transient_key_ga_fetch, $results, $cache_expires * 2 );

				// for debugging
				$sga_ranking_result_keys['ga_results'][$transient_key_ga_fetch]['update'] = $date_now;
				$sga_ranking_result_keys_update = true;
			}
		}

		if ( ! empty( $results ) && ! is_wp_error( $results ) && is_array( $results->rows ) ) {
			$post_ids = array();
			$cnt = 0;
			foreach($results->rows as $result) {
				if ( $cnt >= $display_count ) {
					break;
				}

				// Get Post ID from URL
				$url = isset( $result[0] ) ? $result[0] : '';
				$post_id = sga_ranking_url_to_postid( esc_url( $url ) );
				if ( in_array( $post_id, $post_ids ) ) {
					continue;
				}

				$exclude = apply_filters( 'sga_ranking_exclude_post', false, $post_id, $url );
				if ( $exclude ) {
					continue;
				}

				$post_obj = get_post( $post_id );
				if ( !is_object($post_obj) || $post_obj->post_status != 'publish' ) {
					continue;
				}

				if ( !empty($r) ) {
					if ( array_key_exists( 'post_type', $r ) && is_string( $r['post_type'] ) ) {
						$post_type = explode( ',', $r['post_type'] );
						if ( empty( $post_type ) || !in_array( $post_obj->post_type, $post_type ) ){
							continue;
						}
					}

					if ( array_key_exists( 'exclude_post_type', $r ) ) {
						$exclude_post_type = explode( ',', $r['exclude_post_type'] );
						if ( !empty ( $exclude_post_type ) && in_array( $post_obj->post_type, $exclude_post_type ) ) {
							continue;
						}
					}

					$tax_in_flg = true;
					foreach ( $r as $key => $val ) {
						if ( strpos( $key, '__in' ) !== false ) {
							$tax = str_replace( '__in', '', $key );
							$tax_in = explode( ',', $r[$key] );
							$post_terms = get_the_terms( $post_id, $tax );
							$tax_in_flg = false;
							if ( !empty( $post_terms ) && is_array( $post_terms ) ) {
								foreach ( $post_terms as $post_term ) {
									if ( in_array( $post_term->slug, $tax_in ) ) {
										$tax_in_flg = true;
									}
								}
							}
							break;
						}
					}
					if ( !$tax_in_flg ) {
						continue;
					}

					$tax_not_in_flg = true;
					foreach ( $r as $key => $val ) {
						if ( strpos( $key, '__not_in' ) !== false ) {
							$tax = str_replace( '__not_in', '', $key );
							$tax_in = explode( ',', $r[$key] );
							$post_terms = get_the_terms( $post_id, $tax );
							$tax_not_in_flg = true;
							if ( !empty( $post_terms ) && is_array( $post_terms ) ) {
								foreach ( $post_terms as $post_term ) {
									if ( in_array( $post_term->slug, $tax_in ) ) {
										$tax_not_in_flg = false;
									}
								}
							}
							break;
						}
					}
					if ( !$tax_not_in_flg ) {
						continue;
					}
				}

				if ( true === $get_with_page_views ) {
					$post_ids[] = array(
						$post_id,
						(int) $result[1],
					);
				} else {
					$post_ids[] = $post_id;
				}

				$cnt++;
			}
			set_transient( $transient_key, $post_ids, $cache_expires * 2 );

		} else {
			$post_ids = apply_filters( 'sga_ranking_dummy_data_for_error', array(), $options );
			if ( ! is_admin() && is_super_admin() ) {
				echo '<pre>';
				var_dump( $results );
				echo '</pre>';
			}
		}
	}

	// for debugging
	if ( $sga_ranking_result_keys_update ) {
		foreach ( ['results', 'ga_results'] as $index ) {
			if ( is_bool( $sga_ranking_result_keys_org ) || ! is_array( $sga_ranking_result_keys_org[$index] ) ) {
				continue;
			}
			foreach ( $sga_ranking_result_keys_org[$index] as $transient_key => $value ) {
				if ( ! isset( $sga_ranking_result_keys[$index][$transient_key] ) ) {
					if ( get_transient( $transient_key ) ) {
						$sga_ranking_result_keys[$index][$transient_key] = $value;
					}
				}
			}
		}
		$sga_ranking_result_keys['update'] = $date_now;
		set_transient(
			$transient_key_result_keys,
			$sga_ranking_result_keys,
			(int)($cache_expires / 2)
		);
	}

	return apply_filters( 'sga_ranking_ids', $post_ids, $args, $options );
}
endif;

if ( ! function_exists( 'sga_ranking_get_date' ) ) :
function sga_ranking_get_date( $args = array() ) {
	return sga_ranking_ids( $args );
}
endif;

if ( ! function_exists( 'sga_url_to_postid' ) ) :
function sga_url_to_postid( $url ) {
	return sga_ranking_url_to_postid( $url );
}
endif;

if ( ! function_exists( 'sga_ranking_url_to_postid' ) ) :
function sga_ranking_url_to_postid( $url ) {
	global $wp, $wp_rewrite;

	$post_id = 0;
	$url     = apply_filters( 'url_to_postid', $url );

	// First, check to see if there is a 'p=N' or 'page_id=N' to match against
	if ( preg_match( '#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values ) )    {
		$post_id = absint($values[2]);
	}

	// Check to see if we are using rewrite rules
	$rewrite = $wp_rewrite->wp_rewrite_rules();
	if ( $rewrite && ! empty( $url ) && ! $post_id ) {
		$post_id = 0;

		// Get rid of the #anchor
		$url_split = explode( '#', $url );
		$url = $url_split[0];

		// Get rid of URL ?query=string
		$url_split = explode( '?', $url );
		$url = $url_split[0];

		// Add 'www.' if it is absent and should be there
		if ( false !== strpos( home_url(), '://www.' ) && false === strpos( $url, '://www.' ) ) {
			$url = str_replace( '://', '://www.', $url );
		}

		// Strip 'www.' if it is present and shouldn't be
		if ( false === strpos( home_url(), '://www.' ) ) {
			$url = str_replace( '://www.', '://', $url );
		}

		// Strip 'index.php/' if we're not using path info permalinks
		if ( ! $wp_rewrite->using_index_permalinks() ) {
			$url = str_replace( 'index.php/', '', $url );
		}

		if ( false !== strpos( $url, home_url() ) ) {
			// Chop off http://example.com
			$url = str_replace( home_url(), '', $url );
		} else {
			// Chop off /path/to/blog
			$home_path = parse_url(home_url());
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
			$url = str_replace( $home_path, '', $url );
		}

		// Trim leading and lagging slashes
		$url = trim($url, '/');

		$request = $url;
		// Look for matches.
		$request_match = $request;
		foreach ( (array) $rewrite as $match => $query) {
			// If the requesting file is the anchor of the match, prepend it
			// to the path info.
			if ( ! empty( $url ) && ( $url != $request ) && ( strpos( $match, $url ) === 0) ) {
				$request_match = $url . '/' . $request;
			}

			if ( preg_match( "#^$match#", $request_match, $matches ) ) {
				// Got a match.
				// Trim the query of everything up to the '?'.
				$query = preg_replace( "!^.+\?!", '', $query );

				// Substitute the substring matches into the query.
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );

				// Filter out non-public query vars
				parse_str( $query, $query_vars );
				$query = array();
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array( $key, $wp->public_query_vars ) )
						$query[$key] = $value;
				}

				// Taken from class-wp.php
				foreach ( $GLOBALS['wp_post_types'] as $post_type => $t ) {
					if ( $t->query_var ) {
						$post_type_query_vars[$t->query_var] = $post_type;
					}
				}

				foreach ( $wp->public_query_vars as $wpvar ) {
					if ( isset( $wp->extra_query_vars[$wpvar] ) ) {
						$query[$wpvar] = $wp->extra_query_vars[$wpvar];
					} elseif ( isset( $_POST[$wpvar] ) ) {
						$query[$wpvar] = $_POST[$wpvar];
					} elseif ( isset( $_GET[$wpvar] ) ) {
						$query[$wpvar] = $_GET[$wpvar];
					} elseif ( isset( $query_vars[$wpvar] ) ) {
						$query[$wpvar] = $query_vars[$wpvar];
					}

					if ( !empty( $query[$wpvar] ) ) {
						if ( ! is_array( $query[$wpvar] ) ) {
							$query[$wpvar] = (string) $query[$wpvar];
						} else {
							foreach ( $query[$wpvar] as $vkey => $v ) {
								if ( !is_object( $v ) ) {
									$query[$wpvar][$vkey] = (string) $v;
								}
							}
						}

						if ( isset($post_type_query_vars[$wpvar] ) ) {
							$query['post_type'] = $post_type_query_vars[$wpvar];
							$query['name'] = $query[$wpvar];
						}
					}
				}

				// Do the query
				$query = new WP_Query($query);
				if ( !empty($query->posts) && $query->is_singular ) {
					$post_id = (int) $query->post->ID;
				}
			}
		}
	}
	return apply_filters( 'sga_ranking_url_to_postid', $post_id, $url );
}
endif;
