<?php
/**
 * Plugin Name: Simple GA 4 Ranking
 */


register_activation_hook(
	__FILE__,
	'sga4ranking_activation_hook'
);

function sga4ranking_activation_hook() {
	require_once __DIR__ . '/sga_ranking_migration.php';
	sga_ranking_migration();
}

if ( ! shortcode_exists( 'sga_ranking' ) ) :
	require_once 'vendor/autoload.php';
	require_once 'loader.php';
	$core = new \digitalcube\SimpleGA4Ranking\Core();
	$core->register_hooks();
endif;
