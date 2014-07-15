<?php
//@TODO: convert basic functions into class functions, don't forget to update the shortcode in sm-gallery.php

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
	<style> .ad-gallery { width: <?php echo $box_width-50; ?>px; } .ad-gallery .ad-image-wrapper { height: <?php echo $box_height-340; ?>px; }
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
		//unset the post parent -> post ID as ids may not be related
		$args['post_parent'] = '';
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