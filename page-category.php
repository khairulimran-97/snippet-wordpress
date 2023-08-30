<?php

function create_page_taxonomy() {

  // Pages category
  register_taxonomy(
	'page_category', array (
	  0 				=> 'page', // Assign custom post type
	),
	array(
	  'hierarchical' 	=> true,
	  'labels' 			=> array (
		'name' 			=> 'Category',
		'add_new_item' 	=> 'Add New Category',
	  ),
	  'show_ui' 		=> true,
	  'query_var' 		=> true,
	  'rewrite' 		=> true,
	  'singular_label' 	=> 'Category'
	)
  );
}
add_action( 'init', 'create_page_taxonomy' );
