<?php

	if ( '1' === getenv( 'IS_WP_ENV' ) ) {

		if ( ! is_dir( __DIR__ .'/tmp' ) ) {
			die(
				'------------------------------' . PHP_EOL .
				' There is no test suite.' . PHP_EOL .
				' Please install test suite at first. ' . PHP_EOL .
				' run `composer setup-phpunit` on this plugin directly.' . PHP_EOL .
				'------------------------------' . PHP_EOL
			);
			exit;
		}

		require dirname( dirname( dirname( __FILE__ ) ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
		require dirname( dirname( dirname( __FILE__ ) ) ) . '/vendor/autoload.php';
		define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/tmp/wp-tests-config.php' );
		require 'tmp/includes/functions.php';
		require 'tmp/includes/bootstrap.php';

	} else {

		require dirname( dirname( __DIR__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
		require dirname( dirname( dirname( __FILE__ ) ) ) . '/vendor/autoload.php';

		$_tests_dir = getenv( 'WP_TESTS_DIR' );

		// See temp dir.
		if ( ! $_tests_dir ) {
			$_try_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
			if ( file_exists( $_try_tests_dir . '/includes/functions.php' ) ) {
				$_tests_dir = $_try_tests_dir;
			}
			unset( $_try_tests_dir );
		}

		// Next, try the WP_PHPUNIT composer package.
		if ( ! $_tests_dir ) {
			$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
		}

		// See if we're installed inside an existing WP dev instance.
		if ( ! $_tests_dir ) {
			$_try_tests_dir = __DIR__ . '/../../../../../tests/phpunit';
			if ( file_exists( $_try_tests_dir . '/includes/functions.php' ) ) {
				$_tests_dir = $_try_tests_dir;
			}
		}

		// Fallback.
		if ( ! $_tests_dir ) {
			$_tests_dir = '/tmp/wordpress-tests-lib';
		}

		if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
			echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore
			exit( 1 );
		}
		// Give access to tests_add_filter() function.
		require_once $_tests_dir . '/includes/functions.php';

		function _manually_load_plugin() {
		}
		tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

		// Start up the WP testing environment.
		require $_tests_dir . '/includes/bootstrap.php';

	}
