<?php
class walker_no_list extends Walker {

public $tree_type = array( 'post_type', 'taxonomy', 'custom' );

public $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
	/*	 $indent = str_repeat("\t", $depth);
		$output .= "\n$indent<div class=\"sub-menu\">\n"; */
	}

public function end_lvl( &$output, $depth = 0, $args = array() ) {
		 $indent = str_repeat("\t", $depth);
		$output .= "$indent</div>\n";
	}

	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )	 ? $item->target	 : '';
		$atts['rel']	= ! empty( $item->xfn )		? $item->xfn		: '';
		$atts['href']   = ! empty( $item->url )		? $item->url		: '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = ($args->walker->has_children) ? "<div class=\"sub-menu\">\n" : "";
		$item_output .= $args->before;
		$item_output .= '<a'. $attributes . $id . $class_names .'>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	 public function end_el( &$output, $item, $depth = 0, $args = array() ) {
		 // $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		 // print substr($output, 1, 4);
	}

} // Walker_No_List