<?php
/*
Plugin Name: SM Gallery
Plugin URI: http://wordpress.org/extend/plugins/sm-gallery/
Description: Gallery plugin thats simple because it leans on existing WordPress gallery features provided by http://sethmatics.com/.
Author: sethmatics, bigj9901
Version: 1.1.4
Author URI: http://sethmatics.com/
*/


// load front end scripts and styles
if (!is_admin()) {
	add_action('wp_enqueue_scripts', 'sm_gallery_scripts_and_styles');
	add_action('get_footer', 'sm_conditional_gallery_script_enqueue', 1);
}
	
function sm_gallery_scripts_and_styles() {
	wp_enqueue_script('jquery-ad-gallery-scripts', plugins_url('js/jquery.ad-gallery.js', __FILE__), array('jquery'), '', true );
	//older version of UI scripts (core, resizeable, dragable, dialog) fixes IE Jump issue
	wp_enqueue_script('jquery-ui', false, array('jquery'), '', true );
	wp_enqueue_script('jquery-ui-dialog', false, array('jquery'), '', true );
}

// remove scripts from footer if Gallery is not being used on the page
function sm_conditional_gallery_script_enqueue() {
	global $wp_scripts;
	if(!did_action('before_gallery')) {
		$wpFooterScripts = $wp_scripts->in_footer;
		$remove = array_search ('jquery-ad-gallery-scripts', $wpFooterScripts);
		unset($wpFooterScripts[$remove]);
		$wp_scripts->in_footer = $wpFooterScripts;
	}
}

// remove default gallery shortcode	
remove_shortcode('gallery');

//add our own gallery shortcode
//[gallery modal="" post_id="" box_height="" box_width="" title="" thumbnail="" thumb_class=""]
$sm_gallery_shortcode_noconflict = get_option('sm_gallery_shortcode_noconflict');
if(empty($sm_gallery_shortcode_noconflict)) add_shortcode('gallery', 'sm_gallery');
add_shortcode('sm_gallery', 'sm_gallery');

function sm_gallery( $atts, $content = null ) {
	global $post;
	do_action('before_gallery');
	$count = did_action('before_gallery');
	
	extract( shortcode_atts( array(
	  'post_id' => '',
	  'modal' => 'false',
	  'box_width' => '600',
	  'box_height' => '770',
	  'title' => 'Gallery',
	  'thumbnail' => false,
	  'thumb_class' => 'alignright',
	  'exclude_featured' => false,
	  ), $atts ) );
	  
	  
	  
	  if($post_id == '')
	  	$post_id = $post->ID;

ob_start();	 
	// set width and height of gallery and gallery image?>
    <style> .ad-gallery { width: <?php echo $box_width-50; ?>px; } .ad-gallery .ad-image-wrapper { height: <?php echo $box_height-220; ?>px; } 
    #adGal<?php echo $count;?>.ad-gallery { width: <?php echo $box_width-65; ?>px!important; } #adGal<?php echo $count;?>.ad-gallery .ad-image-wrapper { height: <?php echo $box_height-220; ?>px!important; }
	.ad-gallery .ad-controls { line-height:normal!important;}
    </style>
	<?php 
	// load css
	sm_load_ad_gallery_css($modal);
	
	// set thumbnail to trigger gallery open
	if(isset($thumbnail) && $thumbnail)
		$thumbnail = '<img class="sm-slideshow-trigger '.$thumb_class.'" src="'.$thumbnail.'" style="cursor:pointer;" />'.PHP_EOL;
	
	// output gallery and thumbnail if modal gallery
	if($modal=='true') { 
		$gallery = '<div id="galleryContent'.$count.'">'.get_sm_gallery($post_id, 0, $exclude_featured).'</div>';
		$gallery = $thumbnail.'<div id="galleryContent'.$count.'" class="isModal" style="height:1px; width:1px; overflow:hidden;">'.get_sm_gallery($post_id, $count, $exclude_featured).'</div>';
		
    }
	// only out put gallery if not modal
	else { 
		$gallery = '<div id="galleryContent'.$count.'">'.get_sm_gallery($post_id, 0, $exclude_featured, $atts, $content = null).'</div>';
	}
	
	// script for modal window ?>
	<script type="text/javascript">
		var modalBox<?php echo $count;?>;
		jQuery(window).load(function(){
			jQuery("#galleryContent<?php echo $count;?> .ad-gallery" ).adGallery({  loader_image: '/wp-content/plugins/sm-gallery/media/loader.gif' });
	
			if( jQuery("#galleryContent<?php echo $count;?>" ).hasClass('isModal') ){
				modalBox<?php echo $count;?> = jQuery( "#galleryContent<?php echo $count; ?> .ad-gallery" ).dialog({
							 title: '<?php echo $title; ?>',
							 autoOpen: false,
							 width: <?php echo $box_width; ?>,
							 height: <?php echo $box_height; ?>,
							 modal: true,
							 resizable: false,
							 draggable: true,
							 show: 'fade',
							 overlay: { opacity: 0.90, background: "black" } });
			}
			jQuery("#galleryContent<?php echo $count;?>" ).prev().css('cursor', 'pointer');
			jQuery("#galleryContent<?php echo $count;?>" ).prev().click(function() {
				modalBox<?php echo $count;?>.dialog('open');
			});
		});
	</script>
    <?php
		
	$gallery = ob_get_clean().$gallery;
	return $gallery;
}

// create gallery structure and content
function get_sm_gallery($post_id, $count=0, $exclude_featured, $atts='', $content = null){
	$gallery = '<div class="ad-gallery" id="adGal'.$count.'">'.PHP_EOL;
	$gallery .= '<div class="ad-image-wrapper">'.PHP_EOL;
	$gallery .= '</div>'.PHP_EOL;
	$gallery .= '<div class="ad-controls">'.PHP_EOL;
	$gallery .= '</div>'.PHP_EOL;
	$gallery .= '<div class="ad-nav">'.PHP_EOL;
	$gallery .= '<div class="ad-thumbs">'.PHP_EOL;
	$gallery .= '<ul class="ad-thumb-list">'.PHP_EOL;
	
	
	$exclude = '';
	if($exclude_featured && $exclude_featured!="false")
		$exclude = get_post_thumbnail_id( $post_id ); // exclude the featured image
			
	// get gallery images from post
	$args = array(
		'post_type'	  => 'attachment',
		'numberposts' => -1, // bring them all
		'exclude' 	  =>  $exclude, // exclude the featured image
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
		'post_status' => null,
		'post_parent' => $post_id // post id with the gallery
		); 
		
		
	if ( ! empty( $atts['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $atts['orderby'] ) )
			$args['orderby'] = 'post__in';
		$args['include'] = $atts['ids'];
	}
	
	$slides = get_posts($args);
	
	
	foreach ($slides as $slide) {
		
		//get the thumbnail src
		//try to get thumbnails that are 100 by 100 or closest possible size
		$thumbnailObj = wp_get_attachment_image_src($slide->ID, array(100,100));
		$thumbnailURL = $thumbnailObj[0];
		
		//get the full size img src
		$slideObj = wp_get_attachment_image_src($slide->ID, 'full');
		$slideURL = $slideObj[0];

		$gallery .= '<li><a href="'.$slideURL.'"><img src="'.$thumbnailURL.'" title="'.$slide->post_excerpt.'" alt="'.$slide->post_content.'"></a></li>'.PHP_EOL;
	}
	
	$gallery .= '</ul>'.PHP_EOL;
	$gallery .= '</div>'.PHP_EOL;
	$gallery .= '</div>'.PHP_EOL;
	$gallery .= '</div>'.PHP_EOL;
	
	return $gallery;
}

// add css to the page
function sm_load_ad_gallery_css($modal='false') { ?>
	<?php
	// add gallery stylesheet - only once if there are multiple galleries 
	if( did_action('before_sm_ad_gallery_stylesheet_loader') < 1 ) : 
    do_action('before_sm_ad_gallery_stylesheet_loader');
    ?>
	<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('.ui-widget-overlay').on('click', function(event) {
                jQuery('.ui-icon-closethick').click();
            });
            
        if (!document.createStyleSheet){
            jQuery("head").append(jQuery("<link id=\"style1\" rel='stylesheet' href='<?php echo apply_filters('sm_gallery_css', plugins_url('css/jquery.ad-gallery.css', __FILE__) );?>' type='text/css' media='screen' />"));
        }
        else {
            document.createStyleSheet('<?php echo apply_filters('sm_gallery_css', plugins_url('css/jquery.ad-gallery.css', __FILE__) ); ?>');
        }
	});
    </script>
    <?php 
	endif;
	// add modal stylesheet - only once if there are multiple galleries 
	if( $modal!='false' && did_action('before_sm_modal_box_stylesheet_loader') < 1 ) :
    do_action('before_sm_modal_box_stylesheet_loader');
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
			if (!document.createStyleSheet){
				jQuery("head").append(jQuery("<link id=\"style1\" rel='stylesheet' href='<?php echo plugins_url('css/smoothness/jquery-ui.css', __FILE__);?>' type='text/css' media='screen' />"));
			}
			else {
				document.createStyleSheet('<?php echo plugins_url('css/smoothness/jquery-ui.css', __FILE__);?>');
			}
		});
    </script>
    <?php endif; ?>
<?php }


/* Add Featured Shortcode Meta Box  */
add_action('add_meta_boxes', 'sm_featured_gallery', 10);
add_action('save_post', 'sm_featured_gallery_save');

function sm_featured_gallery(){
	add_meta_box('sm_featured_gallery', 'Featured Image Gallery' , 'sm_featured_gallery_form', 'page', 'side', '');
}

function sm_featured_gallery_form(){ 
	global $post, $post_meta; 
	$post_meta = get_post_custom($post_id);
	
	// show/hide gallery options based on type selection on page load
	echo '<style>';
	echo '#sm_featured_gallery_options, #sm_featured_gallery_thumb_options, #sm_featured_gallery_hyperlink_options { display:none; }';
	if($post_meta['_sm_featured_gallery_type'][0] == 'link')	
		echo '#sm_featured_gallery_options { display:block; }';
	if($post_meta['_sm_featured_gallery_type'][0] == 'append')	
		echo '#sm_featured_gallery_options, #sm_featured_gallery_thumb_options { display:block; }';
	if($post_meta['_sm_featured_gallery_type'][0] == 'hyperlink')	
		echo '#sm_featured_gallery_hyperlink_options { display:block; }';
	echo '</style>';
	
	// show/hide gallery options when type field changes ?>
    <script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#sm_featured_gallery_type').change(function() {
			if(jQuery(this).val() == 'append' || jQuery(this).val() == 'link')
				// if hyperlink options showing, hide and show gallery options
				if( jQuery('#sm_featured_gallery_hyperlink_options').is(":visible") ) {				
					jQuery('#sm_featured_gallery_hyperlink_options').hide('slow', function() {
						jQuery('#sm_featured_gallery_options').show('fast');
					})
				}
				// otherwise just show gallery options
				else
					jQuery('#sm_featured_gallery_options').show('fast');
				
			else if(jQuery(this).val() == 'hyperlink') {
				// if gallery options showing, hide and show hyperlink options
				if( jQuery('#sm_featured_gallery_options').is(":visible") ) {				
					jQuery('#sm_featured_gallery_options').hide('slow', function() {
						jQuery('#sm_featured_gallery_hyperlink_options').show('fast');
					})
					
				}
				// otherwise just show hyperlink options
				else
					jQuery('#sm_featured_gallery_hyperlink_options').show('fast');
			}
			
			// if disabled hide all		
			else {
				jQuery('#sm_featured_gallery_options').hide('fast');
				jQuery('#sm_featured_gallery_hyperlink_options').hide('fast');
			}
			
			//show thumbnail options if appending to featured img	
			if(jQuery(this).val() != 'append')
				jQuery('#sm_featured_gallery_thumb_options').hide('fast');
			else
				jQuery('#sm_featured_gallery_thumb_options').show('fast');
		});
	});
    </script>
    <select id="sm_featured_gallery_type" name="sm_featured_gallery_type" style="margin-bottom:10px;margin-top:5px;width:100%;" >
        <option value="">Disabled</option>
        <option value="link" <?php if($post_meta['_sm_featured_gallery_type'][0] == 'link') { echo ' selected="selected"'; } ?>>Link Featured Image To Gallery</option>
        <option value="append" <?php if($post_meta['_sm_featured_gallery_type'][0] == 'append') { echo ' selected="selected"'; } ?>>Append Featured Image</option>
        <option value="hyperlink" <?php if($post_meta['_sm_featured_gallery_type'][0] == 'hyperlink') { echo ' selected="selected"'; } ?>>Add Hyperlink To Featured Image</option>
    </select>
    <br />
    
    <div id="sm_featured_gallery_options">
        <label for="sm_featured_gallery_title">Title</label><br />
        <input name="sm_featured_gallery_title" id="sm_featured_gallery_title" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_title'][0]; ?>" />
        <br />
        <label for="sm_featured_gallery_post_id">Post ID (Leave blank for current post)<br />
        <span style="font-size:11px;color: #999;">Use when pulling gallery from another post</span> </label><br />
        <input name="sm_featured_gallery_post_id" id="sm_featured_gallery_post_id" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_post_id'][0]; ?>" />
        <br />
        	<input type="checkbox" name="sm_featured_gallery_exclude_featured_checkbox" id="sm_featured_gallery_exclude_featured_checkbox" value="true" <?php if(get_post_meta($post->ID, '_sm_featured_gallery_exclude_featured', true) == 'true') { echo 'checked="checked"'; } ?> >
        	<label for="sm_featured_gallery_exclude_featured_checkbox">Exclude featured image</label>
            <input name="sm_featured_gallery_exclude_featured" id="sm_featured_gallery_exclude_featured" type="hidden" value="" />
            <script type="text/javascript">
				jQuery(document).ready(function() {
  					jQuery('#sm_featured_gallery_exclude_featured_checkbox').change(function() {
						if( jQuery('#sm_featured_gallery_exclude_featured_checkbox').attr('checked') )
							jQuery('#sm_featured_gallery_exclude_featured').val('true');
						else
							jQuery('#sm_featured_gallery_exclude_featured').val('false');
					});
				});
			</script>
            <div class="clearfloat"></div>
            <br />
        <label for="sm_featured_gallery_width">Gallery Width</label><br />
        <input name="sm_featured_gallery_width" id="sm_featured_gallery_width" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_width'][0]; ?>" />
        <br />
        <label for="sm_featured_gallery_height">Gallery Height</label><br />
        <input name="sm_featured_gallery_height" id="sm_featured_gallery_height" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_height'][0]; ?>" />
        <br />
        <div id="sm_featured_gallery_thumb_options">
            <label for="sm_featured_gallery_thumb">Thumbnail Image</label><br />
            <input name="sm_featured_gallery_thumb" id="sm_featured_gallery_thumb" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_thumb'][0]; ?>" />
            <br />
            <label for="sm_featured_gallery_thumb_class">Thumbnail Class</label><br />
            <input name="sm_featured_gallery_thumb_class" id="sm_featured_gallery_thumb_class" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_thumb_class'][0]; ?>" />
            <br />
        </div>
    </div>
    <div id="sm_featured_gallery_hyperlink_options">
        <label for="sm_featured_gallery_hyperlink">Link</label><br />
        <input name="sm_featured_gallery_hyperlink" id="sm_featured_gallery_hyperlink" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo $post_meta['_sm_featured_gallery_hyperlink'][0]; ?>" />
        <br />
        <div style="margin-bottom:10px;">
        	<input type="checkbox" name="sm_featured_gallery_hyperlink_new_checkbox" id="sm_featured_gallery_hyperlink_new_checkbox" value="yes" <?php if(get_post_meta($post->ID, '_sm_featured_gallery_hyperlink_new_widow', true) == 'yes') { echo 'checked="checked"'; } ?> >
        	<label for="sm_featured_gallery_hyperlink_new_checkbox">Open In New Window</label>
            <input name="sm_featured_gallery_hyperlink_new_widow" id="sm_featured_gallery_hyperlink_new_widow" type="hidden" value="" />
            <script type="text/javascript">
				jQuery(document).ready(function() {
  					jQuery('#sm_featured_gallery_hyperlink_new_checkbox').change(function() {
						if( jQuery('#sm_featured_gallery_hyperlink_new_checkbox').attr('checked') )
							jQuery('#sm_featured_gallery_hyperlink_new_widow').val('yes');
						else
							jQuery('#sm_featured_gallery_hyperlink_new_widow').val('no');
					});
				});
			</script>
            <br />
        </div>
    </div>
    
    
    
	<?php 
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'sm_featured_gallery_nonce' );
} 

function sm_featured_gallery_save(){
	global $post;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post->ID;
	
	// verify
	if ( !wp_verify_nonce( $_POST['sm_featured_gallery_nonce'], plugin_basename( __FILE__ ) ) ) return;
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) return;
		else
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	}
	
	// save gallery options
	foreach($_POST as $key => $value) {
	  	if(stristr($key,'sm_featured_gallery')) {
			if($key == 'sm_featured_gallery_width' || $key == 'sm_featured_gallery_height' ) {
				$value=str_replace('px', '', $value); 
			}
			// dont' save nonce or checkbox
			if($key != 'sm_featured_gallery_nonce' && $key != 'sm_featured_gallery_hyperlink_new_checkbox'  && $key != 'sm_featured_gallery_exclude_featured_checkbox') {
				if($value != '')
					update_post_meta($post->ID, '_'.$key, trim($value) );
				else
					delete_post_meta($post->ID, '_'.$key);
			}
		}
	}
}

// filter featured image content and add gallery when applicable
add_filter( 'post_thumbnail_html', 'sm_gallery_featured_filter', 10, 3 );

function sm_gallery_featured_filter( $html, $post_id, $post_image_id ) {
	$post_meta = get_post_custom($post_id);
	$galleryType = $post_meta['_sm_featured_gallery_type'][0];
	
	// if its disabled return featured image conent unfilltered
	if($galleryType == '')
		return $html;
		
	// if we are just adding a link to the feat img
	if($galleryType == 'hyperlink') {
		$url = $post_meta['_sm_featured_gallery_hyperlink'][0];
		$target = '';
		if( $post_meta['_sm_featured_gallery_hyperlink_new_widow'][0] == 'yes' )
			$target = ' target="_blank"';
		$html = '<a href="'.$url.'" id="featuredImgLink"' .$target.'>'.$html.'</a>';
		return $html;	
	}
	
	$sc_post_id = $post_meta['_sm_featured_gallery_post_id'][0] ;
	$height = $post_meta['_sm_featured_gallery_height'][0];
	$width = $post_meta['_sm_featured_gallery_width'][0];
	$title = $post_meta['_sm_featured_gallery_title'][0];
	$thumb = $post_meta['_sm_featured_gallery_thumb'][0];
	$thumb_class = $post_meta['_sm_featured_gallery_thumb_class'][0];
	$exclude_featured = $post_meta['_sm_featured_gallery_exclude_featured'][0];
	
	$featuredObj = wp_get_attachment_image_src(get_post_thumbnail_id( $post_id ), 'full');
		
	
	// build the shortcode based on options entered in form
	$shortCodeContent ='[gallery modal="true" ';
	if($post_id != '') $shortCodeContent .='post_id="'.$sc_post_id.'" ';
	if($height != '')$shortCodeContent .=' box_height="'.$height.'" ';
	if($width != '')$shortCodeContent .='box_width="'.$width.'" ';
	if($title != '')$shortCodeContent .=' title="'.$title.'" '; 
	if($exclude_featured != '')$shortCodeContent .=' exclude_featured="'.$exclude_featured.'" ';
	
	if($galleryType == 'append') $shortCodeContent .=' thumbnail="'.$thumb.'" ';
	
	if($thumb_class != '' && $galleryType == 'append')$shortCodeContent .='thumb_class="'.$thumb_class.'" ';
	$shortCodeContent .=']';
	
	// if using featured image as link
	if( $galleryType == 'link')
		$html = $html.do_shortcode($shortCodeContent);
	
	// if using thumbnail as link
	else {
		$html = '<div class="featureHolder" style="width:'.$featuredObj[1].'px;">'.$html.do_shortcode($shortCodeContent).'</div>';
	}
    return $html;
}