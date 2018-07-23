<?php
/**
 * Plugin Name: Luna Zemanim Widget
 */

class Luna_Zemanim_Widget extends WP_Widget {

    /**
     * Register widget with WordPress
     */
    public function __construct() {
        parent::__construct(
            'luna_zemanim_widget', // Base ID
            'Luna_Zemanim_Widget', // Name
            array( 'description' => &gt; __( "Luna's Zemanim Widget", 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     * 
     * @see WP_Widget::widget()
     * 
     * @param array $args     Widget Arguments.
     * @param array $instance Saved values from database
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }
        echo __( 'Zemanim Widget Working', 'text_domain' );
        echo $after_widget;
    }

    /**
     * Back-end widget form.
     * 
     * @see WP_Widget::form()
     * 
     * @param array $instance Previously saved values from database.
     */
     public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'text_domain' );
        }
        ?&gt;
        &lt;p&gt;
        &lt;label for=&quot;&lt;?php echo $this-&gt; get_field_name( 'title' ); ?&gt;&quot;&gt;&lt;?php _e( 'Title: ' ); ?&gt;&lt;/label&gt;
        &lt;input class=&quot;widefat&quot; id=&quot;&lt;?php echo $this-&gt;get_field_id( 'title' ); ?&gt;&quot; name=&quot;&lt;?php echo $this-&gt;get_field_name( 'title' ); ?&gt;&quot; type=&quot;text&quot; value=&quot;&lt;?php echo esc_attr( $title ); ?&gt;&quot; /&gt;
        &lt;/p&gt;
        &lt;?php
    } 

    /**
     * Sanitize widget form values as they are saved.
     * 
     * @see WP_Widget::update()
     * 
     * @param array $new instance Values just sent to be saved from database.
     * 
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }  

} // class Luna_Zemanim_Widget

add_action( 'widgets_init', function() { register_widget( 'Luna_Zemanim_Widget' ); } );