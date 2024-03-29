<?php
/**
 * Called via comments_template()
 */

if ( !empty( $GLOBALS['frenchpress']->disable_comments ) ) return;

if ( post_password_required() ) return;

echo '<section id=comments class=comments-area>';

/**
* Putting comment styling here because it will so rarely be used
*/
echo "<style>#comments ol{list-style:none;padding:0}li .comment{padding-left:19px;border-left:5px solid rgba(165,165,165,.2)}.comment-metadata{margin:0 0 12px;font-size:80%}</style>";

if ( have_comments() ) :

	echo '<p class="comments-title h3">';

		$comments_number = get_comments_number();
		if ( '1' === $comments_number ) {
			echo 'One Comment';
		} else {
			printf( '%1$s Comments', $comments_number );
		}
		
	echo '<ol class=comment-list>';
		
		$args = [ 'callback' => 'frenchpress_comment', 'style' => 'ol', 'short_ping' => true ];

		/* Disable avatars... or use filter: add_filter( 'wp_list_comments_args', function($r){ $r['avatar_size'] = 0; return $r; } ); */
		if ( isset( $GLOBALS['frenchpress']->avatar_size ) ) $args['avatar_size'] = (int) $GLOBALS['frenchpress']->avatar_size;

		wp_list_comments( $args );

	echo '</ol>';

	the_comments_pagination();

endif; // Check for have_comments().


// If comments are closed but there are comments, say comments are closed
if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) {

	echo '<p class=no-comments>Comments are closed.</p>';
}

/**
 * You can remove the "website" field from the comment form like so:
 * add_filter( 'comment_form_default_fields', function($fields){ unset($fields['url']); return $fields; } );
 */
comment_form( [
	'comment_notes_before'	=> '', 
	'logged_in_as'			=> '', 
	'title_reply'			=> 'Leave a Comment', 
	'title_reply_before'	=> '<p id=reply-title class=comment-reply-title>', 
	'title_reply_after'		=> '</p>', 
	'cancel_reply_before'	=> '<small> &nbsp;',
	'cancel_reply_link'		=> 'cancel',

	] );

echo '</section>';



/**
 * Comment Callback
 */
function frenchpress_comment( $comment, $args, $depth ) {

// comment_class( !empty( $args['has_children'] ) ? 'parent' : '' )// if its ever helpful...
?>
<li id=comment-<?php echo $comment->comment_ID; ?> <?php comment_class(); ?>>
	<div id=div-comment-<?php echo $comment->comment_ID; ?> class=comment-body>
		<div class=comment-meta>
			<span class=comment-author>
				<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] );
				echo $comment->comment_author;
			echo "</span>";

			// Date (w/ comment link)
			if ( empty( $GLOBALS['frenchpress']->comment_no_dates ) ) {
				echo "&nbsp; <a class='comment-permalink comment-date' href='" . esc_url( get_comment_link( $comment, $args ) ) . "'>";
				// echo "<time datetime='" . get_comment_time( 'c' ) . "'>";
						echo mysql2date( 'Y-m-d', $comment->comment_date );// get_option('date_format')
						// echo mysql2date( get_option('date_format') .' '. get_option('time_format'), $comment->comment_date );// could use $comment->comment_date_gmt
				// echo "</time>";
				echo "</a>";
			}

			// comment reply link
			comment_reply_link( array_merge( $args, [
				'add_below' => 'div-comment',
				'depth'		=> $depth,
				'max_depth'	=> $args['max_depth'],
				'before'	=> '&nbsp; ',
				'after'		=> '',
				'reply_text' => 'reply',
			] ) );

			edit_comment_link( 'Edit', '&nbsp; ', '' );

			if ( '0' == $comment->comment_approved )
				echo '<p class=comment-awaiting-moderation>Your comment is awaiting moderation.</p>';
			?>
		</div>
		<div class=comment-content>
			<?php comment_text(); ?>
		</div>
	</div>
<?php // ending </li> not needed
}

/**
 * comment_class() in above function adds usernames to comments classlist... might be smart to remove for security, as if.
 */
add_filter('comment_class', 'frenchpress_remove_username_from_comments' );
function frenchpress_remove_username_from_comments( $classes ){
	$k = array_search( 'byuser', $classes );
	if ( $k !== false ) {
		if ( 'comment-author-' === substr( $classes[ 1 + $k ], 0, 15 ) ) {
			unset( $classes[ 1 + $k ] );
		}
	}
	return $classes;
}