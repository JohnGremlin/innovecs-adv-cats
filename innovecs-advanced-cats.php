<?php
/*
Plugin Name: Innovecs Advanced Cats
Plugin URI: http://github.com
Description: This plugin extends categories with image and video addition.
Version: 1.0
Author: ZeroTool
Author URI: http://github.com
*/
?>
<?php
/**
 * Plugin class
 **/
if ( ! class_exists( 'IV_TAX_META' ) ) {

	class IV_TAX_META {

		public function __construct() {
			//
		}

		/*
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		*/
		public function init() {
			add_action( 'category_add_form_fields', array( $this, 'add_category_image' ), 10, 2 );
			add_action( 'category_add_form_fields', array( $this, 'add_category_video' ), 10, 2 );
			add_action( 'created_category', array( $this, 'save_category_video' ), 10, 1 );
			add_action( 'edited_category', array( $this, 'save_category_video' ), 10, 1 );

			add_action( 'created_category', array( $this, 'save_category_image' ), 10, 2 );
			add_action( 'category_edit_form_fields', array( $this, 'update_category_video' ), 10, 2 );
			add_action( 'category_edit_form_fields', array( $this, 'update_category_image' ), 10, 2 );
			add_action( 'edited_category', array( $this, 'updated_category_video' ), 10, 2 );
			add_action( 'edited_category', array( $this, 'updated_category_image' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
			add_action( 'admin_footer', array( $this, 'add_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_plugin_scripts' ) );

			add_filter( 'wp_footer', array( $this, 'category_template' ) );
		}

		public function load_media() {
			wp_enqueue_media();

			//scripts registation
			wp_register_script( 'admin-custom', plugin_dir_url( __FILE__ ) . 'assets/js/admin-custom.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script('admin-custom');
		}

		public function add_plugin_scripts(){
			//styles registation
			wp_register_style( 'magnific-popup', plugin_dir_url( __FILE__ ) . 'assets/css/magnific-popup.css', false, '1.1', 'all');

			//scripts registation
			wp_register_script( 'magnific-popup', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ), $this->version, false );


			wp_enqueue_style('magnific-popup');
			wp_enqueue_script('magnific-popup');
		}

		/*
		 * Extending category template with image/youtube/video 
		 * @since 1.0.0
		*/
		function category_template( $template ) {
			global $wp_embed;

			if ( is_category() ) {
				$category         		= get_category( get_query_var( 'cat' ) );
				$cat_id           		= $category->cat_ID;
				$image_id         		= get_term_meta( $cat_id, 'category-image-id', true );
				$category_video_checked = get_term_meta( $cat_id, 'category_video', true );
				$youtube_url	  		= get_term_meta( $cat_id, 'category-video-youtube', true);
				$video_id	      		= get_term_meta( $cat_id, 'category-video-id', true );
				$video_url 		  		= wp_get_attachment_url( $video_id );
				$cat_custom_class 		= get_term_meta( $cat_id, 'category-title-class', true );
				$embed_youtube			= $wp_embed->run_shortcode('[embed]'. $youtube_url .'[/embed]');

				if ( ! empty( $cat_custom_class ) ) {
					$cat_custom_class = '.' . $cat_custom_class;
				}
				?>
                <script>
                  var cat_image = '<?php echo wp_get_attachment_image( $image_id, $size = 'large' );?>'
                  var cat_youtube = '<?php echo $youtube_url; ?>'
                  var cat_simple_video = '<?php echo $video_url ?>'
                  var cat_video = '<?php echo $category_video_checked; ?>'
                  var cat_custom_class = '<?php echo $cat_custom_class; ?>'

                  if (cat_custom_class.length > 1 & cat_image.length > 0) {
                  	if (cat_youtube.length > 1 && cat_video === 'youtube'){
                  		jQuery(cat_custom_class).after('<a class="youtube-popup-link" href="'+cat_youtube+'">'+cat_image+'</a>')
                  		jQuery('.youtube-popup-link').magnificPopup({
							type: 'iframe'
						})
                  	}else if (cat_simple_video.length > 1 && cat_video === 'video'){
                  		jQuery(cat_custom_class).after('<a class="video-popup-link" href="'+cat_simple_video+'">'+cat_image+'</a>')
                  		jQuery('.video-popup-link').magnificPopup({
							type: 'iframe'
						})
                  	}else{
                  		jQuery(cat_custom_class).after(cat_image)
                  	}
                  } else if (cat_custom_class.length === 0 && cat_image.length > 1) {
                    if (cat_youtube.length > 1 && cat_video === 'youtube'){
                  		jQuery('h1.page-title').after('<a class="youtube-popup-link" href="'+cat_youtube+'">'+cat_image+'</a>')
                  		jQuery('.youtube-popup-link').magnificPopup({
							type: 'iframe'
						})
                  	}else if (cat_simple_video.length > 1 && cat_video === 'video'){
                  		jQuery('h1.page-title').after('<a class="video-popup-link" href="'+cat_simple_video+'">'+cat_image+'</a>')
                  		jQuery('.video-popup-link').magnificPopup({
							type: 'iframe'
						})
                  	}else{
                  		jQuery('h1.page-title').after(cat_image)
                  	}
                  } else if ( cat_custom_class.length > 1 && cat_image.length === 0 ) {
                  	if (cat_youtube.length > 1 && cat_video === 'youtube'){
                  		var youtube_embed = '<?php echo $embed_youtube; ?>'
                  		jQuery(cat_custom_class).after(youtube_embed)
                  		
                  	}else if (cat_simple_video.length > 1 && cat_video === 'video'){

                  		    jQuery('h1.page-title').after(
                  			'<video class="wp-video-shortcode" id="video-<?php echo $video_id; ?>" width="640" height="360" preload="auto" controls="controls"><source type="video/mp4" src='+cat_simple_video+' /><a href="video.mp4">video.mp4</a></video>')

                  	}else{
                  		//
                  	}
                  }else if ( cat_custom_class.length === 0 && cat_image.length === 0 ) {
                  	if (cat_youtube.length > 1 && cat_video === 'youtube'){
                  		var youtube_embed = '<?php echo $embed_youtube; ?>'
                  		jQuery('h1.page-title').after(youtube_embed)
                  		
                  	}else if (cat_simple_video.length > 1 && cat_video === 'video'){

                  		jQuery('h1.page-title').after(
                  			'<video class="wp-video-shortcode" id="video-<?php echo $video_id; ?>" width="640" height="360" preload="auto" controls="controls"><source type="video/mp4" src='+cat_simple_video+' /><a href="video.mp4">video.mp4</a></video>')

                  	}else{
                  		//
                  	}
                  }
                </script>

				<?php
			}

			return $template;
		}

		/*
		  * Add a form video fields in the new category page
		  * @since 1.0.0
		 */
		public function add_category_video( $taxonomy ) { ?>
            <div class="form-field term-group video-container">
                <label for="category-image-id"><?php _e( 'Select Video Type', 'innovec-theme' ); ?></label>
                <p>
                    <input type="radio" name="category_video" id="category_video_youtube"
                           value="<?php _e( 'youtube', 'innovec-theme' ); ?>" <?php checked( 'youtube', get_option( 'category_video' ), true ); ?>>youtube
                    <input type="radio" name="category_video" id="category_video"
                           value="<?php _e( 'video', 'innovec-theme' ); ?>" <?php checked( 'video', get_option( 'category_video' ), true ); ?>>video
            <div class="form-field term-group youtube">
                        <label for="category-video-youtube"><?php _e( 'YouTube Video', 'innovec-theme' ); ?></label>
                <p>
                    <input type="text" id="category-video-youtube" name="category-video-youtube"
                           value="<?php echo $title_class; ?>">
                <p class="description">Please insert the youtube video url.</p>
                </p>
            </div>
            <div class="form-field term-group video">
                <label for="category-video-id"><?php _e( 'Video', 'innovec-theme' ); ?></label>
                <input type="hidden" id="category-video-id" name="category-video-id" class="custom_media_url" value="">
                <div id="category-video-wrapper"></div>
                <p>
                    <input type="button" class="button button-secondary tax_media_video_button"
                           id="tax_media_video_button" name="tax_media_video_button"
                           value="<?php _e( 'Add Video', 'innovec-theme' ); ?>"/>
                    <input type="button" class="button button-secondary tax_media_video_remove"
                           id="tax_media_video_remove" name="tax_media_video_remove"
                           value="<?php _e( 'Remove Video', 'innovec-theme' ); ?>"/>
                	<p class="description">Please upload the video.</p>
                </p>
            </div>
            </p>
            </div>
			<?php
		}

		/*
		 * Save the video form field
		 * @since 1.0.0
		*/
		public function save_category_video( $term_id ) {
			$category_video_checked = $_POST['category_video'];
			$video_youtube          = $_POST['category-video-youtube'];
			//var_dump( $_POST );
			if ( ! empty( $_POST['category-video-id'] ) && $category_video_checked == 'video' ) {
				$video = $_POST['category-video-id'];
				add_term_meta( $term_id, 'category-video-id', $video, true );
				add_term_meta( $term_id, 'category_video', $category_video_checked, true );
				add_term_meta( $term_id, 'category-video-youtube', $video_youtube, true );
			}
			if ( $category_video_checked == 'youtube' ) {
				add_term_meta( $term_id, 'category-video-id', $video, true );
				add_term_meta( $term_id, 'category-video-youtube', $video_youtube, true );
				add_term_meta( $term_id, 'category_video', $category_video_checked, true );
			}
		}

		/*
		 * Edit the video form field
		 * @since 1.0.0
		*/
		public function update_category_video( $term, $taxonomy ) { ?>

            <tr class="form-field term-group-wrap video-container">
                <th scope="row">
                    <label for="category-video-id"><?php _e( 'Video', 'innovec-theme' ); ?></label>
                </th>
                <td>
					<?php $video_id      = get_term_meta( $term->term_id, 'category-video-id', true );
					$category_video_type = get_term_meta( $term->term_id, 'category_video', true );
					$video_youtube       = get_term_meta( $term->term_id, 'category-video-youtube', true ); ?>

                    <input type="radio" name="category_video" id="category_video_youtube"
                           value="<?php _e( 'youtube', 'innovec-theme' ); ?>" <?php checked( 'youtube', $category_video_type, true ); ?>>
                    youtube
                    <input type="radio" name="category_video" id="category_video"
                           value="<?php _e( 'video', 'innovec-theme' ); ?>" <?php checked( 'video', $category_video_type, true ); ?>>video
                        <div class="form-field term-group youtube"
                             style="display: <?php if( $category_video_type === 'youtube' ){echo 'block';}else{echo 'none';} ?>">
                            <label for="category-video-youtube"><?php _e( 'YouTube Video', 'innovec-theme' ); ?></label>
                            <input type="hidden" id="category-video-id" name="category-video-id" class="custom_media_url" value="<?php echo $video_id;?>">
		                    <p>
		                        <input type="text" id="category-video-youtube" name="category-video-youtube"
		                               value="<?php echo $video_youtube; ?>">
		                    <p class="description">Please insert the youtube video url.</p>
						</div>
	                    <div class="form-field term-group video"  
	                    	 style="display: <?php if( $category_video_type === 'video' ){echo 'block';}else{echo 'none';} ?>">
	                        <label for="category-video-id"><?php _e( 'Video', 'innovec-theme' ); ?></label>
	                        <input type="hidden" id="category-video-id" name="category-video-id" class="custom_media_url" value="<?php echo $video_id;?>">
	                        <div id="category-video-wrapper">
	                        	<?php if ( $video_id ) { ?>
									<?php echo wp_get_attachment_url( $video_id ); ?>
								<?php } ?>
	                        </div>
	                        <p>
	                            <input type="button" class="button button-secondary tax_media_video_button"
	                                   id="tax_media_video_button" name="tax_media_video_button"
	                                   value="<?php _e( 'Add/Edit Video', 'innovec-theme' ); ?>"/>
	                            <input type="button" class="button button-secondary tax_media_video_remove"
	                                   id="tax_media_video_remove" name="tax_media_video_remove"
	                                   value="<?php _e( 'Remove Video', 'innovec-theme' ); ?>"/>
	                        <p class="description">Please upload the video.</p>
	                        </p>
	                    </div>
                </td>
            </tr>
        
			<?php
		}

		/*
		 * Update the video form field value
		 * @since 1.0.0
		 */
		public function updated_category_video( $term_id, $tt_id ) {

			$category_video_checked = $_POST['category_video'];
			$video_youtube          = $_POST['category-video-youtube'];
			$video = $_POST['category-video-id'];
			if ( $category_video_checked == 'video' ) {
				update_term_meta( $term_id, 'category-video-id', $video );
				update_term_meta( $term_id, 'category_video', $category_video_checked );
				update_term_meta( $term_id, 'category-video-youtube', $video_youtube );

			} else {
				update_term_meta( $term_id, 'category_video', '' );
			}
			if ( $category_video_checked == 'youtube' ) {
				update_term_meta( $term_id, 'category-video-id', $video );
				update_term_meta( $term_id, 'category-video-youtube', $video_youtube );
				update_term_meta( $term_id, 'category_video', $category_video_checked );
			} else {
				update_term_meta( $term_id, 'category-video-youtube', '' );
			}
		}

		/*
		 * Add a form field in the new category page
		 * @since 1.0.0
		*/
		public function add_category_image( $taxonomy ) { ?>
            <div class="form-field term-group">
                <label for="category-image-id"><?php _e( 'Image', 'innovec-theme' ); ?></label>
                <input type="hidden" id="category-image-id" name="category-image-id" class="custom_media_url" value="">
                <div id="category-image-wrapper"></div>
                <p>
                    <input type="button" class="button button-secondary tax_media_button" id="tax_media_button"
                           name="tax_media_button" value="<?php _e( 'Add Image', 'innovec-theme' ); ?>"/>
                    <input type="button" class="button button-secondary tax_media_remove" id="tax_media_remove"
                           name="tax_media_remove" value="<?php _e( 'Remove Image', 'innovec-theme' ); ?>"/>
                </p>
            </div>
            <div class="form-field term-group">
                <label for="category-title-class"><?php _e( 'Title class name', 'innovec-theme' ); ?></label>
                <p>
                    <input type="text" id="category-title-class" name="category-title-class"
                           value="<?php echo $title_class; ?>">
                <p class="description">Add the class name of your category title to show the image, or leave it blank,
                    if it's already there.</p>
                </p>
            </div>
			<?php
		}

		/*
		 * Save the form field
		 * @since 1.0.0
		*/
		public function save_category_image( $term_id, $tt_id ) {
			if ( isset( $_POST['category-image-id'] ) && '' !== $_POST['category-image-id'] ) {
				$image = $_POST['category-image-id'];
				add_term_meta( $term_id, 'category-image-id', $image, true );
			}
			if ( isset( $_POST['category-title-class'] ) && '' !== $_POST['category-title-class'] ) {
				$title_class = $_POST['category-title-class'];
				add_term_meta( $term_id, 'category-title-class', $title_class, true );
			}
		}

		/*
		 * Edit the form field
		 * @since 1.0.0
		*/
		public function update_category_image( $term, $taxonomy ) { ?>
            <tr class="form-field term-group-wrap category-image-wrapper">
                <th scope="row">
                    <label for="category-image-id"><?php _e( 'Image', 'innovec-theme' ); ?></label>
                </th>
                <td>
					<?php $image_id = get_term_meta( $term->term_id, 'category-image-id', true ); ?>
                    <input type="hidden" id="category-image-id" name="category-image-id"
                           value="<?php echo $image_id; ?>">
                    <div id="category-image-wrapper">
						<?php if ( $image_id ) { ?>
							<?php echo wp_get_attachment_image( $image_id, 'medium' ); ?>
						<?php } ?>
                    </div>
                    <p>
                        <input type="button" class="button button-secondary tax_media_button"
                               id="tax_media_button" name="tax_media_button"
                               value="<?php _e( 'Add/Edit Image', 'innovec-theme' ); ?>"/>
                        <input type="button" class="button button-secondary tax_media_remove"
                               id="tax_media_remove" name="tax_media_remove"
                               value="<?php _e( 'Remove Image', 'innovec-theme' ); ?>"/>

                    </p>
                </td>
            </tr>
            <tr class="form-field term-group-wrap">
                <th scope="row">
                    <label for="category-title-class"><?php _e( 'Title class name', 'innovec-theme' ); ?></label>
                </th>
                <td>
					<?php $title_class = get_term_meta( $term->term_id, 'category-title-class', true ); ?>
                    <p>
                        <input type="text" id="category-title-class" name="category-title-class"
                               value="<?php echo $title_class; ?>">
                    <p class="description">Add the class name of your category title to show the image, or leave it
                        blank, if it's already there.</p>
                    </p>
                </td>
            </tr>
			<?php
		}

		/*
		 * Update the form field value
		 * @since 1.0.0
		 */
		public function updated_category_image( $term_id, $tt_id ) {
			if ( isset( $_POST['category-image-id'] ) && '' !== $_POST['category-image-id'] ) {
				$image = $_POST['category-image-id'];
				update_term_meta( $term_id, 'category-image-id', $image );
			} else {
				update_term_meta( $term_id, 'category-image-id', '' );
			}
			if ( isset( $_POST['category-title-class'] ) && '' !== $_POST['category-title-class'] ) {
				$title_class = $_POST['category-title-class'];
				update_term_meta( $term_id, 'category-title-class', $title_class );
			} else {
				update_term_meta( $term_id, 'category-title-class', '' );
			}
		}

		/*
		 * Add script
		 * @since 1.0.0
		 */
		public function add_script() { ?>
            <script>
            	function video_media(){

				jQuery(document).ready(function ($) {
                function media_upload (button_class) {
                  var _custom_media = true,
                    _orig_send_attachment = wp.media.editor.send.attachment
                  $('body').on('click', button_class, function (e) {
                    var button_id = '#' + $(this).attr('id')
                    var send_attachment_bkp = wp.media.editor.send.attachment
                    var button = $(button_id)
                    _custom_media = true
                    wp.media.editor.send.attachment = function (props, attachment) {
                      if (_custom_media) {
                        $('#category-video-id').val(attachment.id)
                        $('#category-video-wrapper').html('<video class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />')
                        $('#category-video-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block')
                      } else {
                        return _orig_send_attachment.apply(button_id, [props, attachment])
                      }
                    }
                    wp.media.editor.open(button)
                    return false
                  })
                }

                media_upload('.tax_media_video_button.button')
                $('body').on('click', '.tax_media_video_remove', function () {
                  $('#category-video-id').val('')
                  $('#category-video-wrapper').html('<video class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />')
                })

                $(document).ajaxComplete(function (event, xhr, settings) {
                  var queryStringArr = settings.data.split('&')
                  if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                    var xml = xhr.responseXML
                    $response = $(xml).find('term_id').text()
                    if ($response != '') {
                      // Clear the thumb video
                      $('#category-video-wrapper').html('')
                    }
                  }
                })
              })
              }

              function image_media(){
              jQuery(document).ready(function ($) {
                function media_upload (button_class) {
                  var _custom_media = true,
                    _orig_send_attachment = wp.media.editor.send.attachment
                  $('body').on('click', button_class, function (e) {
                    var button_id = '#' + $(this).attr('id')
                    var send_attachment_bkp = wp.media.editor.send.attachment
                    var button = $(button_id)
                    _custom_media = true
                    wp.media.editor.send.attachment = function (props, attachment) {
                      if (_custom_media) {
                        $('#category-image-id').val(attachment.id)
                        $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />')
                        $('#category-image-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block')
                      } else {
                        return _orig_send_attachment.apply(button_id, [props, attachment])
                      }
                    }
                    wp.media.editor.open(button)
                    return false
                  })
                }

                media_upload('.tax_media_button.button')
                $('body').on('click', '.tax_media_remove', function () {
                  $('#category-image-id').val('')
                  $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />')
                })

                $(document).ajaxComplete(function (event, xhr, settings) {
                  var queryStringArr = settings.data.split('&')
                  if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                    var xml = xhr.responseXML
                    $response = $(xml).find('term_id').text()
                    if ($response != '') {
                      // Clear the thumb image
                      $('#category-image-wrapper').html('')
                    }
                  }
                })
              })
          	}

          	video_media()
          	image_media()
            </script>
		<?php }

	}

	$IV_TAX_META = new IV_TAX_META();
	$IV_TAX_META->init();

}