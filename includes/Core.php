<?php
namespace digitalcube\SimpleGA4Ranking;

class Core {

	public function register_hooks() {
		add_action(
			'admin_menu', function() {
				$admin_view = new Admin\Options\View();
				add_menu_page(
					'Simple GA4 Ranking',
					'Simple GA4 Ranking',
					'administrator',
					'sga4_ranking',
					[ $admin_view, 'option_page' ],
					'dashicons-performance'
				);
			}
		);
		add_action(
			'plugins_loaded', function() {
				if ( class_exists( '\digitalcube\SimpleGA4Ranking\Admin\OAuth\Admin' ) ) {
					$admin = new Admin\OAuth\Admin();
					add_action( 'admin_init', [ $admin, 'admin_init' ] );
					add_action( 'admin_menu', [ $admin, 'admin_menu' ] );
				}
				if ( class_exists( '\digitalcube\SimpleGA4Ranking\Admin\OAuth\View' ) ) {
					add_action( 'admin_init', [ Admin\OAuth\View::class, 'register_setting_fields' ] );
				}
				if ( class_exists( '\digitalcube\SimpleGA4Ranking\Admin\Options\View' ) ) {
					add_action( 'admin_init', [ Admin\Options\View::class, 'register_setting_fields' ] );
				}
			}
		);
	}
}
