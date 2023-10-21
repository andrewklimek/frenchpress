<?php

add_action( 'after_setup_theme', function() { add_theme_support( 'woocommerce' ); } );


// Remove Woo Tabs - an example for child themes
// remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
// Remove Woo CSS - see https://docs.woocommerce.com/document/css-structure/#disabling-woocommerce-styles
//add_filter( 'woocommerce_enqueue_styles', '__return_false' );

/**
 * Why unhook them if the functions are pluggable?
 * https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/includes/wc-template-functions.php#L1020
 */
// remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
// remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
// remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
// add_action('woocommerce_before_main_content', 'frenchpress_output_content_wrapper', 10);
// add_action('woocommerce_after_main_content', 'frenchpress_output_content_wrapper_end', 10);
// add_action('woocommerce_sidebar', 'frenchpress_get_sidebar', 10);
function woocommerce_output_content_wrapper() {
	echo "<main id=main class=site-main>";
}
function woocommerce_output_content_wrapper_end() {
	echo '</main>';
}
function woocommerce_get_sidebar() {
	if (!empty($GLOBALS['frenchpress']->sidebar)) echo $GLOBALS['frenchpress']->sidebar;
}


// Could add a custom sidebar and maybe hook it inot sidebar.php with these conditionals
function frenchpress_woo_sidebar() {
	if ( is_single() && is_active_sidebar( 'single-product' ) ) {
		ob_start();
		dynamic_sidebar( 'single-product' );
		return ob_get_clean();
	}
}
add_filter( 'frenchpress_custom_sidebar', 'frenchpress_woo_sidebar' );
// Register widget areas
function frenchpress_woo_widgets_init() {
	register_sidebar( [
		'name'          => 'Single Product',
		'id'            => 'single-product',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class="widgettitle">',
		'after_title'   => "</h3>\n",
	] );
}
add_action( 'widgets_init', 'frenchpress_woo_widgets_init' );
