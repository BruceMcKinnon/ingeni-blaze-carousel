=== Ingeni Blaze Carousel ===

Contributors: Bruce McKinnon
Tags: carousel, swiper
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 2025.01

A Blaze-based carousel, that provides support for content sourced from a folder relative to the home URL.

Also allows content to be sources from WP posts (e.g., content blocks)

NB - Does not currently support thumbnail navigation.



== Description ==

* - Images are added by adding them to a folder (hosted on the web server).

* - Based on Blaze Slider - https://blaze-slider.dev/




== Installation ==

1. Upload the 'ingeni-blaze-carousel' folder to the '/wp-content/plugins/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Display the carousel using the shortcode



== Frequently Asked Questions ==



= How do a display the carousel? =

Use the shortcode [ingeni-blaze]

The following parameters may be included:



source_path: Directory relative to the home page that contains the images to b displayed. Defaults to '/photos-bucket/',

wrapper_class: Wrapping class name. Defaults to 'ingeni-swiper-wrap'.

show_thumbs: Display a horzontal list of thumbnails below the main image. Defaults to 1 (show thumbnails). Used inconjunction with sync_thumbs.

sync_thumbs: Keep the main image and thumbnail list in sync. Defaults to 1 (equals sync the thumbnails). Used inconjunction with show_thumbs.

max_thumbs: Max. number of thumbnails to display. Defaults to 0 (show all thumbnails).

show_arrows: Show navigation arrows. Defaults to 1 (show arrows).

show_dots: Show navigation dots. Defaults to 0 (show dots).

shuffle: Randomly shuffle the order of the images. Defaults to 1 (shuffle images).

speed: msecs to display image before moving to the next. Defaults to 2000 (2 secs).

bg_images: Display images as background images. Default = 0 (foreground images)

category: Display the featured images from posts of a specific category. Provide the category name as the parameter value.

file_ids: Comma separated list of media library file IDs. Easy way to get this list is to create a post gallery of the required images. The standard [gallery] shortcode contains a list of file IDs.

post_ids: Comma separated list of post IDs.

post_type: Used in-conjunction with the post_ids parameter. E.g., ‘post’, ‘page’. Defaults to ’content_block’.

orderby: Order in which the slides appear. Used in-conjunction with the post_ids parameter. E.g., ‘post__in’. Defaults to ‘title’.

order: Defaults to 'ASC'

center_mode: When using variable width, center the image in the div. Defaults to 0.

variable_width: Cope with variable width images. Default to 0. 

fade: Defaults to 1 for fade transitions. NB, slide transition is forced when using variable_width and center_mode. 

adaptive_height - When set to 1, enables adaptive height for single slide horizontal carousels. Default = 0

thumbnail_size - If displaying the thumbnail or featured image of a post, specify the size to use. Default is 'full',

show_title - If showing a carousel of images, set to 1 to have the image title displayed. Default = 0

translucent_layer_class - Specify the a translucent class name. Default = "".

link_post => Set to 1 to linking to slides sourced from posts. Default = 0

delay_start - Msec to delay video/slider start. Defaults to 0. Max value = 60000.

slides_to_show - Number of slides to show at one time. Default = 1

slides_to_scroll  - Number of slides to scroll in a single scroll. Default = 1

show_content - If 1, display content from a post to be used an an overlay - e.g., text overlaying image. Defaults to 0

template - Specify a slider template. Will search in the {theme}/ingeni-swiper-templates and then the plugin template folder for a matching template file.

template_function_call: specify the calling function in a template file. Defaults to 'do_swiper_template'. Required when multiple sliders on a single page.

responsive_breakpoints: Comma delimited string containing responsive breakpoints. For example: "640,1024". Default is blank = non-responsive display.

responsive_slides_to_show: Comma delimited string containing number of slides to show for each breakpoint. For example: "2,3". Defaults is blank = non-responsive display.

slider_class - specify unique JS class to permit multiple sliders on a single page. Defaults to 
'slider-'.

pause_on_hover - If set to 0 hovering over slider won't pause it. Defaults to 1 - pause on hover.



== Examples ==


One image background images:

[ingeni-blaze source_path="/assets/2020/home-photos/" show_arrows=1 show_dots=0 speed=3000 bg_images=0]



Three image carousel:

[ingeni-blaze source_path="/products/" show_thumbs=0 show_arrows=1 show_dots=0 slides_to_show=3 slides_to_scroll=3 center_mode=1 variable_width=0 wrapper_class="product_slider" speed=5000 fade=0]



Responsive slider. Small = 1, Medium = 2, Large = 3:

[ingeni-swiper source_path="/products/" show_thumbs=0 show_arrows=1 show_dots=0 slides_to_show=1 slides_to_scroll=1 center_mode=1 variable_width=0 wrapper_class="product_slider" speed=3000 fade=0 bg_images=1 responsive_breakpoints="640,1024" responsive_slides_to_show="2,3"]





== Changelog ==

v2025.01 - Based on ingeni-slick-carousel v2023.06



