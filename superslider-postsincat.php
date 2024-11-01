<?php
/**
Plugin Name: Superslider-PostinCat 
Plugin URI: http://superslider.daivmowbray.com/superslider/superslider-postsincat
Author: Daiv Mowbray
Author URI: http://superslider.daivmowbray.com
Description: This widget scrolls the thumbnails dynamicaly created list of posts from the active category. Displaying the first image and title.
Version: 2.1
 */

class ss_postsincat_widget extends WP_Widget {

 
    /** constructor -- name this the same as the class above */
    function ss_postsincat_widget() {
    	
    	$widget_ops = array('description' => __('Sliding list of posts as thumbnails', 'superslider-postsincat'));
        parent::WP_Widget(false, $name = 'SuperSlider-PostsinCat', $widget_ops);	

		if ( !function_exists('wp_register_sidebar_widget') )
		return;
		
		global $slidebox_id;
        global $load_css;
        srand((double)microtime()*1000000); 
		$slidebox_id = rand(0,1000);
		
		$pic_js_path = plugins_url( 'js/' , __FILE__ );
		$admin_path = plugins_url( 'admin/' , __FILE__ );
		
		wp_register_script('moocore',$pic_js_path.'mootools-core-1.4.5-full-compat-yc.js',NULL, '1.4.5');
		wp_register_script('slideBox',$pic_js_path.'slideBox-v1.0.js',array( 'moocore' ), '1.0', true); // loads into the footer
		
		wp_register_style('superslider_admin_tool', $admin_path.'ss_admin_tool.css');
		
		if (!is_admin())  {				
			if (function_exists('wp_enqueue_script'))  {
			   wp_enqueue_script('moocore');
			   wp_enqueue_script('slideBox');
			}
		}
         
    }
    function ss_admin_style($hook){     	
        	if( 'widgets.php' == $hook )
        	return;
        	//wp_enqueue_style( 'superslider_admin');
    	    wp_enqueue_style( 'superslider_admin_tool');

	}
	
	function ss_admin_script($hook){
			if( 'widgets.php' == $hook )
        	return;
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-core');
            wp_enqueue_script( 'jquery-dimensions');  
            wp_enqueue_script( 'jquery-tooltip' );
            wp_enqueue_script( 'superslider-admin-tool' );
	}    
    
	/**
	* Adds css to head of template file
	*
	*/
	function ss_postsincat_load_css() {
			wp_enqueue_style( 'postincat_style');			
	}
    
	/**
	* Adds js after the widget
	* called by function widget
	*
	*/
	function ss_postsincat_js_starter($options){
		   global $slidebox_id;	

			extract($options, EXTR_SKIP);

			$ss_widget = '
		 var slidePosts'.$slidebox_id.' = new slidePosts(\'slider'.$slidebox_id.'\',{
			fadeArrows:true,
			startOpacity:0.4,
			endOpacity:1,
			speed:'.$speed.',
			transition:Fx.Transitions.'.$trans_type.'.'.$trans_typeout.',
			myaction:\''.$myaction.'\'
		});
		   
			';
			$starterOut = '<!-- SuperSlider postincat widget. -->';
			$starterOut .= "\n"."<script type=\"text/javascript\">\n";
			$starterOut .= "\t"."// <![CDATA[\n";		
			$starterOut .= "window.addEvent('domready', function() {
					".$ss_widget."
					});\n";
			$starterOut .= "\t".'// ]]>';
			$starterOut .= "\n".'</script>'."\n";

			echo $starterOut;	
	}
	
    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $options) {	
    
    	global $post;
	    global $slidebox_id;
	    global $img_width;
        global $img_height;
        global $ran_num;
        global $load_css;
        global $css_path;
    	
        extract( $args );
        extract( $options );
     
     if ($options['load_css'] == 'default') {				  
        $css_path = WP_PLUGIN_URL.'/superslider-postsincat/plugin-data/superslider/ssPostinCat/';               
     } elseif($options['load_css'] == 'content') {			
        $css_path = WP_CONTENT_URL.'/plugin-data/superslider/ssPostinCat/';
     } elseif($options['load_css'] == 'theme') {			
        $css_path = get_stylesheet_directory_uri().'/plugin-data/superslider/ssPostinCat/';
     } elseif($options['load_css'] == 'off') {			
        $css_path = get_stylesheet_directory_uri().'/ssPostinCat/';
     }
   
   if($css_path !== '')  {
        $pic_css_file = $css_path.$theme.'/'.$theme.'.css';
        wp_register_style('postincat_style', $pic_css_file);
        wp_enqueue_style( 'postincat_style');
    }else {
        $pic_css_file = '';
    }

		// get the size of the default thumbnail image
        $img_width = get_option ( $imagesize.'_size_w' );
        $img_height = get_option ( $imagesize.'_size_h' );
 
		$the_output = NULL;
		$image_output = NULL;
		
		$this_post_id = $post->ID;
	
        $categories = get_the_category();
        $num = count($categories);
        if ($num > 1) $categories = array_slice($categories, 0, 1);

        if (empty($categories))  {
			return NULL;
		 }
                 
        $title 		= apply_filters('widget_title', $options['title']);
             
        foreach ($categories as $category)  {
            
           if ($options['add_cat_name'] == 'on')  { $cat_name = $category->name;  }else { $cat_name = ''; }           
           $title_text = ($options['title'] != "") ? $options['title'].' '.$cat_name : "";
          $posts = get_posts('numberposts='.$postnumb.'&category='. $category->term_id);
           	   
           // remove the active post from the list
           foreach( $posts as $key => $obj)  {
               if ( $obj->ID == $this_post_id)
                unset($posts[$key]);
            }
            
           foreach($posts as $post)  {
                $postid = $post->ID; 
                $attachments = array();
                $image_output = '';
				// check first for a post 2.9 post thumb setting
                if ( function_exists( 'get_the_post_thumbnail' )) {          
                    $image_output = get_the_post_thumbnail($post_id = $postid, $size = $options['imagesize'], $attr = array('class'=>'postincat_thumb'));
                 }
                if ( empty($image_output) ) {		
				    $attachments = get_children( array('post_parent' => $postid, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image') );	//, 'order' => $order, 'orderby' => $orderby		
			         
			         $att_count = count($attachments);
                     if($att_count > 1) $attachments = array_slice($attachments, 0, 1, true);
                     $image_output = '';			        
			         $image = '';
                     
                     foreach ( $attachments as $id => $attachment ) {  
                         $image = wp_get_attachment_image_src($id, $imagesize);
                         $image_output = "<img src=\"$image[0]\" alt=\" {$attachment->post_excerpt }\" width=\"$image[1]\" height=\"$image[2]\" />";    
                     }
			    }
			    if ( empty($image_output) &&  empty($attachments)) { 
 
			        $image_output = '';			        
			        $image = '';
			        // use ss_image_by_scan to search the content for an image
			        $image_output = $this->ss_image_by_scan( $args = array() );
			         			        
			        // if there are no images in the content, we'll use the default/last resort image.
                    if ($image_output == false)  {
                        $n = mt_rand(1, $ran_num);          
                        $image = $css_path.'random-images/random-'.$n.'.jpg';
                        $alt = 'default image';
                        $image_output = '<img src="'.$image.'" alt="'.$alt.'" width="'.$img_width.'" height="'.$img_height.'" />';                            
                     }   
			    }

                $the_output .= "\n".'<li class="ss_postincat_post" ><a href="' .get_permalink($post->ID) . '">' .$image_output. '</a><br />';
                if ($show_post_title == 'on') $the_output .= '<a href="' .get_permalink($post->ID) . '">' .$post->post_title . '</a>';
                $the_output .= '</li>';
          
           } // end foreach posts           

            $ss_widget_out = $before_widget.$before_title.$title_text.$after_title; 

            $ss_widget_out .= "\n".'<div id="slider'.$slidebox_id.'" class="slideBox-container" style="height:'.$display_height.'px; overflow:hidden;" >
              
            <div class="slideBox-wrapper">
            <div class="slideBox-slider">
                 <ul class="ss_post_list">'.$the_output.'</ul>
            </div></div>
            <div class="slideBox-previous"><a href="#" class="slideBox-previous" title="Previous">&nbsp;</a></div>
            <div class="slideBox-next"><a href="#" class="slideBox-next" title="Next">&nbsp;</a></div>
               
            <div class="slideBox_overlay a"></div>
            <div class="slideBox_overlay b"></div>
            </div>'.$after_widget;
             
       }    

        echo $ss_widget_out; 
        
        // load the custom js into the footer
        $this->ss_postsincat_js_starter($options);
              
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {				
		$options = $old_instance;
				
			$options['title'] = strip_tags($new_instance['title']);
			$options['add_cat_name'] = $new_instance['add_cat_name'];
			$options['show_post_title'] = $new_instance['show_post_title'];			
			$options['postnumb'] = (int)$new_instance['postnumb'];			
			$options['display_height'] = (int)$new_instance['display_height'];
			$options['ran_num'] =  $new_instance['ran_num'];
			$options['load_css'] =  $new_instance['load_css'];
			$options['theme'] =  $new_instance['theme'];
			$options['imagesize'] =  $new_instance['imagesize'];
			$options['myaction'] =  $new_instance['click'];
			$options['speed'] =  $new_instance['speed'];
			$options['trans_type']	= $new_instance["trans_type"];
			$options['trans_typeout']	= $new_instance["trans_typeout"];
        
        return $options;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($options) {	    	
    	if (  empty($options) ){			
			$options = array( 'add_cat_name'=>'on', 
			                 'trans_type'=>'Sine', 
			                 'trans_typeout'=>'easeIn', 
			                 'myaction'=>'click', 
			                 'speed'=>40, 
			                 'title'=>'More from', 
			                 'postnumb'=>12, 
			                 'show_post_title'=>'on', 
			                 'display_height'=>552, 
			                 'ran_num'=>5, 
			                 'load_css'=>'default',
			                 'theme'=>'default', 
			                 'imagesize'=>'thumbnail');

		 }	
		
		extract($options, EXTR_SKIP);
		 		 
 		$plugin_name = 'superslider-postsincat';
 		$selected ='selected="selected"';
	    $checked = 'checked="checked"';
        
        ?>
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('title'); ?>"><?php echo __('Title:') ;?> 
		  <input class="" id="<?php echo $this->get_field_name('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('add_cat_name'); ?>"><?php  echo __('Add cat name to title:',$plugin_name) ;?> 
		  <input id="<?php echo $this->get_field_name('add_cat_name'); ?>" name="<?php echo $this->get_field_name('add_cat_name'); ?>" type="checkbox" <?php if($options['add_cat_name'] == "on") echo $checked; ?> value="on" /></label></p>
        
        <p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
        <label for="<?php echo $this->get_field_name('show_post_title'); ?>"><?php echo __('Show Post tile:') ;?> 
            <input id="<?php echo $this->get_field_name('show_post_title'); ?>" name="<?php echo $this->get_field_name('show_post_title'); ?>" type="checkbox" <?php if($options['show_post_title'] == "on") echo $checked; ?> value="on" /></label></p>
		
		<p style="text-align:right;  border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		  <label for="<?php echo $this->get_field_name('imagesize'); ?>"><?php  echo __('Image size to use :',$plugin_name) ; ?> 
		  <select name="<?php echo $this->get_field_name('imagesize'); ?>" id="<?php echo $this->get_field_name('imagesize'); ?>">   
        <?php 
        global $wp_version;    
        // is not version 3+
         if (version_compare($wp_version, "2.9.9", "<")) {
            $size_names = array('thumbnail' => 'thumbnail', 'medium' => 'medium', 'large' => 'large');
            if (function_exists('add_theme_support')) $size_names['post-thumbnail'] = 'post-thumbnail'; 
            if (class_exists("ssShow")) { $size_names['slideshow'] = 'slideshow'; $size_names['minithumb'] = 'minithumb';}
            if (class_exists("ssExcerpt")) $size_names['excerpt'] = 'excerpt'; 
            if (class_exists("ssPnext")) $size_names['prenext'] = 'prenext'; 
    
        } else {       
            $size_names =  get_intermediate_image_sizes();// this only works with WP version 3+
        }
        foreach ( $size_names as $size ) { ?>
        <option <?php if($options['imagesize'] == $size) echo $selected; ?> value="<?php echo $size; ?>" > <?php echo $size; ?></option>     
        <?php }?>     
        </select></label></p>	
		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('postnumb'); ?>"><?php  echo __('Maximum number of posts to list:',$plugin_name) ; ?> 
		  <input style="width: 30px;" id="<?php echo $this->get_field_name('postnumb'); ?>" name="<?php echo $this->get_field_name('postnumb'); ?>" type="text" value="<?php echo $options['postnumb']; ?>" /></label></p>
		  		  		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd; padding-bottom: 5px;"><label for=" <?php echo $this->get_field_name('ran_num'); ?>">		
		<?php  echo __('# of random images to select from',$plugin_name) ; ?>
		  <input style="width: 30px;" id="<?php echo $this->get_field_name('ran_num'); ?>" name="<?php echo $this->get_field_name('ran_num'); ?>" type="text" value="<?php echo $options['ran_num']; ?>" /></label>
		  <span style="text-align:right; font-size:0.8em;">		  
		  <?php  echo __('when no image, a random image will be selected from plugin-data/ssPostinCat/random-images/',$plugin_name) ; ?>
		  </span></p>
	
		<p style="text-align:right;">
		<label for="<?php echo $this->get_field_name('load_css'); ?>"> <?php  echo __('Load css from where:',$plugin_name) ; ?></label>
		<select name="<?php echo $this->get_field_name('load_css'); ?>" id="<?php echo $this->get_field_name('load_css'); ?>">
			 <option <?php if($options['load_css'] == "default") echo $selected; ?>  value="default"> default</option>
			 <option <?php if($options['load_css'] == "content") echo $selected; ?> value="content"> wp-content</option>
			 <option <?php if($options['load_css'] == "theme") echo $selected; ?>  value="theme"> theme</option> 
			 <option <?php if($options['load_css'] == "off") echo $selected; ?>  value="off"> off</option> 
		</select>
		</p>
		   	
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('theme'); ?>"><?php  echo __('Plugin Theme to use:',$plugin_name) ; ?> </label>
			<select id="<?php echo $this->get_field_name('theme'); ?>" name="<?php echo $this->get_field_name('theme'); ?>" >
            <option label="default" value="default" <?php if( $options['theme'] == "default") echo $selected; ?> >default</option>
            <option label="black" value="black" <?php if( $options['theme'] == "black") echo $selected; ?> >black</option>
            <option label="blue" value="blue" <?php if( $options['theme'] == "blue") echo $selected; ?> >blue</option>
            <option label="custom" value="custom" <?php if( $options['theme'] == "custom") echo $selected; ?> >custom</option>
        </select>
		</p>
		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;"><label for="<?php echo $this->get_field_name('display_height'); ?>"><?php  echo __('Height of display area:',$plugin_name) ; ?> 
		  <input style="width: 40px;" id="<?php echo $this->get_field_name('display_height'); ?>" name="<?php echo $this->get_field_name('display_height'); ?>" type="text" value="<?php echo $options['display_height']; ?>" /></label></p>
		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;"><label for="<?php echo $this->get_field_name('speed'); ?>"> <?php echo __('Speed of display area scroll: <br />(0 fast - 100 slow)',$plugin_name) ; ?> 
		  <input style="width: 40px;" id="<?php echo $this->get_field_name('speed'); ?>" name="<?php echo $this->get_field_name('speed'); ?>" type="text" value="<?php echo $options['speed']; ?>" /></label></p>

		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('click'); ?>"><?php  echo __('To activate use:',$plugin_name); ?> </label>
			<select id="<?php echo $this->get_field_name('click'); ?>" name="<?php echo $this->get_field_name('click'); ?>" >
            <option label="click" value="click" <?php if($options['myaction'] == "click") echo $selected; ?> >click</option>
            <option label="mouseover" value="mouseover" <?php if($options['myaction'] == "mouseover") echo $selected; ?>>mouseover</option>
        </select>
		</p>
		
		<p style="text-align:right; border-bottom: 1px solid #cdcdcd;padding-bottom: 5px;">
		<label for="<?php echo $this->get_field_name('trans_type'); ?>"> <?php  echo __(" Transition type:",$plugin_name) ?> </label>  
		 <select name="<?php echo $this->get_field_name('trans_type'); ?>" id="<?php echo $this->get_field_name('trans_type'); ?>">
			 <option   <?php if($options['trans_type'] == "Sine") echo $selected; ?> id="Sine" value="Sine"> Sine</option>
			 <option   <?php if($options['trans_type'] == "Elastic") echo $selected; ?> id="Elastic" value="Elastic"> Elastic</option>
			 <option   <?php if($options['trans_type'] == "Bounce") echo $selected; ?> id="Bounce" value="Bounce"> Bounce</option>
			 <option   <?php if($options['trans_type'] == "Back") echo $selected; ?> id="Back" value="Back"> Back</option>
			 <option   <?php if($options['trans_type'] == "Expo") echo $selected; ?> id="Expo" value="Expo"> Expo</option>
			 <option   <?php if($options['trans_type'] == "Circ") echo $selected; ?> id="Circ" value="Circ"> Circ</option>
			 <option   <?php if($options['trans_type'] == "Quad") echo $selected; ?> id="Quad" value="Quad"> Quad</option>
			 <option   <?php if($options['trans_type'] == "Cubic") echo $selected; ?> id="Cubic" value="Cubic"> Cubic</option>
			 <option   <?php if($options['trans_type'] == "Linear") echo $selected; ?> id="Linear" value="Linear"> Linear</option>
			 <option   <?php if($options['trans_type'] == "Quart") echo $selected; ?> id="Quart" value="Quart"> Quart</option>
			 <option   <?php if($options['trans_type'] == "Quint") echo $selected; ?> id="Quint" value="Quint"> Quint</option>
			</select>
			<br />
		
		<label for="<?php echo $this->get_field_name('trans_typeout'); ?>"> <?php  echo __(' Transition action:',$plugin_name); ?></label>
		<select name="<?php echo $this->get_field_name('trans_typeout'); ?>" id="<?php echo $this->get_field_name('trans_typeout'); ?>">
			 <option <?php if($options['trans_typeout'] == "easeIn") echo $selected; ?> id="easeIn" value="easeIn"> ease in</option>
			 <option <?php if($options['trans_typeout'] == "easeOut") echo $selected; ?> id="easeOut" value="easeOut"> ease out</option>
			 <option <?php if($options['trans_typeout'] == "easeInOut") echo $selected; ?>  id="easeInOut" value="easeInOut"> ease in out</option>     
		</select><br /></p>
        <?php 
    }
 	/**
	 * Scans the post for images within the content
	 * Not called by default 
	 *
	 * @since 1.0
	 * @param array $args
	 * @return string|image
	 */
	function ss_image_by_scan( $args = array() )  {

    global $post;
    global $img_width;
    global $img_height;
    $image = '';
        
    preg_match( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $post->post_content, $matches );

    //if ( isset( $matches ) ) $image = $matches[0][0];
    if ( isset($matches[0]) )  {

            $image = $matches[0];
      
            $pattern = '/<img(.*?)class=[\'"](.*?)[\'"](.*?)\/>/i';
            $replacement = '<img$1 class="thumbnail" $3 />';
            $image = preg_replace($pattern, $replacement, $image);
 
            $pos1 = stripos($image, $img_width);
           
         // Yep, 'img_width' is certainly in 'image'
         if ($pos1 !== false)  {   
                    // send back the image
                    return $image;
                   
          } else  {
                // now replace the height, width and scr extension 
                
                $patterns[0] = '/width=[\'"](.*?)[\'"]/';
                $patterns[1] = '/height=[\'"](.*?)[\'"]/';
                $patterns[2] = '/(-\d+)(.*?)x(.*?)\./';
    
                $replacements[0] = 'width="' . $img_width . '"';
                $replacements[1] = 'height="' . $img_height . '"';
                $replacements[2] = "-" . $img_width . 'x' . $img_height.'.';
              
                $image = preg_replace( $patterns, $replacements, $image );
    
                // get the image path on server,   
                $pattern = '/<img(.*?)src=[\'"](.*?)[\'"](.*?)\/>/i';
                $replacement = ' $2 ';
                $justimage = preg_replace($pattern, $replacement, $image);    
                $imagefile = ABSPATH.substr($justimage,stripos($justimage,"wp-content"));

                // look to see if it is there;           
               if (file_exists($imagefile)) {  
                    return $image;
                }else{                      
                     return false;
                }
          }
          
      } else  {
        return false;
      }

	}
 
} // end class ss-postsincat_widget
add_action('widgets_init', create_function('', 'return register_widget("ss_postsincat_widget");'));
?>