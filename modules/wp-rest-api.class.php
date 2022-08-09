<?php

add_action( 'plugins_loaded', function() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if ( is_plugin_active( 'json-rest-api/plugin.php' ) && ( '3.9.2' <= get_bloginfo( 'version' ) && '4.2' > get_bloginfo( 'version' ) ) ) {
        add_action( 'wp_json_server_before_serve', function ( $server ) {
            // Ranking
            $wp_json_ranking = new WP_JSON_SGARanking( $server );
            add_filter( 'json_endpoints', array( $wp_json_ranking, 'register_routes' ), 1 );
        }, 10, 1);
    }
});

/**
 * Name: SGARanking Endpoint
 */
class WP_JSON_SGARanking extends WP_JSON_Posts {
	/**
	 * Register the ranking-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$ranking_routes = array(
			'/ranking' => array(
				array(
					array(
						$this,
						'get_ranking'
					),
					WP_JSON_Server::READABLE
				),
			),
		);
		return array_merge( $routes, $ranking_routes );
	}

	/**
	 * Retrieve ranking
	 *
	 * Overrides the $type to set to 'post', then passes through to the post
	 * endpoints.
	 *
	 * @see WP_JSON_Posts::get_posts()
	 */
	public function get_ranking( $filter = array(), $context = 'view' ) {
		$ids        = sga_ranking_get_date( $filter );
		$posts_list = array();
		foreach ( $ids as $id ) {
			$posts_list[] = get_post( $id );
		}
		$response = new WP_JSON_Response();

		if ( ! $posts_list ) {
			$response->set_data( array() );
			return $response;
		}
		// holds all the posts data
		$struct = array();
		$response->header(
			'Last-Modified',
			mysql2date( 'D, d M Y H:i:s', get_lastpostmodified( 'GMT' ), 0 ).' GMT'
		);
		foreach ( $posts_list as $post ) {
			$post = get_object_vars( $post );
			// Do we have permission to read this post?
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}
			$response->link_header(
				'item',
				json_url( '/posts/' . $post['ID'] ),
				array(
					'title' => $post['post_title'],
				)
			);
			$post_data = $this->prepare_post( $post, $context );
			if ( is_wp_error( $post_data ) ) {
				continue;
			}
			$struct[] = $post_data;
		}
		$response->set_data( $struct );
		return $response;
	}
}
