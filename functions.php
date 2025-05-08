<?php
	
	// Call parent theme
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	function theme_enqueue_styles() {
	    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ) );
	}
	
	// Add image size for mini-features - so the aspect ratio is correct (300 x 200)
	add_image_size( 'mini-feature', 450, 300, true );
	add_image_size( 'before-after', 1200, 900, true );
	
	// Mini-features Shortcode
	function mini_features_func( $max_items ) {
		
		// Get the mini-features
		$args = array(
			'post_type'	=> 'mini_feature',
			'order'		=> 'ASC'
		);
		$mini_features = query_posts( $args );
		
		$return = '<div id="mini-feature-section">';
		$count = 1;
		while( have_posts() ) : the_post(); 
			$last = '';
			if( $count%3 == 0 ) $last =' last';
			$return .= '<div class="mini-feature' . $last . '">'; 
			
				// Get the image
				$mini_image = get_field( 'image' );

				$title = $mini_image['title'];
				$alt = $mini_image['alt'];
				
				$size = 'mini-feature';
				$thumb = $mini_image['sizes'][ $size ];
				$width = $mini_image['sizes'][ $size . '-width' ];
				$height = $mini_image['sizes'][ $size . '-height' ];
				
				// Get the URL
				$link_url = get_field( 'url' );
				
				// Display the Image and Title (both links) 
				$post_title = get_the_title();
				$return .= '<a href="' . $link_url . '" alt="' . $post_title . '" title="' . $post_title . '"><img src="' . $thumb . '" alt="' . $alt . '" title="' . $title . '" width="' . $width . '" height="' . $height . '" /></a>';
				$return .= '<a class="mini-title" href="' . $link_url . '" alt="' . $post_title . '" title="' . $post_title . '"><h3>' . $post_title . '</h3></a>';
				if ( $count%3 == 0) $return .= '<div class="separator"></div>';
				$count++;
			$return .= '</div>';
		endwhile;
		
		// Reset Query
		wp_reset_query();
		$return .= '</div>';
		return $return;
	}
	add_shortcode( 'mini-features', 'mini_features_func' );
	
	// Check if child page; if so, add sibling pages before content, as links
	function show_siblings( $parent_id ) {
		echo '<div class="sibling-pages">';
		echo '<strong>' . get_the_title( $parent_id ) . ':</strong>';
		$child_pages = get_children( $parent_id );
		$count = 1;
		foreach( $child_pages as $child_page ) {
			$separator = '<span class="separator">|</span>';
			$first = '';
			if( $count <= 1 ) {
				$separator = '';
				$first = 'class="first" ';
			}
			echo $separator . '<a ' . $first . 'href="' . get_permalink( $child_page->ID ) . '" alt="' . $child_page->post_title . '" title="' . $child_page->post_title . ' - DPC Event Services">' . $child_page->post_title . '</a>';
			$count++;
		}
		echo '</div>';
	}
	
	/* Add Before/After Photos with Shortcode */
	function before_after_func() {
		$return = '';
		if( have_rows( 'photo_set' ) ) :
			while( have_rows( 'photo_set' ) ) : the_row();
				$title = get_sub_field('title');
				$before_image = get_sub_field('before_photo');
				$after_image = get_sub_field('after_photo');
				$size = 'before-after';
				// Before Image
				$before_image_url = $before_image['sizes'][$size];
				$before_image_width = $before_image['sizes'][$size . '-width'];
				$before_image_height = $before_image['sizes'][$size . '-height'];
				// After Image
				$after_image_url = $after_image['sizes'][$size];
				$after_image_width = $after_image['sizes'][$size . '-width'];
				$after_image_height = $after_image['sizes'][$size . '-height'];
							
				$return .= '<h2>' . $title . '</h2>';
				$return .= '<p><img src="' . $before_image_url . '" alt="' . $title . ' - Before" title="' . $title . ' - Before" width="' . $before_image_width . '" height="' . $before_image_height . '" class="before-photo" /></p>';
				$return .= '<p><img src="' . $after_image_url . '" alt="' . $title . ' - After" title="' . $title . ' - After" width="' . $after_image_width . '" height="' . $after_image_height . '" class="after-photo" /></p>';
						
			endwhile;	
					
			else:
				// No rows found
			endif;
			
			return $return;
	
	}
	add_shortcode('before-after-selections', 'before_after_func');
	
	/**
	 * Add a 3% surcharge to every dollar over $500 on the checkout page
	 * change the $percentage to set the surcharge to a value to suit
	 * Uses the WooCommerce fees API
	 *
	 * Add to theme functions.php
	 */
	add_action( 'woocommerce_cart_calculate_fees','woocommerce_custom_surcharge' );
	function woocommerce_custom_surcharge() {
	  global $woocommerce;

		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;
		
		// Check for carts greater than $500
		if( $woocommerce->cart->cart_contents_total > 500 ) {
			$percentage = 0.03;
			$surchargeable = $woocommerce->cart->cart_contents_total - 500;
			$surcharge = $surchargeable * $percentage;	
			$woocommerce->cart->add_fee( 'Online Payment Surcharge', $surcharge, true, 'standard' );
		}
	}
	
?>