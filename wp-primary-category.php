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
		array('post', 'custom_post'), // "custom_post" is a sample custom post type, extend this array as needed
		array(
			'label' => __( 'Primary category' ),
			'rewrite' => array( 'slug' => 'primary-category' ),
			'hierarchical' => true
		)
	);
}


/*
register a custom post type, just for the demonstration purposes 
of this exersize. see https://codex.wordpress.org/Post_Types
*/
function primary_category_create_custom_post_type(){
	register_post_type('custom_post', 
		array(
			'labels' => array(
				'name' => __('Custom posts'),
				'singular_name' => __('Custom post')
			),
			'public' => true,
			'has_archive' => true,
			'taxonomies'  => array('category')
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
		parent::__construct('primary_category_widget', 'Primary Category', $widget_ops);
	}
	public function widget($args, $instance){
		if(!empty($_GET['search_term'])){
			$search_term = sanitize_text_field($_GET['search_term']);
		} else{
			$search_term = null;
		}
		// outputs the content of the widget
		$content = '<h4 class="widgettitle">Primary Categories</h4>';
		$content .= '<form id="category-select" class="category-select postform " action="'.esc_url( home_url( '/' ) ).'" method="get">';
		$content .= '<input type="text" class="form-control" value="'.$search_term.'" name="search_term" placeholder="search">';
		$content .= primary_category_dropdown();
		$content .= '</form>';
		echo $content;
	}
	
}

/*
filter (search) our posts when on the archive page
*/
function primary_category_search($query){
	if(!empty($_GET['search_term'])){
		//limit the filter to front end, main query and archive pages
		if($query->is_main_query() && !is_admin() && $query->is_archive ) {
			$query->set('s', sanitize_text_field($_GET['search_term']));
		}
	}
}


/*
build our custom categories drop-down list
*/
function primary_category_dropdown(){
	$terms = get_terms('primary_category');
	$output = '<select class="form-control postform" name="primary_category" id="primary-category-picker">';
	$output .= '<option selected="true" disabled="disabled">Choose...</option>';
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

/*
we also want to have our drop-down list of terms be nice and responsive, 
so we use jquery to submit the form on-change
*/
function primary_category_public_scripts() {
    wp_enqueue_script( 'primary-category-public-script', plugin_dir_url( __FILE__ ) . 'js/public.js', array( 'jquery' ), false, true );
}

// add our actions from the function we've defined above
add_action('init', 'primary_category_init' ); // initialize our custom taxonomy
add_action('init', 'primary_category_create_custom_post_type'); // init our custom post type
add_action('admin_enqueue_scripts', 'primary_category_admin_scripts' ); // add our js to only the admin pages
add_action('wp_enqueue_scripts', 'primary_category_public_scripts' ); // add our js to only the admin pages
add_action('widgets_init', function(){ register_widget('Primary_Category_Widget'); }); // init the dropdown menu
add_action('pre_get_posts', 'primary_category_search'); // init our cusotm archive search 

