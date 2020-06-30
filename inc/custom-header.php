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

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses frenchpress_header_style()
 */
function frenchpress_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'frenchpress_custom_header_args', array(
		'default-image'			=> '',
		'default-text-color'	=> '000000',
		'width'					=> 1000,
		'flex-width'			=> true,
		'height'				=> 250,
		'flex-height'			=> true,
		'header-text'			=> true,
		'wp-head-callback'		=> 'frenchpress_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'frenchpress_custom_header_setup' );

if ( ! function_exists( 'frenchpress_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog.
 *
 * @see frenchpress_custom_header_setup().
 */
function frenchpress_header_style() {
	$header_text_color = get_header_textcolor();
	// If no custom options for text are set, let's bail.
	// get_header_textcolor() options: add_theme_support( 'custom-header' ) is default, hide text (returns 'blank') or any hex value.
	if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
		return;
	}
	// returns 'blank' if text has been hidden
	if ( 'blank' !== $header_text_color ) {

		print "<style>.site-title a,.site-title,.site-description{color:#". esc_attr( $header_text_color ) ."}</style>";
	}
}
endif;
