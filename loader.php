<?php

	require __DIR__ . '/modules/constants.php';
	require __DIR__ . '/modules/filters.php';
	require __DIR__ . '/modules/functions.php';
	require __DIR__ . '/modules/shortcode.php';

	if ( class_exists( 'WP_Widget' ) ) {
		require __DIR__ . '/modules/wp-widget.class.php';
	}
	if ( class_exists( 'WP_JSON_Posts' ) ) {
		require __DIR__ . '/modules/wp-rest-api.class.php';
	}
