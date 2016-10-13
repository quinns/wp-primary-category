<?php 
/* 
Plugin Name: Primary Categories for Posts 
Plugin URI: https://github.com/quinns/wp-primary-category
Description: Allows content authors to designate a primary category for posts, and filter based on categories. 
Version: 1.0.0 
Author: Quinn Supplee
Author URI: https://www.quinnsupplee.com/ 
*/


/*
create a new taxonomy for "primary category", see
https://codex.wordpress.org/Function_Reference/register_taxonomy 
*/
function primary_category_init() {
	register_taxonomy(
		'primary_category',
		array('post', 'some_other'), // "some_other" TBD, custom post type
		array(
			'label' => __( 'Primary category' ),
			'rewrite' => array( 'slug' => 'primary-category' ),
			'hierarchical' => true
		)
	);
}


/*

let's make a custom widget for the drop-down list of taxonomy terms

*/

class Primary_Category_Widget extends WP_Widget{
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'primary_category_widget',
			'description' => 'Display Primary Category picker',
		);
		parent::__construct( 'primary_category_widget', 'Primary Category', $widget_ops );
	}
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		$content = '<h4 class="widgettitle">Primary Categories</h4>';
		$content .=  '<form id="category-select" class="category-select postform " action="'.esc_url( home_url( '/' ) ).'" method="get">';
		$content .= primary_category_dropdown();
		//$content .= '<input type="submit" name="submit" value="go" class="btn btn-default" />';
		$content .= '</form>';
		echo $content;
	}
	
}

// function to build out custom categories drop-down list
function primary_category_dropdown(){
	$terms = get_terms('primary_category');
	$output = '<select class="form-control postform" name="primary_category" id="primary-category-picker">';
	foreach($terms as $term){
		//pre-select our chosen term, if any
		if(isset($_GET['primary_category']) && sanitize_text_field($_GET['primary_category']) == $term->slug){
			$selected  = ' selected ';
		} else {
			$selected = null;
		}
		$output .= sprintf( '<option class="level-0" value="%s" '.$selected.'>%s</option>', $term->slug, $term->name );
	}
	$output .= '</select>';
	// add a non-javascript fallback
	$output .= '<noscript><input type="submit" class="btn btn-default" value="Go"></noscript>';
	return $output;

}



/*
We only want to allow the Primary Category to be a single-select, but WP Taxonomies want to be
multiple-select. Looked at a few ways to handle this but the most compact and straightforward approach
seems to be to use a bit of jQuery to convert the check boxes into a set of radio buttons
note - this could get unweildy if there are tons of categories ...
*/

function primary_category_admin_scripts() {
    wp_enqueue_script( 'primary-category-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), false, true );
}
function primary_category_public_scripts() {
    wp_enqueue_script( 'primary-category-public-script', plugin_dir_url( __FILE__ ) . 'js/public.js', array( 'jquery' ), false, true );
}

// add our actions from the function we've defined above
add_action( 'init', 'primary_category_init' ); // initialize our custom taxonomy
add_action( 'admin_enqueue_scripts', 'primary_category_admin_scripts' ); // add our js to only the admin pages
add_action( 'wp_enqueue_scripts', 'primary_category_public_scripts' ); // add our js to only the admin pages
add_action('widgets_init', function(){ register_widget('Primary_Category_Widget'); });


/*
notes and resources...
https://codex.wordpress.org/Post_Types
https://codex.wordpress.org/Widgets_API
https://wordpress.stackexchange.com/questions/86864/using-1-taxonomy-for-multiple-post-types
http://sudarmuthu.com/blog/creating-single-select-wordpress-taxonomies/
https://frankiejarrett.com/2011/09/create-a-dropdown-of-custom-taxonomies-in-wordpress-the-easy-way/
http://kellenmace.com/create-a-taxonomy-dropdown-in-wordpress/
https://stackoverflow.com/questions/16823679/wordpress-plugin-development-how-to-use-jquery-javascript
https://github.com/WebDevStudios/Taxonomy_Single_Term
https://wpshout.com/why-when-and-how-to-make-your-own-template-tags/
https://wordpress.stackexchange.com/questions/17385/custom-post-type-templates-from-plugin-folder
https://codex.wordpress.org/Function_Reference/wp_get_post_terms	
http://www.wpbeginner.com/plugins/how-to-display-custom-taxonomy-terms-in-wordpress-sidebar-widgets/
*/
