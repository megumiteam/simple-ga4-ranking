<?php
/*
Plugin Name: Simple GA 4 Ranking
Plugin URI: https://digitalcube.jp
Description: Ranking plugin using data from google analytics.
Author: Digitalcube
Author URI: https://digitalcube.jp
Version: 0.0.1
Domain Path: /languages
Text Domain: sga4ranking
Requires at least: 5.9
Requires PHP:　7.4

Copyright 2018 - 2022 digitalcube (email : info@digitalcube.jp)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

load_plugin_textdomain(
	'sga4ranking',
	 false,
	 dirname( plugin_basename( __FILE__ ) ) . '/languages'
);

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
