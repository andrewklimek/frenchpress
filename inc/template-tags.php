<?php
/**
 * show edit link and dashboard link if admin bar has been hidden via the 'show_admin_bar' filter
 */
function frenchpress_mini_admin_bar(){

	if ( ! current_user_can( 'edit_posts' ) || apply_filters( 'show_admin_bar', true ) ) return;

	echo '<span class=mini-adminbar style="position:fixed;bottom:0;right:0;background:#fff;opacity:.5;line-height:0">';
	if ( is_singular() )
		echo '<a href="' . get_edit_post_link() . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="fill:#000;width:20px;margin:5px"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></svg></a>';
	echo '<a href="' . get_admin_url() . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="fill:#000;width:20px;margin:5px"><path d="M3.76 16h12.48c1.1-1.37 1.76-3.11 1.76-5 0-4.42-3.58-8-8-8s-8 3.58-8 8c0 1.89.66 3.63 1.76 5zM10 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM6 6c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm8 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-5.37 5.55L12 7v6c0 1.1-.9 2-2 2s-2-.9-2-2c0-.57.24-1.08.63-1.45zM4 10c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm12 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-5 3c0-.55-.45-1-1-1s-1 .45-1 1 .45 1 1 1 1-.45 1-1z"></path></svg></a>';
	echo '</span>';
}
//add_action( 'wp_footer', 'frenchpress_mini_admin_bar' );// putting in footer.php at this time

/**
 * current year shorcode for copyright lines
 */
add_shortcode( 'current_year', function(){ return date('Y'); } );

/**
 * Custom posts nav function because I'm insane
 */
function frenchpress_posts_nav( $before='', $after='.' ) {
	global $paged, $wp_query;

	$max_page = $wp_query->max_num_pages;

	if ( $max_page < 2 ) return;

	/*
	<nav class="navigation posts-navigation">
		<h2 class=screen-reader-text>Posts navigation</h2>
		<div class=nav-links>
			<div class=nav-previous><a href=/blog/page/2/>Older posts</a></div>
			<div class=nav-next><a href=/blog/>Newer posts</a></div>
		</div>
	</nav>
	<nav class="navigation pagination">
		<h2 class=screen-reader-text>Posts navigation</h2>
		<div class=nav-links>
			<a class="prev page-numbers" href=/blog/>Previous</a>
			<a class=page-numbers href=/blog/><span class=screen-reader-text>Page </span>1</a>
			<span aria-current=page class="page-numbers current"><span class=screen-reader-text>Page </span>2</span>
			<a class="next page-numbers" href=/blog/page/2/>Next</a>
		</div>
	</nav>
	*/

	$out = '<nav class=posts-nav><h2 class=screen-reader-text>Posts navigation</h2><div class="nav-links fff fff-spacebetween">';

	if ( !$paged ) $paged = 1;

	$pagenum_link = get_pagenum_link(809);

	if ( $paged > 1 )
		$out .= '<a class=prev href="' . str_replace( '809', $paged - 1, $pagenum_link ) . '">Newer<span class=screen-reader-text> Posts</span></a>';
	else
		$out .= '<a class=prev style="opacity:.1"><span class=screen-reader-text>No </span>Newer<span class=screen-reader-text> Posts</span></a>';

	$out .= ' <span aria-current=page class="page-numbers current"><span class=screen-reader-text>Page </span>' . $before . $paged . $after . '</span> ';

	if ( $paged < $max_page )
		$out .= '<a class=next href="' . str_replace( '809', $paged + 1, $pagenum_link ) . '">Older<span class=screen-reader-text> Posts</span></a>';
	else
		$out .= '<a class=next style="opacity:.1"><span class=screen-reader-text>No </span>Older<span class=screen-reader-text> Posts</span></a>';

	$out .= '</div></nav>';

	return $out;
}


/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
if ( ! function_exists( 'frenchpress_entry_meta' ) ) :
function frenchpress_entry_meta() {

	// Disable or do a custom meta
	// eg, to only show meta on 'posts' (not cutom post types): function( $skip ){ return 'post' === get_post_type() ? $skip : true; }
	// eg, to customize meta for archives only, the first line in the filter could be "if ( !is_archive() ) return $skip_the_rest;"
	if ( apply_filters( 'frenchpress_entry_meta_header', false ) ) {
		return;
	}

	$date = get_the_date( DATE_W3C );// PHP constant same as 'c' format
	$modified_date = get_the_modified_date( DATE_W3C );

	$time = $date === $modified_date ? '<time class="entry-date published updated" datetime="' . $date . '">' . get_the_date() . '</time>'
		: '<time class="entry-date published" datetime="' . $date . '">' . get_the_date() . '</time><time class=updated datetime="' . $modified_date . '">' . get_the_modified_date() . '</time>';

	if ( apply_filters( 'frenchpress_entry_meta_link_time', false ) ) {
		$time = '<a href="' . esc_url( get_permalink() ) . '" rel=bookmark>' . $time . '</a>';
	}

	$byline = get_the_author();

	if ( apply_filters( 'frenchpress_entry_meta_link_author', is_multi_author() ) ) {
		$byline = '<a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . $byline . '</a>';
	}
	$byline = "by <span class='author vcard'>{$byline}</span>";

	echo "<p class=entry-meta-header><span class=posted-on>{$time}</span><span class=byline> {$byline}</span></p>";
}
endif;

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
if ( ! function_exists( 'frenchpress_entry_footer' ) ) :
function frenchpress_entry_footer() {

	// echo "<p class=entry-meta-footer>";

	if ( 'post' === get_post_type() ) {// only show category and tag on posts

		$separate_meta = ", ";

		if ( frenchpress_categorized_blog() && $categories_list = get_the_category_list( $separate_meta ) ) {

			echo '<p class=cat-links>Filed under ' . $categories_list;

		}

		if ( $tags_list = get_the_tag_list( '', $separate_meta ) ) {

			echo '<p class=tag-links>Tagged ' . $tags_list;

		}
	}

	// echo "</p>";
}
endif;


/**
 * Custom Comment Callback
 */
function frenchpress_comment( $comment, $args, $depth ) {
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( !empty( $args['has_children'] ) ? 'parent' : '', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class=comment-body>
			<footer class="comment-meta fff fff-spacebetween">
				<div class="comment-author vcard fffi">
					<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
					<cite class=fn><?php echo get_comment_author_link( $comment ) ?></cite>
				</div>
				<div class="comment-metadata fffi">
					<a class=comment-permalink href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>"><?php
							echo mysql2date( get_option('date_format') .' '. get_option('time_format'), $comment->comment_date );// could use $comment->comment_date_gmt
						?></time>
					</a>
					<?php
					comment_reply_link( array_merge( $args, array(
						'add_below' => 'div-comment',
						'depth'	 => $depth,
						'max_depth' => $args['max_depth'],
						'before'	=> ' | ',
						'after'	 => ''
					) ) );
					edit_comment_link( 'Edit', ' | ', '' );
					?>
				</div>
				<?php
				if ( '0' == $comment->comment_approved )
					echo '<p class=comment-awaiting-moderation>Your comment is awaiting moderation.</p>';
				?>
			</footer>
			<div class=comment-content>
				<?php comment_text(); ?>
			</div>
		</article>
	<?php
	// ending </li> is handled by core or a custom end-callback
}


/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function frenchpress_categorized_blog() {

	if ( false === ( $cats = get_transient( 'frenchpress_categories' ) ) ) {

		$cats = get_categories( array(
			'fields'	 => 'ids',
			'hide_empty' => 1,
			'number'	 => 2,// We only need to know if there is more than one category.
		) );

		$cats = count( $cats );

		set_transient( 'frenchpress_categories', $cats );
	}

	if ( $cats > 1 ) return true;// This blog has more than 1 category

	else return false;// This blog has only 1 category

}

/**
 * Flush out the transients used in frenchpress_categorized_blog.
 */
function frenchpress_category_transient_flusher() {

	if ( ! defined( 'DOING_AUTOSAVE' ) || ! DOING_AUTOSAVE ) delete_transient( 'frenchpress_categories' );

}
add_action( 'edit_category', 'frenchpress_category_transient_flusher' );
add_action( 'save_post',	 'frenchpress_category_transient_flusher' );
