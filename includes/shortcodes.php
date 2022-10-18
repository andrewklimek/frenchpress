<?php

/*****
*
Main FrenchPress building-block Shortcode
*
(some other small normal shortcodes in template-tags.php)
*
*
attributes:
el (element, default "div")
class
id
style
tray
bg (background image or color)
color (text color)
template (for now it adds a class ffft-[your template] but may build in templates later)
grid (flexbox conatainer)
cell (flexbox item)
*
*
Possible flexbox values (for grid/cell):

GRID-ONLY:

column
nowrap

(justify-content)
left
center
right
spacebetween
spacearound

GRID or CELL:

true (just apply default)

(align-items/self)
top
middle
bottom

(flex)
none		(0 0 auto)
auto		(1 1 auto)
initial	 (0 1 auto)
noshrink	(1 0 auto) *** DISABLED ***
magic		(1 1 auto + width:18em)
even		(1 1 0)

(flex-basis)
x1 (100% )
x2 (50%)
x3 (33.3%)
x4 (25%)

CELL-ONLY
(grow)
1 - 11
99 (flex-grow:99 - essentially prevents other items from growing at all UNTIL they wrap to their own row. e.g.: main content vs sidebars)
*
*
***/

add_filter( 'the_content', 'frenchpress_custom_shortcode_parsing', 9 );// Run this early to avoid wpautop
// add_filter( 'widget_custom_html_content', 'frenchpress_custom_shortcode_parsing', 9 );// Only new HTML widgets
// add_filter( 'widget_text_content', 'frenchpress_custom_shortcode_parsing', 9 );// Only text widget
add_filter( 'widget_text', 'frenchpress_custom_shortcode_parsing', 9 );// both, at least for now

function frenchpress_custom_shortcode_parsing( $c ) {

	// $p = "/\[(section|grid|cell)(?!\w)([^\]]*)\] ( (?: (?: [^\[]*? | \[(?!\/\\1\]) ) | (?R) )* ) \[\/\\1\]/x";// recursive feature

	$p = "/\[((?:section|grid|cell)(?:_\w+)?)(?!\w)([^\]]*)\]((?:[^\[]*|\[(?!\/\\1\]))*)\[\/\\1\]/";// allows for nesting with _suffixes, ex: [grid][grid_2][/grid_2][/grid]

	// recursive
	while ( false !== strpos($c, '[/grid') || false !== strpos($c, '[/cell') || false !== strpos($c, '[/section') ) {

		$c = preg_replace_callback( $p, function($m){
				return frenchpress_shortcode( shortcode_parse_atts( $m[2] ), $m[3], $m[1] );
			}, $c );

	}
	return $c;
}

function frenchpress_shortcode( $a, $c = '', $tag = '' ) {

	if ( !empty( $a['el'] ) ) {
		$el = $a['el'];
	} else {
		$el = ( false !== strpos( $tag, 'section' ) ) ? 'section' : 'div';
	}

	$id = !empty( $a['id'] ) ? " id='{$a['id']}'" : "";
	$class = !empty( $a['class'] ) ? "{$a['class']}" : "";

	// build Style attribute
	$style = "";
	// bg attribute for backgrounds
	if ( !empty( $a['bg'] ) ) {
		if ( false !== strpos($a['bg'],'/') ) $style .= "background:url({$a['bg']}) center/cover;";// has slash: presume image
		else $style .=  "--" === substr( $a['bg'], 0, 2 ) ? "background:var({$a['bg']});" : "background:{$a['bg']};";// color allows for css variable with missing var()
	}
	if ( !empty( $a['color'] ) ) {
		$style .=  "--" === substr( $a['color'], 0, 2 ) ? "color:var({$a['color']});" : "color:{$a['color']};";
	}
	// ad-hoc style attribute
	if ( !empty( $a['style'] ) ) $style .= $a['style'];
	$style = " style='{$style}'";

	// Flex
	if ( false !== strpos( $tag, 'grid' ) || !empty( $a['grid'] ) ) {
		$class .= ' fff';
		if ( !empty( $a['grid'] ) && 'true' !== $a['grid'] ) {// anything but true, lets add the modifiers
			$class .= " fff-" . str_replace( " ", " fff-", $a['grid'] );
		}
	}
	if ( false !== strpos( $tag, 'cell' ) || !empty( $a['cell'] ) ) {
		$class .= ' fffi';
		if ( !empty( $a['cell'] ) && 'true' !== $a['cell'] ) {// anything but true, lets add the modifiers
			$class .= " fffi-" . str_replace( " ", " fffi-", $a['cell'] );
		}

		// [flex-space] shortcode to add expanding space as a more intuitive way than settign spacebetween and putting things in grouping divs
		// $c = str_replace( "[flex-space]", "<hr class=spacer style='flex-grow:9'>", $c, $count );// or class='fffi fffi-9'
		$c = str_replace( ["[flex-space]","[flex-spacer]"], "</div><div>", $c, $count );// who knows we might spell it with an R at the end by accident
		if ( $count ) {
			$c = "<div>$c</div>";
			$class .= " fff fff-column fff-spacebetween";// top is actually left on columns
		}
	}

	// Templates
	if ( !empty( $a['template'] ) ) {
		$class .= " ffft-{$a['template']}";
	}

	// final check for any classes, add attribute
	$class = $class ? " class='". trim( $class ) ."'" : "";

	// process other shortcodes
	// $c = do_shortcode($c);// not anymore cause we're in a custom early shortcode processing

	// shortcut for wrapping content in a "tray" div (param tray=1, tray='pad' or tray='pad-2')
	if ( ! empty( $a['tray'] ) ) {
		$tray_class = $a['tray'] === '1' ? 'tray' : 'tray ' . $a['tray'];
		$c = "<div class='{$tray_class}'>{$c}</div>";
	}

	// string it all together
	return "<{$el}{$id}{$class}{$style}>\n{$c}</{$el}>";
}
