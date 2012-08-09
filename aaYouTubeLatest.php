<?php
/*
Plugin Name: YouTube Latest
Plugin URI: http://www.ambientage.com/blog/youtube-latest/
Description: Gets the latest youtube video from your channel (based on your username) and displays them in a widget. Gives you the option to set width &amp; height of video.
Version: 0.2
Author: Ambient Age
Author URI: http://www.ambientage.com

Copyright 2012 Ambient Age (email : plugins@ambientage.com)

*/
?>
<?php


/**
 * Aa_YouTubeLatest widget class
 *
 */
class Aa_YouTubeLatest extends WP_Widget {

	function Aa_YouTubeLatest() {
		$widget_ops = array('classname' => 'widget-youtube-latest',
                                    'description' => __( "The latest YouTube video in your channel. (Default width: 640px, height: 385px)") );
		$this->WP_Widget('aa_youtube_latest', __('YouTube Channel Latest'), $widget_ops);
		$this->alt_option_name = 'widget_youtube_latest';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );

                // Register a sidebar for it...
                register_sidebar( array(
                        'name' => __( 'Latest Video Area'),
                        'id' => 'latest-video-area',
                        'description' => __( 'Area for YouTube Plugin (if you need it, create a dynamic-sidbar called \'latest-video-area\' in your theme)'),
                        'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
                        'after_widget' => '</div>',
                        'before_title' => '<h3 class="widget-title">',
                        'after_title' => '</h3>',
                ) );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('aa_youtube_latest', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['aa_youtube_title']) ? __('YouTube Latest') : $instance['aa_youtube_title']);
                $username = $instance['aa_youtube_username'];
                $width = $instance['aa_youtube_width'];
                $height = $instance['aa_youtube_height'];

                $profileLink = '<a id="aa-youtube-profile-link" title="See more at our YouTube Channel" href="http://www.youtube.com/user/'. $username . '">See our channel</a>';
?>
		<?php echo $before_widget; ?>

                <?php if ( $title ){
                        echo $before_title . $title . $profileLink . $after_title;

                    }
                
                    /**
                     * @todo Make this an option
                     * @var number of vids to display
                     */
                    $videoCount = 1; 
                    ?>

                    <div id="aa-youTubeLatest">
                        <?php
                            include_once(ABSPATH.WPINC.'/class-simplepie.php'); // path to include script
                            $xmlLoc = 'http://gdata.youtube.com/feeds/api/users/' . $username . '/uploads';
                            $feed = fetch_feed($xmlLoc); 
                       
                            if (!is_wp_error($feed)) :
                                $rss_items = $feed->get_items();
                                $counter = 0;

                                foreach ( $rss_items as $item ) :
                                    if( ++$counter > $videoCount ){
                                        break;
                                    }
                                    $videoLink = $item->get_link();       // http://www.youtube.com/watch?v=3ptt3lnoKhI&feature=youtube_gdata
                                    $videoStart = substr($videoLink, (strpos($videoLink, 'v=') + 2));
                                    $vidCode = substr($videoStart, 0, strpos($videoStart, '&'));

                                    $embedUrl = 'http://www.youtube.com/v/'. $vidCode;
                                    
                                ?>
                                    <iframe <?php echo ( isset( $width ) && !empty( $width) ? 'width="' . $width . '"' : '' ); ?> 
                                        <?php echo ( isset( $height ) && !empty( $height) ? 'height="' . $height . '"' : '' ); ?>
                                             src="https://www.youtube.com/embed/<?php echo $vidCode; ?>?rel=0" frameborder="0" allowfullscreen></iframe>
                                    
                                    <?php 
                                    /*  Old object-baseed embed code
                                    <object width="<?php echo $width; ?>" height="<?php echo $height; ?>">
                                         <param name="movie" value="<?php echo $embedUrl; ?>"></param>
                                         <param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
                                         <embed src="<?php echo $embedUrl; ?>" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="<?php echo $width; ?>" height="<?php echo $height; ?>">
                                         </embed>
                                     </object>
                                      */ ?>
                                 <?php endforeach; ?>
                        <?php else: ?>

                            <p>No video found. Sorry!</p>
                            
                        <?php endif; ?>
                    </div> <!-- #aa-youTubeLatest -->

		<?php echo $after_widget; ?>
<?php

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('aa_youtube_latest', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
                $instance['aa_youtube_title'] = strip_tags($new_instance['aa_youtube_title']);
                $instance['aa_youtube_username'] = strip_tags($new_instance['aa_youtube_username']);
		$instance['aa_youtube_width'] = (int) $new_instance['aa_youtube_width'];
                $instance['aa_youtube_height'] = (int) $new_instance['aa_youtube_height'];
                
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_youtube_latest']) )
			delete_option('widget_youtube_latest');
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_youtube_latest', 'widget');
	}

	function form( $instance ) {
                $title = isset($instance['aa_youtube_title']) ? esc_attr($instance['aa_youtube_title']) : 'Latest Video';
		$username = isset($instance['aa_youtube_username']) ? esc_attr($instance['aa_youtube_username']) : 'amnestyinternational';

		if ( !isset($instance['aa_youtube_width']) || !$number = (int) $instance['aa_youtube_width'] ){
			$width = 640;
        }else{
            $width = $instance['aa_youtube_width'];
        }

        if ( !isset($instance['aa_youtube_height']) || !$number = (int) $instance['aa_youtube_height'] ){
			$height = 385;
        }else{
            $height = $instance['aa_youtube_height'];
        }
?>
        <p><label for="<?php echo $this->get_field_id('aa_youtube_title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('aa_youtube_title'); ?>" name="<?php echo $this->get_field_name('aa_youtube_title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('aa_youtube_username'); ?>"><?php _e('YouTube Username:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('aa_youtube_username'); ?>" name="<?php echo $this->get_field_name('aa_youtube_username'); ?>" type="text" value="<?php echo $username; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('aa_youtube_width'); ?>"><?php _e('Video Width:'); ?></label>
		<input id="<?php echo $this->get_field_id('aa_youtube_width'); ?>" name="<?php echo $this->get_field_name('aa_youtube_width'); ?>" type="text" value="<?php echo $width; ?>" size="3" /><br />
		<small><?php _e('(take from YouTube)'); ?></small></p>

                <p><label for="<?php echo $this->get_field_id('aa_youtube_height'); ?>"><?php _e('Video Height:'); ?></label>
		<input id="<?php echo $this->get_field_id('aa_youtube_height'); ?>" name="<?php echo $this->get_field_name('aa_youtube_height'); ?>" type="text" value="<?php echo $height; ?>" size="3" /><br />
		<small><?php _e('(take from YouTube)'); ?></small></p>
<?php
    }
}


/**
 * Remove some default widgets
 * and register the new ones versions instead.
 *
 * @return void
 */
function set_new_widgets(){
    register_widget('Aa_YouTubeLatest');
}
add_action('widgets_init', 'set_new_widgets', 1);
