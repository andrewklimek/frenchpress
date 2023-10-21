<?php
/**
 * Product Loop Start
 *
 * Replaced ul, add loop class
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="products columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?> loop">
