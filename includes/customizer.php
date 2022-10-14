<?php
/**
 * Sample implementation of the Custom Header feature.
 *
 * You can add an optional custom header image to header.php like so ...
 *
	<?php if ( get_header_image() ) : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel=home>
		<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="">
	</a>
	<?php endif; // End header image check. ?>
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 */

function frenchpress_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'frenchpress_custom_header_args', array(
		// 'default-text-color'	=> '000',
		'width'					=> 1000,
		'flex-width'			=> true,
		'height'				=> 250,
		'flex-height'			=> true,
		// 'wp-head-callback'		=> 'frenchpress_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'frenchpress_custom_header_setup' );

// remove control for header text color
add_action( 'customize_register', function( $wp_customize ) {
	$wp_customize->remove_control( 'header_textcolor' );
} );