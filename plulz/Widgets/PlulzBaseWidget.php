<?php

/**
 * This class is responsible for creating and registering widgets on Wordpress
 * it extends the default WP_Widget class
 *
 * To create new widgets just copy and past a new file with the new widget name
 *
 */

if( !class_exists('PlulzBaseWidget') )
{
    class PlulzBaseWidget extends WP_Widget
    {
        /**
         * The constructor
         *
         * @see WP_Widget::__construct
         * @param $args
         */
        public function __construct()
        {
            $baseID         =   'id';
            $name           =   'name';
            $description    =   'description';

		    parent::WP_Widget( $baseID, $name, array( 'description' => $description ) );
	    }

        /**
         * Outputs the options form on admin side of widget
         *
         * @see WP_Widget::form
         * @param $instance
         * @return void
         */
        public function form( $instance )
        {
            if ( $instance )
                $title = esc_attr( $instance[ 'title' ] );
            else
                $title = __( 'New title', 'text_domain' );

            $output =   "<p><label for='" . $this->get_field_id('title') . "'>" . _e('Title:') . "</label>";
            $output .=  "<input class='widefat' id='" . $this->get_field_id('title') . "' name='" . $this->get_field_name('title') . "' type='text' value='" . $title . "' />";
            $output .=  "</p>";
            
            echo $output;
        }

        /**
         * Processes widget options to be saved
         *
         * @see WP_Widget::update
         * @param $new_instance
         * @param $old_instance
         * @return array
         */
        public function update( $new_instance, $old_instance )
        {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            return $instance;
        }

        /**
         *  Method that creates the output of the widget
         *
         * @see WP_Widget::widget
         * @param $args
         * @param $instance
         */
        public function widget( $args, $instance )
        {
            extract( $args );

            $title = apply_filters( 'widget_title', $instance['title'] );

            echo $before_widget;

            if ( $title )
                echo $before_title . $title . $after_title;

            // Do stuff
            echo "Hello, World!";
            
            echo $after_widget;
        }

    }
}
