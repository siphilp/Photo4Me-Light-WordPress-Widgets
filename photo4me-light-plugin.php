<?php
/*
Plugin Name: Photo4Me Light * Beta
Plugin URI: https://www.siphilp.co.uk/photo4me-widget-beta-testing
Description: Plugin that displays a gallery of images for a specific member on <a href="http://www.photo4me.com?ref=plugin-light">Photo4me</a>
Version: 0.1
Author: Simon Philp
Author URI: https://www.siphilp.co.uk
License: MIT
*/
?>
<?php

if( !defined('WPINC') ) exit('No direct access permitted');

class photo4me_light_plugin extends WP_Widget{

    function photo4me_light_plugin(){
        parent::WP_Widget(false, $name = __('Photo4Me light','photo4me_light_plugin'));
    }

    function form($instance){
        if($instance)
        {
            $memberid = esc_attr($instance['memberid']);
			$imagelimit =esc_attr($instance['imagelimit']);
			$widgettitle = esc_attr($instance['widgettitle']);
			$cacheApiCall = esc_attr($instance['cacheApiCall']);		
        }else
        {
            $memberid = 14016;
			$imagelimit = 6;
			$widgettitle = "Photo4Me";
			$cacheApiCall = 5;
		
        }

?>
	<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'photo4me_light_plugin'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('widgettitle'); ?>" name="<?php echo $this->get_field_name('widgettitle'); ?>" type="text" value="<?php echo $widgettitle; ?>" />
    </p>
	

    <p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Member Id', 'photo4me_light_plugin'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('memberid'); ?>" name="<?php echo $this->get_field_name('memberid'); ?>" type="number" value="<?php echo $memberid; ?>" />
    </p>
	
	  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Number Max Images', 'photo4me_light_plugin'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('imagelimit'); ?>" name="<?php echo $this->get_field_name('imagelimit'); ?>" type="number" min="1" value="<?php echo $imagelimit; ?>" />
    </p>
	
	 <p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Cache API Results for x amount of minutes', 'photo4me_light_plugin'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('cacheApiCall'); ?>" name="<?php echo $this->get_field_name('cacheApiCall'); ?>" type="number" min="1" value="<?php echo $cacheApiCall; ?>" />
    </p>	
<?php

    }

    function update($new_instance,$old_instance){
			$instance = $old_instance;
			$instance['memberid'] = strip_tags($new_instance['memberid']);
			$instance['imagelimit'] = strip_tags($new_instance['imagelimit']);
			$instance['widgettitle'] = strip_tags($new_instance['widgettitle']);
			$instance['cacheApiCall'] = strip_tags($new_instance['cacheApiCall']);		
			delete_transient('photo4me-photos');
			return $instance;
			}

    function widget($args,$instance){  
		
		wp_register_style('new-photo4me-light-css', plugins_url('photo4me-light\photo4me-light.css'),false,'0.1','all');
		wp_enqueue_style('new-photo4me-light-css');
	
        $memberid = $instance['memberid'];
		$imageLimit = $instance['imagelimit'];
		$widgetTitle = $instance['widgettitle'];		
		$cacheTime = $instance['cacheApiCall'];
		$transName = 'photo4me-photos';	
		
		if(false === ($data = get_transient($transName)))
		{
				$json = wp_remote_get("http://api.photo4me.com/result/$memberid");	
				$data = json_decode($json['body'],true);
				set_transient($transName,$data,60*$cacheTime);	
		}
		

		if( is_wp_error($json)){		
			echo 'ERROR - Try again..';
		}else{
		
	
		$photodata = $data['Photos'];
		
		echo '<aside class="widget wp_widget_photo4me"><h1 class="widget-title">'. $widgetTitle . '</h1><ul id="photo4me-image-list">';	
		
		$counter = 0;
		
		foreach($photodata as $photo){ 
	
		if($counter == $imageLimit) break;
				
				$picturename =$photo['PictureName'];
				$picturedescription = $photo['PictureDescription'];
				$photothumb = $photo['PhotoThumbUrl']; 	
				$shopurl = $photo['PhotoUrl'];
				echo '<li><a href="' .  $shopurl .'" target="_blank" title="' . $picturename . '-' . $picturedescription . '"><img src="' . $photothumb . '" class="widget-photo4me-img-side" alt="' . $picturename . '-' . $picturedescription . '" /></a></li>';
				$counter++;
		
		}
		
		echo '</ul><p style="float:none;clear:both"><a href="http://www.photo4me.com/photographer.asp?OwnerID=' . $memberid . '">View All Images</a></p>';
		echo '</aside>';
		echo $after_widget;
		}
		
    }	
	
}

//registering widget
add_action('widgets_init',create_function('','return register_widget("photo4me_light_plugin");'));

?>