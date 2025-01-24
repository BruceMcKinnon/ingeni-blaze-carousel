<?php 
//
// Post preview template for Ingeni Blaze Carouse
//

function do_ingeni_blaze_template( $this_post ) {
	try {

		$retHtml = '<div class="blaze-template-wrap post-preview">';

			$retHtml .= '<div class="post-preview-inner"><a href="'.get_the_permalink( $this_post->ID ).'">';
			if ( has_post_thumbnail( $this_post->ID ) ) {
				$thumb_id = get_post_thumbnail_id($this_post->ID);
				$thumb_url = wp_get_attachment_image_src($thumb_id,'full', false);
				$style = 'background-image: url('. $thumb_url[0] .')';

				$retHtml .= '<div class="bg_img" style="'.$style.'"></div>';
			}
			
			$retHtml .= '<div class="title_wrap"><p>'.get_the_title( $this_post->ID ).'</p></div>';
			
			$retHtml .= '</a></div>';

		$retHtml .= '</div>';


	} catch (Exception $ex) {
		$retHtml = '<p>do_ingeni_blaze_template: '.$ex->getMessage().'</p>';
	}

	return $retHtml;
}

?>