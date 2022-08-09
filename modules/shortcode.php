<?php

function sga_ranking_shortcode( $atts ) {

	$ids = sga_ranking_ids( $atts );
	if ( ! is_array( $ids ) || empty( $ids ) ) {
		return apply_filters( 'sga_ranking_shortcode_empty', '' );
	}

	$cnt    = 1;
	$output = '<ol class="sga-ranking">';
	foreach ( $ids as $id ) {
		$post_permalink = get_permalink( $id );
		$post_title     = get_the_title( $id );
		$output        .= sprintf(
			'<li class="sga-ranking-list sga-ranking-list-%d">%s<a href="%s" title="%s">%s</a>%s</li>',
			$cnt,
			apply_filters( 'sga_ranking_before_title', '', $id, $cnt ),
			esc_url( $post_permalink ),
			esc_attr( $post_title ),
			esc_html( $post_title ),
			apply_filters( 'sga_ranking_after_title', '', $id, $cnt )
		);
		$cnt++;
	}
	$output .= '</ol>';
	return apply_filters( 'sga_ranking_shortcode', $output, $ids );

}
add_shortcode( 'sga_ranking', function( $atts ) {
	return sga_ranking_shortcode( $atts );
} );
