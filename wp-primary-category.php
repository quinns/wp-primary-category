<?php 
/* 
Plugin Name: Primary Categories for Posts 
Plugin URI: https://github.com/quinns/wp-primary-category
Description: Allows content authors to designate a primary category for posts, and filter based on categories. 
Version: 1.0.0 
Author: Quinn Supplee
Author URI: https://www.quinnsupplee.com/ 
*/


// see: https://codex.wordpress.org/Function_Reference/register_taxonomy
function primary_category_init() {
	// create a new taxonomy
	register_taxonomy(
		'primary_category',
		'post',
		array(
			'label' => __( 'Primary category' ),
			'rewrite' => array( 'slug' => 'primary-category' ),
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
		)
	);
}


// add_filter('single_template', 'primary_category_post_template');
/*
We only want to allow the Primary Category to be a single-select, but WP Taxonomies want to be
multiple-select. Looked at a few ways to handle this but the most compact and straightforward approach
seems to be to use a bit of jQuery to convert the check boxes into a set of radio buttons
note - this could get unweildy if there are tons of categories ...
*/

function primary_category_admin_scripts() {
    wp_enqueue_script( 'primary-category-script', plugin_dir_url( __FILE__ ) . '/js/scripts.js', array( 'jquery' ), '1.0.0', true );
}

// add our actions from the function we've defined above
add_action( 'init', 'primary_category_init' ); // initialize our custom taxonomy
add_action( 'admin_enqueue_scripts', 'primary_category_admin_scripts' ); // add our js to only the admin pages


/*
notes and resources...

http://sudarmuthu.com/blog/creating-single-select-wordpress-taxonomies/
https://frankiejarrett.com/2011/09/create-a-dropdown-of-custom-taxonomies-in-wordpress-the-easy-way/
http://kellenmace.com/create-a-taxonomy-dropdown-in-wordpress/
https://stackoverflow.com/questions/16823679/wordpress-plugin-development-how-to-use-jquery-javascript
https://github.com/WebDevStudios/Taxonomy_Single_Term
https://wpshout.com/why-when-and-how-to-make-your-own-template-tags/
https://wordpress.stackexchange.com/questions/17385/custom-post-type-templates-from-plugin-folder
https://codex.wordpress.org/Function_Reference/wp_get_post_terms	
*/
