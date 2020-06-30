<?php
/**
 * Called via comments_template()
 *
 * Disable avatars via the standard method:
 * add_filter( 'wp_list_comments_args', function($r){ $r['avatar_size'] = 0; return $r; } );
 *
 */

if ( post_password_required() ) {
	return;
}

echo '<section id=comments class=comments-area>';

/**
* Putting comment styling here because it will so rarely be used
*
* These aren’t needed for now as I’m making all textareas 100% width blocks
*  .comment-form-comment label {display: block;}
*  #comment {width: 100%;}
*
*/
echo "<style>.comment-list,.children{list-style:none;padding:0}.children{padding-left:19px;border-left:5px solid rgba(165,165,165,.2)}.comment-meta{margin:0 0 12px}</style>";

	if ( have_comments() ) :

		echo '<h2 class="comments-title h3">';

			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				echo 'One Comment';
			} else {
				printf( '%1$s Comments', $comments_number );
			}
		echo '</h2>
			<ol class=comment-list>';

				wp_list_comments( array(
					'callback'   => 'frenchpress_comment',// this is in inc/template-tags.php
					'style'	  => 'ol',
					'short_ping' => true,
				) );

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
	comment_form();

	echo '</section>';
