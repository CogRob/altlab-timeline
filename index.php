<?php
/**
 * Plugin Name: ALT LAB Timeline Maker
 * Plugin URI: https://github.com/woodwardtw/
 * Description: lets you create Timeline JS (Knight Lab) data views using WordPress posts (including custom post types)

 * Version: 1.7
 * Author: Tom Woodward
 * Author URI: http://bionicteaching.com
 * License: GPL2
 */
 
 /*   2016 Tom  (email : bionicteaching@gmail.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

		if(!function_exists('load_timelinejs_script')){
		    function load_timelinejs_script() {		    	
		        global $post;
		        if (get_post_type($post->ID) === 'timeline'){
			        $deps = array('jquery');
			        $version= '1.0'; 
			        $in_footer = false;
			        wp_enqueue_script('knightlab_timeline', plugins_url( '/js/timeline-min.js', __FILE__), $deps, $version, $in_footer);			
			    }
			}
		}
		add_action('wp_enqueue_scripts', 'load_timelinejs_script');

		function add_timeline_stylesheet() {
			global $post;
			if (get_post_type($post->ID) === 'timeline'){
		    	wp_enqueue_style( 'timeline-css', plugins_url( '/css/timeline.css', __FILE__ ) );
		    }
		}

		add_action('wp_enqueue_scripts', 'add_timeline_stylesheet');
	

//CUSTOM POST TYPES

// Register Custom Post Type Timeline
// Post Type Key: Timeline
function create_timeline_cpt() {

	$labels = array(
		'name' => __( 'Timelines', 'Post Type General Name', 'textdomain' ),
		'singular_name' => __( 'Timeline', 'Post Type Singular Name', 'textdomain' ),
		'menu_name' => __( 'Timelines', 'textdomain' ),
		'name_admin_bar' => __( 'Timeline', 'textdomain' ),
		'archives' => __( 'Timeline Archives', 'textdomain' ),
		'attributes' => __( 'Timeline Attributes', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Timeline:', 'textdomain' ),
		'all_items' => __( 'All Timelines', 'textdomain' ),
		'add_new_item' => __( 'Add New Timeline', 'textdomain' ),
		'add_new' => __( 'Add New', 'textdomain' ),
		'new_item' => __( 'New Timeline', 'textdomain' ),
		'edit_item' => __( 'Edit Timeline', 'textdomain' ),
		'update_item' => __( 'Update Timeline', 'textdomain' ),
		'view_item' => __( 'View Timeline', 'textdomain' ),
		'view_items' => __( 'View Timelines', 'textdomain' ),
		'search_items' => __( 'Search Timelines', 'textdomain' ),
		'not_found' => __( 'Not found', 'textdomain' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
		'featured_image' => __( 'Featured Image', 'textdomain' ),
		'set_featured_image' => __( 'Set featured image', 'textdomain' ),
		'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
		'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
		'insert_into_item' => __( 'Insert into Timeline', 'textdomain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Timeline', 'textdomain' ),
		'items_list' => __( 'Timelines list', 'textdomain' ),
		'items_list_navigation' => __( 'Timelines list navigation', 'textdomain' ),
		'filter_items_list' => __( 'Filter Timelines list', 'textdomain' ),
	);
	$args = array(
		'label' => __( 'Timeline', 'textdomain' ),
		'description' => __( 'various ALT Lab Timelines', 'textdomain' ),
		'labels' => $labels,
		'menu_icon' => '',
		'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'trackbacks', 'page-attributes', 'custom-fields', ),
        'taxonomies' => array('category'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 5,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true,
		'has_archive' => true,
		'hierarchical' => false,
		'exclude_from_search' => false,
		'show_in_rest' => true,
		'publicly_queryable' => true,
		'capability_type' => 'post',
		'menu_icon' => 'dashicons-hammer',
	);
	register_post_type( 'Timeline', $args );

}
add_action( 'init', 'create_timeline_cpt', 0 );


//FROM https://codex.wordpress.org/Plugin_API/Filter_Reference/single_template
/* Filter the single_template with our custom function*/
function get_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == 'timeline') {
          $single_template = dirname( __FILE__ ) . '/timeline.php';
     }
     return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );


class Event {
    public $media = "";
    public $start_date = "";
    public $text = "";
    
}

function makeTheEvents ($post_id){
	        $cats = wp_get_post_categories($post_id); 

	        //if custom field type is set to a custom post type then get that instead
	        if (get_post_meta($post_id, 'type', true )){
	        	$post = get_post_meta( $post_id, 'type', true );
	        } else {
	        	$post = 'post';
	        }

			$args = array(
				'posts_per_page' => 40, 
				'orderby' => 'date',
				'category__in' =>  $cats,
				'post_type' => $post,
			);
			$the_query = new WP_Query( $args );
			// The Loop
			$the_events = array();

			if ( $the_query->have_posts() ) :				
			while ( $the_query->have_posts() ) : $the_query->the_post();
				$the_id = get_the_ID();
				//get the featured image to use as media
				if (get_the_post_thumbnail_url( $the_id, 'full')){
					$featured_img_url = get_the_post_thumbnail_url( $the_id, 'full');
					$thumbnail_id = get_post_thumbnail_id( $the_id);
					$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); 
					$caption = get_post($thumbnail_id)->post_excerpt;
				} else $featured_img_url = "";

				$event = new Event();
				//MEDIA
				@$event->media->url = $featured_img_url;
				@$event->media->caption = $alt;
				@$event->media->credit = $caption;
				//DATE
				@$event->start_date->month = get_the_date(n);
				@$event->start_date->day =  get_the_date(j);
				@$event->start_date->year =  get_the_date(Y);
				//TEXT
				@$event->text->headline = get_the_title();
				@$event->text->text = get_the_content();
				//END DATE
				if (get_post_meta($the_id, 'end_date', true) && get_post_meta($the_id, 'end_date', true)["text"]){
					$end_date = get_post_meta($the_id, 'end_date', true)["text"];
					@$event->end_date->month = intval(substr($end_date, 5, 2));
					@$event->end_date->day =  intval(substr($end_date, -2));
					@$event->end_date->year =  intval(substr($end_date,0, 4));
				}
			    array_push($the_events, $event);
				endwhile;
			endif;
			// Reset Post Data
			wp_reset_postdata();
			$the_events = json_encode($the_events);
			return $the_events;
}

//ADD THE END DATE METABOX TO POSTS

//add end date option to posts
function end_date_meta_box() {
	add_meta_box(
		'end_date_meta_box', // $id
		'Event End Date', // $title
		'show_end_date_meta_box', // $callback
		'post', // $screen
		'normal', // $context
		'high' // $priority
	);
}
add_action( 'add_meta_boxes', 'end_date_meta_box' );


function save_end_date_meta( $post_id ) {   
	// verify nonce
	if ( !wp_verify_nonce( $_POST['end_date_meta_box_nonce'], basename(__FILE__) ) ) {
		return $post_id; 
	}
	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// check permissions
	if ( 'page' === $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}  
	}
	
	$old = get_post_meta( $post_id, 'end_date', true );
	$new = $_POST['end_date'];

	if ( $new && $new !== $old ) {
		update_post_meta( $post_id, 'end_date', $new );
	} elseif ( '' === $new && $old ) {
		delete_post_meta( $post_id, 'end_date', $old );
	}
}
add_action( 'save_post', 'save_end_date_meta' );

function show_end_date_meta_box() {
	global $post;  
	$meta = get_post_meta( $post->ID, 'end_date', true ); ?>

	<input type="hidden" name="end_date_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <!-- All fields will go here -->
    <p>
	<label for="end_date[text]">End Date</label>
	<input type="date" name="end_date[text]" id="end_date[text]" class="regular-text" value="<?php echo $meta['text'];?>">
    </p>
   
	<?php }

//FLUSH PERMALINK SETTINGS
register_deactivation_hook( __FILE__, 'altlab_timeline_flush_rewrites' );
register_activation_hook( __FILE__, 'altlab_timeline_flush_rewrites' );
function altlab_timeline_flush_rewrites() {
	create_timeline_cpt();
	flush_rewrite_rules();
}
