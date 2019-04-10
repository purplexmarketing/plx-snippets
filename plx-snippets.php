<?php
/**
* Plugin Name: PLX Snippets
* Description: Easily add snippets of text in the PHP code within your theme using Wordpress Custom Fields
* Version: 1.0
* Author: Purplex Marketing
* Author URI: http://www.plx.mk/
* License: GPLv2 or later
*/

// Register the plugin admin stylesheet
function plx_sn_styles() {
	wp_register_style( 'plx_sn_display_styles', plugin_dir_url( __FILE__ ) . 'css/styles.css' );
	wp_enqueue_style( 'plx_sn_display_styles');
}
add_action( 'admin_enqueue_scripts', 'plx_sn_styles');
add_action( 'wp_enqueue_scripts', 'plx_sn_styles');

// Register the Custom Snippet Post Type
function plx_register_cpt_snippet() {

  $labels = array(
    'name' 									=> _x( 'Snippets', 'plx_snippet' ),
    'singular_name' 				=> _x( 'Snippets', 'plx_snippet' ),
    'add_new' 							=> _x( 'Add Snippet', 'plx_snippet' ),
    'add_new_item' 					=> _x( 'Add New Snippet', 'plx_snippet' ),
    'edit_item' 						=> _x( 'Edit Snippet', 'plx_snippet' ),
    'new_item' 							=> _x( 'New Snippet', 'plx_snippet' ),
    'view_item' 						=> _x( 'View Snippet', 'plx_snippet' ),
    'search_items' 					=> _x( 'Search Snippets', 'plx_snippet' ),
    'not_found' 						=> _x( 'No snippets found', 'plx_snippet' ),
    'not_found_in_trash' 		=> _x( 'No snippets found in Bin', 'plx_snippet' ),
    'parent_item_colon' 		=> _x( 'Parent Snippet:', 'plx_snippet' ),
    'menu_name'							=> _x( 'PLX Snippets', 'plx_snippet' ),
    'name_admin_bar'				=> _x( 'Snippet', 'plx_snippet' ),
    'all_items' 						=> _x( 'All Snippets', 'plx_snippet'),
  );

  $args = array(
    'labels' 								=> $labels,
    'hierarchical' 					=> true,
    'description' 					=> 'Snippets filterable by snippet group',
    'supports' 							=> array( 'title' ),
    'taxonomies' 						=> array( 'plx-sn-groups' ),
    'public' 								=> true,
    'show_ui' 							=> true,
    'show_in_menu' 					=> true,
    'menu_icon' 						=> 'dashicons-slides',
    'show_in_nav_menus'			=> true,
    'publicly_queryable' 		=> true,
    'exclude_from_search' 	=> false,
    'has_archive' 					=> true,
    'query_var' 						=> true,
    'can_export' 						=> true,
    'rewrite' 							=> true,
    'capability_type' 			=> 'post',
    'register_meta_box_cb' => 'plx_add_snippet_metaboxes',

  );

  register_post_type( 'plx_snippet', $args );

}
add_action( 'init', 'plx_register_cpt_snippet' );

function plx_snippet_groups_taxonomy() {

	$labels = array(
		'name' 							=> _x( 'Snippet Groups', 'plx_snippet' ),
		'singular_name' 		=> _x( 'Snippet Group', 'plx_snippet' ),
		'search_items' 			=> _x( 'Search Snippet Groups', 'plx_snippet' ),
		'all_items'         => _x( 'All Snippet Groups', 'plx_snippet' ),
		'parent_item'       => _x( 'Parent Snippet Group', 'plx_snippet' ),
		'parent_item_colon' => _x( 'Parent Snipper Group:', 'plx_snippet' ),
		'edit_item'         => _x( 'Edit Snippet Group', 'plx_snippet' ),
		'update_item'       => _x( 'Update Snippet Group', 'plx_snippet' ),
		'add_new_item'      => _x( 'Add New Snippet Group', 'plx_snippet' ),
		'new_item_name'     => _x( 'New Snippet Group', 'plx_snippet' ),
		'menu_name'         => _x( 'Snippet Groups', 'plx_snippet' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'plx-snippet-groups' ),
	);

  register_taxonomy( 'plx-snippet-groups', 'plx_snippet_groups', $args );
}
add_action( 'init', 'plx_snippet_groups_taxonomy');

function plx_snippet_shortcode() {

	global $post;

	if (get_post_status( $post->ID ) == 'auto-draft') {

		$html = '<p class="description">Shortcodes will appear once snippet has been saved</p>';
		echo $html;

	} else {

		$html = '';

		$plx_shortcode_snippet = '[plxsnippet id=' . $post->ID . ']';

		$html .= '<div class="plx-row">';
		$html .= '<div class="plx-col-6">';
		$html .= '<label for="plx-shortcode-snippet">Display this snippet using the following shortcode<label>';
		$html .= '<input type="text" id="plx-shortcode-snippet" onfocus="this.select();" readonly="readonly" class="widefat code" value="' . htmlentities($plx_shortcode_snippet, ENT_QUOTES) . '" /></p>';
		$html .= '</div>';
		$html .= '<div class="clear"></div>';
		$html .= '</div>';

		echo $html;

	}

}

function plx_add_snippet_metaboxes() {

	global $post;

	add_meta_box('plx_snippet_shortcode', 'Shortcodes', 'plx_snippet_shortcode', 'plx_snippet', 'normal', 'low');

}

function plx_snippet_display($post) {
	echo get_the_title( $post->ID );
}

function plx_get_snippet( $atts, $content = null ) {
	$a = shortcode_atts( array(
		'id'				=> ''
	), $atts );

	$snippet_id = esc_attr($atts['id']);

	$post = get_post($snippet_id);
	$snippet_post .= plx_snippet_display($post, 'r');

	return $snippet_post;
}
add_shortcode('plxsnippet', 'plx_get_snippet');

function plx_snippet_hide_menu_items() {
	remove_menu_page( 'edit.php?post_type=plx_snippet' );
}

function plx_snippet_check_admin() {
	if ( ! current_user_can('manage_options') ) {
		add_action( 'admin_menu', 'plx_snippet_hide_menu_items' );
	}
}
add_action( 'plugins_loaded', 'plx_snippet_check_admin' );