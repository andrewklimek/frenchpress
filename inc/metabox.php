<?php
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function frenchpress_add_meta_box( $post_type ) {
	if ( !in_array ( $post_type, array('attachment','revision','nav_menu_item') ) ){
		add_meta_box(
			'frenchpress_schema_article',
			'Schema.org',
			'frenchpress_meta_box_callback',
			null, 'advanced', 'default',
			array('type' => $post_type)
		);
	}
}
add_action( 'add_meta_boxes', 'frenchpress_add_meta_box' );

/**
 * Prints the box content.
 */
function frenchpress_meta_box_callback( $post, $metabox ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'frenchpress_save_meta_box_data', 'frenchpress_meta_box_nonce' );

	/**
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, 'frenchpress_schema_article', true );
	if ( ! $value) {
		global $wpdb;
		$type = $metabox['args']['type'];
		$type_default = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}postmeta ON ID=post_id WHERE post_type='{$type}' AND meta_key='frenchpress_schema_article' ORDER BY ID DESC LIMIT 1" );
		$value = $type_default ? $type_default : 'BlogPosting';
	}
	echo '<label for=frenchpress_new_field>Itemtype: </label>';
	echo '<input type=text id=frenchpress_new_field name=frenchpress_new_field value="' . esc_attr( $value ) . '" size=25 />';
}

/**
 * When the post is saved, saves our custom data.
 */
function frenchpress_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['frenchpress_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['frenchpress_meta_box_nonce'], 'frenchpress_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['frenchpress_new_field'] ) ) {
		return;
	}

	// Sanitize user input.
	$my_data = sanitize_text_field( $_POST['frenchpress_new_field'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'frenchpress_schema_article', $my_data );
}
add_action( 'save_post', 'frenchpress_save_meta_box_data' );

/* function frenchpress_build_schema ($post_object) {
	$schema = '<script type=application/ld+json>
	{
	"@context" : "http://schema.org",
	"@type" : "' . urlencode( get_metadata( 'post', get_the_ID(), 'frenchpress_schema_article', true )) . '",
	"headline" : "'. get_the_title() . '",
	"author" : {
		"@type" : "Person",
		"name" : "' . get_the_author() . '"
	},
	"datePublished" : "' . get_the_date() . '",
	"url" : "' . get_the_permalink() . '"
	}
</script>'; */
function frenchpress_print_schema (){
	$schema = '<script type=application/ld+json>
	{
		"@context" : "http://schema.org",
		"@type" : "'. urlencode( get_metadata( 'post', get_the_ID(), 'frenchpress_schema_article', true )) .'",
		"headline" : "'. get_the_title() .'",
		"author" : {
			"@type" : "Person",
			"name" : "'. get_the_author() .'"
		},
		"datePublished" : "'. get_the_date() .'",
		"url" : "'. get_the_permalink() .'",
		"image" : "'. wp_get_attachment_url( get_post_thumbnail_id() ) .'"
	}
</script>';
	echo $schema;
}
add_action('wp_print_footer_scripts', 'frenchpress_print_schema');
/* }
add_action( 'the_post', 'frenchpress_build_schema' ); */