<?php
/*
Plugin Name: Ingeni Blaze Carousel
Version: 2025.01

Plugin URI: https://ingeni.net
Author: Bruce McKinnon - ingeni.net
Author URI: https://ingeni.net
Description: Blaze-based carousel for Wordpress
*/

/*
Copyright (c) 2025 Ingeni Web Solutions
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

Disclaimer: 
	Use at your own risk. No warranty expressed or implied is provided.
	This program is free software; you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 	See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Requires : Wordpress 6.x or newer ,PHP 7.4 +

v2025.01 - Initial version, based on Ingeni Slick Carousel v2023.06

*/

if (!function_exists("ingeni_blaze_log")) {
	function ingeni_blaze_log($msg) {
		$upload_dir = wp_upload_dir();
		$logFile = $upload_dir['basedir'] . '/' . 'ingeni_blaze_log.txt';
		date_default_timezone_set('Australia/Sydney');

		// Now write out to the file
		$log_handle = fopen($logFile, "a");
		if ($log_handle !== false) {
			fwrite($log_handle, date("H:i:s").": ".$msg."\r\n");
			fclose($log_handle);
		}
	}
}

if (!function_exists("endsWith")) {
	function endsWith($haystack, $needle) {
			// search forward starting from end minus needle length characters
			return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}


add_shortcode( 'ingeni-blaze','do_ingeni_blaze' );
function do_ingeni_blaze( $args ) {

	$params = shortcode_atts( array(
		'source_path' => '/photos-bucket/',
		'slider_class' => 'blaze-slider',
		'wrapper_class' => 'ingeni_blaze_container',
		'slider_thumbs_class' => 'ingeni_blaze_thumbs',
		'max_thumbs' => -1,
		'show_thumbs' => 0,
		'show_arrows' => 1,
		'show_dots' => 0,
		'loop' => 1,
		'shuffle' => 1,
		'file_list' => "",
		'file_ids' => "",
		'file_path' => "",
		'autoplay' => 1,
		'start_path' => "",
		'bg_images' => 0,
		'category' => '',
		'speed' => 2000,
		'post_ids' => '',
		'post_type' => 'content_block',
		'orderby' => 'title',
		'order' => 'ASC',
		'center_mode' => 0,
		'variable_width' => 0,
		'fade' => 1,
		'adaptive_height' => 1,
		'thumbnail_size' => 'full',
		'show_title' => 0,
		'translucent_layer_class' => '',
		'link_post' => 0,
		'slides_to_show' => 1,
		'slides_to_scroll' => 1,
		'delay_start' => 0,
		'slides_to_scroll' => 1,
		'show_content' => 0,
		'template' => '',
		'template_function_call' => 'do_ingeni_blaze_template',
		'responsive_breakpoints' => '',
		'responsive_slides_to_show' => '',
		'pause_on_hover' => 1,
		'image_size' => 'full',
	), $args );

//fb_log('blaze:'.print_r($params,true));

	$titles = array();
	$captions = array();
	$alt_texts = array();
	$links = array();
	$content = array();

	$photos = null;
	$home_path = '';

	$slider_class = $params['slider_class'];
	$slider_thumbs_class = $params['slider_thumbs_class'] ;

	$unique_id = trim(uniqid());
	$slider_id = "blaze" .$unique_id ;
	$slider_thumb_id = "thumbs" . $unique_id;
	$wrapper_id = "wrapper" . $unique_id;

	
	$sort_order = strtoupper($params['order']);
	if ($params['order'] != 'DESC') {
		$sort_order = 'ASC';
	}

//ingeni_blaze_log('params:'.print_r($params,true));

		// Attempt to load a template file
		$template_file = '';
		if ( $params['template'] != '' ) {

			if ( file_exists( plugin_dir_path( __FILE__ ) . '/templates/'.$params['template'] ) ) {
				$template_file = plugin_dir_path( __FILE__ ) . '/templates/'.$params['template'];
			}
			if ( file_exists( get_template_directory() .'/ingeni-blaze-templates/'.$params['template'] ) ) {
				$template_file = get_template_directory() .'/ingeni-blaze-templates/'.$params['template'];
			}
			if ( file_exists( get_stylesheet_directory() .'/ingeni-blaze-templates/'.$params['template'] ) ) {
				$template_file = get_stylesheet_directory() .'/ingeni-blaze-templates/'.$params['template'];
			}
		}
//ingeni_blaze_log('template_file:'.$template_file);

		if ( file_exists( $template_file ) ) {
//ingeni_blaze_log('template_file exists!');
			// Template-based  content
			include_once($template_file);

			$id_array = array();
			if (strlen($params['post_ids']) > 0) {
				$id_array = explode(",",$params['post_ids']);
			}
	
			if ( function_exists($params['template_function_call']) ) {
//ingeni_blaze_log('function exists!');
				// Handle WooCommerce products

				if ( $params['post_type'] == 'product' ) {
					$args = array(
						'orderby' => $params['orderby'],
						'order' => $sort_order,
						'numberposts' => $params['max_thumbs'],
						'post_type' => $params['post_type'],
						'tax_query' => array(
							array(
								'taxonomy' => 'product_cat',
								'terms' => array_map( 'sanitize_title', explode( ',', $params['category'] ) ),
								'field' => 'slug',
								'operator' => 'AND',
							)
						)
					);

					if ($id_array) {
						$args = array_merge($args, array('post__in' => $id_array) );
					}

				} elseif ( strlen($params['post_ids']) > 0 ) {
					//
					// Post IDs supplied
					//
					$id_array = explode(",",$params['post_ids']);

					$args = array(
						'post__in' => $id_array,
						'posts_per_page' => $params['max_thumbs'],
						'orderby' => $params['orderby'],
						'post_type' => $params['post_type']
					);

				} else {
					$args = array(
						'category_name' => $params['category'],
						'post_type' => $params['post_type'],
						'orderby' => $params['orderby'],
						'order' => $sort_order,
						'numberposts' => $params['max_thumbs'],
					);

//ingeni_blaze_log('args:'.print_r($args,true));

					if ($id_array) {
						$args = array_merge($args, array('post__in' => $id_array) );
					}
				}

				$idx = 0;
				$content_post = get_posts( $args );

				$inline_style = "";
	
				$sync1 = "";
				$sync2 = "";
	
	
				foreach( $content_post as $post ) {
//ingeni_blaze_log('template call: '.$params['template_function_call']);
	
					$sync1 .= '<div class="blaze_slide">' . call_user_func( $params['template_function_call'], $post ) . '</div>';
//ingeni_blaze_log('sync:'.$sync1);
				}
			}
		} elseif ( strlen($params['post_ids']) > 0 ) {
//ingeni_blaze_log('no template. post ids');
		//
		// Content based slides
		//
		$id_array = explode(",",$params['post_ids']);

		$args = array(
			'post__in' => $id_array,
			'post_type' => $params['post_type'],
			'class' => 'content_block_featured',
			'orderby' => $params['orderby'],
			'order' => $sort_order,
			'posts_per_page' => $params['max_thumbs'],
		);
//fb_log(print_r($args,true));
		$idx = 0;
		$content_post = get_posts( $args );

//fb_log(print_r($content_post,true));
		$inline_style = "";

		$sync1 = "";
		$sync2 = "";


		$content = array();

		foreach( $content_post as $post ) {

			if ( has_post_thumbnail( $post->ID ) || ( $params['post_type'] == 'attachment' ) ) {
				// v2023.03 - Changed logic so the GUID is used as a fallback, rather than first choice.
				// This allows a smaller image to be selected.

				$thumb_id = get_post_thumbnail_id($post->ID);

				if ($thumb_id) {
					$thumb_url = wp_get_attachment_image_src($thumb_id,$params['image_size'], false);
				} else {
					$thumb_url = wp_get_attachment_image_src($post->ID,$params['image_size'], false);
				}

				if ($thumb_url) {
					$style = 'background-image: url('. $thumb_url[0] .')';
				} else {
					$thumb_url = $post->guid;
					$style = 'background-image: url('. $thumb_url .')';
				}

				
				$title = get_the_title($post->ID);
				$caption = wp_get_attachment_caption($post->ID);
				array_push( $titles, $title );
				array_push( $captions, $caption );

				if (strlen($caption) > 0 ) {
					$title = $caption;
				}
				array_push( $links, get_the_permalink($post->ID) );
				array_push( $content, get_the_content($post->ID) );

				if ($params['link_post'] > 0) {
					$sync1 .= '<a href="'.get_the_permalink($post->ID).'">';
				}

				$sync1 .= '<div class="blaze_slide"><div style="'.$style.'"><div class="title-layer"><h3>' .$title .'</h3></div></div></div>';

				if ($params['link_post'] > 0) {
					$sync1 .= '</a>';
				}


			} else {
				$sync1 .= '<div class="blaze_slide">' . apply_filters('the_content', $post->post_content) . '</div>';
			}

			++$idx;
			if ( ($idx > $params['max_thumbs']) && ($params['max_thumbs'] > 0) ) {
				break;
			}
		}

	}	else {
//ingeni_blaze_log('image based');
		//
		// Image-based slides
		//
		if ( strlen($params['category']) > 0 ) {
//ingeni_blaze_log('cat');
			$photos = array();

			$order_by = $params['orderby'];
			if ( $params['shuffle'] > 0) {
				$order_by = 'rand';
			}

			$sort_order = strtoupper($params['order']);
			if ($params['order'] != 'DESC') {
				$sort_order = 'ASC';
			}

			$post_attribs = array (
				'posts_per_page' => $params['max_thumbs'],
				'offset' => 0,
				'category_name' => $params['category'],
				'orderby' => $order_by,
				'order' => $sort_order,
			);

			//ingeni_blaze_log(print_r($post_attribs,true));
			$myquery = new WP_Query( $post_attribs );
		
			if ( $myquery->have_posts() ) {
				while ( $myquery->have_posts() ) {
					$myquery->the_post();
					$thumb_url = get_the_post_thumbnail_url( get_the_ID(), $params['thumbnail_size'] );

					array_push( $photos, $thumb_url );
					array_push( $titles, get_the_title() );
					array_push( $links, get_the_permalink() );
					array_push( $content, get_the_content() );
				}
			}

		} elseif ( strlen($params['file_list']) > 0 ) {
//ingeni_blaze_log('file list');
			//
			// A list of file names were passed in
			//
			$photos = explode(",",$params['file_list']);
			$home_path = $params['file_path'];

		} elseif ( strlen($params['file_ids']) > 0 ) {
//ingeni_blaze_log('file ids');
			//
			// If a list of media ID, get the source URLs and create a file_list
			//
			$photos = array();
			$home_path = "";
			//ingeni_blaze_log('file ids='.$params['file_ids']);

			$media_ids = array();
			$media_ids = explode(",",$params['file_ids']);

			$source_urls = "";
			$idx = 0;


			foreach($media_ids as $media_id) {
				$source_urls .= wp_get_attachment_url( $media_id ) . ',';
			}
			$source_urls = substr($source_urls,0,strlen($source_urls)-1);

			$params['file_list'] = $source_urls;
			$params['file_path'] = "";

			// Now prepare the list of the slider
			$photos = explode(",",$params['file_list']);
			$home_path = $params['file_path'];

		
		} else {
			try {
				if ($params['start_path'] != '') {
					chdir($params['start_path']);
				}
//ingeni_blaze_log('curr path:'.getcwd() .'|'.$params['source_path']);
				$root_dir = getcwd();
				if (stripos($root_dir, '/wp-admin') !== FALSE ) {
					$root_dir = str_ireplace('/wp-admin','',$root_dir);
				}
				if ( !file_exists($root_dir . $params['source_path']) ) {
					throw new Exception('Path does not exist: '.$root_dir . $params['source_path']);
				} else {
					$photos = scandir( $root_dir . $params['source_path']);
					if (!$photos) {
						throw new Exception('Error while scanning: '.$root_dir . $params['source_path']);
					}
				}
			} catch (Exception $ex) {
				if ( function_exists("ingeni_blaze_log") ) {
					ingeni_blaze_log('Scanning folder '.$params['source_path'].' : '.$ex->getMessage());
				}
			}
			$home_path = get_bloginfo('url') . $params['source_path'];
		}

		$sync1 = "";
		$sync2 = "";


		if ($params['bg_images'] == 1) {
			$params['adaptive_height'] = 'false';
		} else {
			if ($params['adaptive_height'] == 0) {
				$params['adaptive_height'] = 'false';
			} else {
				$params['adaptive_height'] = 'true';
			}			
		}

		$idx = 0;
		if ( ($params['shuffle'] > 0) && ($params['show_title'] == 0) ) {
			if ( $photos ) {
				shuffle($photos);
			}
		}
//ingeni_blaze_log('photos to show: '.print_r($photos,true));

		if ( !$photos ) {
			// We have no photos
			return '<div class="'.$params['blaze_class'].'"><p>Sorry, nothing to show!</p></div>';

		} else {
			foreach ($photos as $photo) {
				if ( (strpos(strtolower($photo),'.webp') !== false) || (strpos(strtolower($photo),'.jpg') !== false) || (strpos(strtolower($photo),'.png') !== false)  || (strpos(strtolower($photo),'.mp4') !== false) ) {		
	//ingeni_blaze_log('photo to show: '.$home_path . $photo);
					if ($params['bg_images'] > 0) {

						if ($params['link_post'] > 0) {
							$sync1 .= '<a href="'.$links[$idx].'">';
						}

						if ( endsWith($photo,'.mp4') ) {

							// Disable autoplay if the first slide is a video
							if ($idx == 0) {
								$params['autoplay'] = false;
							}

							$sync1 .= '<div class="blaze_slide"><div class="blaze-video-wrap hide-for-small" >';
							$sync1 .= '<video class="blaze-video" id="blaze-video-'.$idx.'"muted preload data-origin-x="0" data-origin-y="0" >';
								$source_img = get_bloginfo('url') . '/' .$params['source_path'] .'/' . $photo;
								$source_img = str_replace('\/\/','\/',$source_img);
							$sync1 .= '<source src="' . $source_img . '" type="video/mp4">';
							$sync1 .= 'Your browser does not support the video tag.</video>';

						} else {
							$sync1 .= '<div class="blaze_slide" id="blaze-image-'.$idx.'"><div class="bg-item" style="background-image:url('. $home_path . $photo .')" data="'. $home_path . $photo .'" draggable="false">';
						}

						if ($params['translucent_layer_class'] !== '') {
							$sync1 .= '<div class="' . $params['translucent_layer_class'] . '"></div>';
						}
						if ($params['show_title'] > 0) {

							if ( count($titles) > $idx ) {
								$slide_title = $titles[$idx];
							} else {
								$slide_title = '';
							}

							$sync1 .= '<div class="slide_title">' . $slide_title . '</div>';

						} elseif ($params['show_content'] > 0) {
							// v2020.06 - Insert the content from a post
							if ( count($content) > $idx ) {
								$slide_content = $content[$idx];
							} else {
								$slide_content = '';
							}

							$sync1 .= '<div class="slide_content">' . $slide_content . '</div>';
						}

						$sync1 .= '</div></div>';

						if ($params['link_post'] > 0) {
							$sync1 .= '</a>';
						}
		
					} else {
	//ingeni_blaze_log($home_path . $photo);
						$sync1 .= '<div class="blaze_slide"><img src="'. $home_path . $photo .'" draggable="false" loading="lazy"></img></div>';
					}
					++$idx;
					if ( ($idx > $params['max_thumbs']) && ($params['max_thumbs'] > 0) ) {
						break;
					}
				}
			}
		}
	}


	$sync2 = str_replace($links,"#",$sync1);

	
	//ingeni_blaze_log('links: '.print_r($links,true));
	//ingeni_blaze_log('titles: '.print_r($titles,true));
	

	//if ( (!is_int($params['slides_to_show']) ) || ($params['slides_to_show'] < 0) ) {
		//$params['slides_to_show'] = 1;
	//}
	//if ( (!is_int($params['slides_to_scroll']) ) || ($params['slides_to_scroll'] < 1) ) {
		//$params['slides_to_scroll'] = 1;
	//}


	if ( $params['slides_to_show'] < 1 ) {
		$params['slides_to_show'] = 1;
		$params['fade'] = "false";
	}

	// Grab the responsive settings
	$responsive_config = '';
	if ($params['responsive_breakpoints'] && $params['responsive_slides_to_show'] ) {
		$resp_breaks = explode(',',$params['responsive_breakpoints']);
		$resp_slides = explode(',',$params['responsive_slides_to_show']);

		$num_breaks = count($resp_breaks);
		if ( $num_breaks == count($resp_slides) ) {
			for ( $idx = 0; $idx < $num_breaks; ++$idx ) {
				$responsive_config .= "'(min-width:".$resp_breaks[$idx]."px)':{slidesToShow:".$resp_slides[$idx].",},";
			}
		}
	}


	if ($params['autoplay'] == 1) {
		$params['autoplay'] = 'true';
	} else {
		$params['autoplay'] = 'false';
	}
	if ($params['show_arrows'] == 1) {
		$params['show_arrows'] = 'true';
	} else {
		$params['show_arrows'] = 'false';
	}
	if ($params['show_dots'] == 1) {
		$params['show_dots'] = 'true';
	} else {
		$params['show_dots'] = 'false';
	}

	if ($params['fade']) {
		$params['fade'] = 1;
	} else {
		$params['fade'] = 0;
	}

	if ($params['loop'] == 1) {
		$params['loop'] = 'true';
	} else {
		$params['loop'] = 'false';
	}


	/*
	$watchSlidesProgress = 'false';

	if ($params['show_thumbs']  > 0) {
		$sync2 = '<div thumbsSlider="" class="blaze '.$slider_thumbs_class.'">' . $sync1 . '</div>';
		$watchSlidesProgress = 'true';
	} else {
		$sync2 = '';
	}
	*/
	


	// Can't use fade and centerMode/variableWidth together.
	if ( ($params['center_mode'] == 1) && ($params['variable_width'] == 1) ) {
		$params['fade'] = 'false';
	}

	if ($params['center_mode'] == 1) {
		$params['center_mode'] = 'true';
	} else {
		$params['center_mode'] = 'false';
	}

	if ($params['pause_on_hover'] == 1) {
		$params['pause_on_hover'] = 'true';
	} else {
		$params['pause_on_hover'] = 'false';
	}

	if ($params['variable_width'] == 1) {
		$params['variable_width'] = 'true';
	} else {
		$params['variable_width'] = 'false';
	}


	if ( !is_numeric( $params['delay_start'] ) ) {
		$params['delay_start']  = 0;
	} else {
		$params['delay_start'] = intval($params['delay_start']);
	}

	if ( $params['delay_start'] < 0 ) {
		$params['delay_start'] = 0;
	} elseif ( $params['delay_start'] > 60000 ) {
		$params['variable_width'] = 60000;
	}



	$js = "<script>var $ = jQuery();";
	
	$js .= "jQuery(document).ready(function(){";


	// Arrows and dots navigation
	$controls_html = '';
	if ( ( $params['show_dots'] == 'true' ) || ( $params['show_arrows'] == 'true' ) ) {
		$controls_html = '<div class="controls"><button class="blaze-prev"></button><div class="blaze-pagination"></div><button class="blaze-next"></button></div>';
	}

	// Thumbs navigation
	$thumbs_js = '';

	// Setup the thumbs blaze, if required
	/*
	if ($params['show_thumbs'] != 0) {
			
		$js .= "console.log('initialising blaze for ".$slider_thumbs_class."');
		var blaze_thumbs = new Blaze('.".$slider_thumbs_class."', {
			loop: ".$params['loop'].",
			spaceBetween: 10,
			slidesPerView: 4,
			freeMode: true,
			watchSlidesProgress: ".$watchSlidesProgress.",
		});";

		$thumbs_js = "thumbs: { blaze: blaze_thumbs, },";
	}
	*/

	// Setup the main swipe
	$js .= "console.log('initialising blaze for #".$slider_id."');

		var myblaze = document.querySelector('#".$slider_id."');
		new BlazeSlider(myblaze, {
			all: {
				slidesToShow: " . $params['slides_to_show'] . ",
				slidesToScroll: " . $params['slides_to_scroll'] . ",
				enableAutoplay: " . $params['autoplay'] . ",
				loop: ".$params['loop'] . ",
				autoplayInterval: " . $params['speed'] . ",
				enablePagination: " . $params['show_arrows'] . ",
				stopAutoplayOnInteraction: " . $params['pause_on_hover'] . ",
			}," . $responsive_config . " }";
				
		$js .= ");";

		// Make sure there are no double commas
		$js = str_replace(",,",",",$js);

	$js .= "});"; // End of document ready

	$js .= "</script>";


	if ($params['show_thumbs'] != 1) {
		$sync2 = '';
	}

	return '<div class="'.$params['wrapper_class'].'"><div class="'.$params['slider_class'].'" id="'.$slider_id.'"><div class="blaze-container"><div class="blaze-track-container"><div class="blaze-track">'.$sync1.'</div></div></div>'.$sync2.$controls_html.'</div></div>'.$js;
}


function ingeni_load_blaze() {
	$dir = plugins_url( 'blaze/', __FILE__ );

	// blaze slider
	wp_enqueue_style( 'blaze-css', 'https://unpkg.com/blaze-slider@latest/dist/blaze.css' );

	wp_register_script( 'blaze_js', 'https://unpkg.com/blaze-slider@latest/dist/blaze-slider.min.js', array('jquery'), null, true );
	wp_enqueue_script( 'blaze_js' );

	//
	// Plugin CSS
	//
	wp_enqueue_style( 'ingeni-blaze-css', plugins_url('ingeni-blaze-carousel.css', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'ingeni_load_blaze' );


function ingeni_update_blaze() {
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/BruceMcKinnon/ingeni-blaze-carousel',
		__FILE__,
		'ingeni-blaze-carousel'
	);
	
	//Optional: If you're using a private repository, specify the access token like this:
	//$myUpdateChecker->setAuthentication('your-token-here');
	
	//Optional: Set the branch that contains the stable release.
	//$myUpdateChecker->setBranch('stable-branch-name');

}
add_action( 'init', 'ingeni_update_blaze' );


// Plugin activation/deactivation hooks
function ingeni_blaze_activation() {
	flush_rewrite_rules( false );
}
register_activation_hook(__FILE__, 'ingeni_blaze_activation');

function ingeni_blaze_deactivation() {
  flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'ingeni_blaze_deactivation' );

?>