<?php
/**
 * Called via get_search_form()
 */
 ?>
 <form role=search method=get class="search-form fff fff-bottom" action="<?php echo esc_url( home_url( '/' ) ) ?>">
	<style>
	.search-submit{min-width:0;padding:3px 3px 3px 10px;margin:0;border:0;line-height:0;color:currentColor;background:none}
	.search-field{border:0;padding:0;background:none;width:100%;outline:0;display:block}
	.search-form{max-width:25em;border-bottom:1px solid currentColor}
	</style>
	<label class=grow>
		<span class=screen-reader-text>Search for:</span>
		<input type=search class=search-field value="<?php echo get_search_query() ?>" name=s>
	</label>
	<button type=submit class=search-submit><svg xmlns=http://www.w3.org/2000/svg width=16 height=16 viewBox="0 0 16 16"><path d="M13.07 13.93c-.23 0-.45-.1-.6-.26l-3-3c-.8.55-1.73.83-2.68.83-2.62 0-4.73-2.1-4.73-4.7 0-2.62 2.1-4.73 4.72-4.73 2.6 0 4.7 2.1 4.7 4.72 0 .94-.28 1.88-.83 2.66l3 3c.16.16.26.38.26.6 0 .48-.4.87-.86.87zM6.8 3.8c-1.67 0-3 1.34-3 3 0 1.64 1.33 3 3 3 1.64 0 3-1.36 3-3 0-1.67-1.36-3-3-3z"/></svg></button>
</form>
