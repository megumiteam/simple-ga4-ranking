<?php

add_action( 'widgets_init', function() {
    return register_widget( 'WP_Widget_SGARanking' );
});

/**
 * Name: SGARanking Widget
 */
class WP_Widget_SGARanking extends WP_Widget {

    public function __construct() {
        $widget_ops = array(
            'classname'   => 'widget_simple_ga_ranking',
            'description' => __( "Show ranking the data from Google Analytics", '' ),
        );
        parent::__construct( 'simple_ga_rankig', __( 'Simple GA4 Ranking', '' ), $widget_ops );
    }

    public function widget( $args, $instance ) {
        extract($args);
        $title = apply_filters(
            'widget_title',
            empty( $instance['title'] ) ? '' : $instance['title'],
            $instance,
            $this->id_base
        );
        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        echo sga_ranking_shortcode(
            apply_filters( 'sga_widget_shortcode_argument', array() )
        );
        echo $after_widget;
    }

    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
        $title = $instance['title'];
        printf(
            '<p><label for="%s">%s <input class="widefat" id="%s" name="%s" type="text" value="%s" /></label></p>',
            $this->get_field_id('title'),
            __('Title:'),
            $this->get_field_id('title'),
            $this->get_field_name('title'),
            esc_attr($title)
        );
   }

   public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args(
            (array) $new_instance,
            array(
                'title' => '',
            )
        );
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
}
