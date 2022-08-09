<?php

if ( ! defined( 'SGA_RANKING_OPTION_NAME' ) ) {
	define( 'SGA_RANKING_OPTION_NAME', 'sga4-ranking-options' );
}
if ( ! defined( 'SGA_RANKING_DEFAULT' ) ) {
	define(
		'SGA_RANKING_DEFAULT', [
			'period'        => 30,        
			'cache_expire'  => 24 * HOUR_IN_SECONDS,
			'display_count' => 10,
			'debug_mode'    => 0,		
		]
	);
}
