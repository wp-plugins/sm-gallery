<?php
class sm_gallery_admin {
	//singleton instance
	protected static $instance = NULL;

	//class init
	function __construct(){
		/* Add Featured Shortcode Meta Box  */
		add_action('add_meta_boxes', array($this,'featured_gallery_meta_box'), 10);
		add_action('save_post', array($this,'featured_gallery_save'));
	}

	//plugins working instance
	public static function get_instance() {
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	//setup the meta box to control the gallery on featured image feature
	function featured_gallery_meta_box($post_type){
		add_meta_box('sm_featured_gallery', 'Featured Image Gallery' , array($this,'featured_gallery_form'), 'page', 'side', '');
		//limit meta box to certain post types
		$post_types = apply_filters('sm_gallery_post_types', array('post', 'page'));
		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'sm_featured_gallery'
				,__( 'Featured Image Gallery', SM_GALLERY_TEXTDOMAIN )
				,array( $this, 'featured_gallery_form' )
				,$post_type
				,'side'
				,''
			);
		}
	}

	//form on page/post that controls the options for featured banner "onclick" gallery popup
	function featured_gallery_form($post, $metabox){
		$post_id = $post->ID;
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
			<input name="sm_featured_gallery_title" id="sm_featured_gallery_title" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_title'][0]; ?>" />
			<br />
			<label for="sm_featured_gallery_post_id">Post ID (Leave blank for current post)<br />
				<span style="font-size:11px;color: #999;">Use when pulling gallery from another post</span> </label><br />
			<input name="sm_featured_gallery_post_id" id="sm_featured_gallery_post_id" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_post_id'][0]; ?>" />
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
			<input name="sm_featured_gallery_width" id="sm_featured_gallery_width" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_width'][0]; ?>" />
			<br />
			<label for="sm_featured_gallery_height">Gallery Height</label><br />
			<input name="sm_featured_gallery_height" id="sm_featured_gallery_height" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_height'][0]; ?>" />
			<br />
			<div id="sm_featured_gallery_thumb_options">
				<label for="sm_featured_gallery_thumb">Thumbnail Image</label><br />
				<input name="sm_featured_gallery_thumb" id="sm_featured_gallery_thumb" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_thumb'][0]; ?>" />
				<br />
				<label for="sm_featured_gallery_thumb_class">Thumbnail Class</label><br />
				<input name="sm_featured_gallery_thumb_class" id="sm_featured_gallery_thumb_class" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @$post_meta['_sm_featured_gallery_thumb_class'][0]; ?>" />
				<br />
			</div>
		</div>
		<div id="sm_featured_gallery_hyperlink_options">
			<label for="sm_featured_gallery_hyperlink">Link</label><br />
			<input name="sm_featured_gallery_hyperlink" id="sm_featured_gallery_hyperlink" type="text" style="margin-bottom:10px;width:100%;" value="<?php echo @esc_url($post_meta['_sm_featured_gallery_hyperlink'][0]); ?>" />
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

	//handle the save function for the featured image gallery form
	function featured_gallery_save($post_id){
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

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
						update_post_meta($post_id, '_'.$key, sanitize_text_field($value) );
					else
						delete_post_meta($post_id, '_'.$key);
				}
			}
		}
	}
}