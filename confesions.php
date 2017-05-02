<?php

/*
Plugin Name:  Confessions - special commnets
Description:  This plugin ads a confesions (special comments) custom post type bassed functionality.
Version:	  0.1
Author:       Star Design Interactive
Author URI:   https://www.stardesign.pl
License:      GPL3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
Domain Path:  /languages
Text Domain:  confes
*/

/* 
Security
*/

if (!defined('ABSPATH')) 
{  
	exit;
}

/*
 * Basic Setup
 */

load_plugin_textdomain('confession', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

/*
 * Installation
 */
function confes_activate () {
		
	if (! get_option('confes_activated') ) {
	
	/* Initialize Settings */
	
	add_option('confes_activated',"1");

	}
}
register_activation_hook( __FILE__ , 'confes_activate' );


function confes_deactivate () {
	// nothing to do
}
register_deactivation_hook( __FILE__ , 'confes_deactivate' );


function confes_delete () {
	if ( get_option('confes_activated') ) {
		delete_option('confes_activated');
	}
}
register_uninstall_hook( __FILE__ , 'confes_delete' ); 


/* 
 * Confession custom post type
 */
function confes_create_post_type() {
	
	
	// Set UI labels for Custom Post Type
	$labels = array(
		'name'                => __( 'Confessions' ),
		'singular_name'       => __( 'Confession' ),
		'menu_name'           => __( 'Wyznania' ),
		'parent_item_colon'   => __( 'Parent Confession' ),
		'all_items'           => __( 'Wszystkie wyznania' ),
		'view_item'           => __( 'View Confession' ),
		'add_new_item'        => __( 'Dodaj nowe wyznanie' ),
		'add_new'             => __( 'Dodaj nowe' ),
		'edit_item'           => __( 'Edit Confession' ),
		'update_item'         => __( 'Update Confession' ),
		'search_items'        => __( 'Search Confession' ),
		'not_found'           => __( 'Not Found' ),
		'not_found_in_trash'  => __( 'Not found in Trash' ),
	);
	
	// Set other options for Custom Post Type
	$args = array(
		'label'               => __( 'confessions' ),
		'description'         => __( 'Special user confessions' ),
		'labels'              => $labels,
		// Features this CPT supports in Post Editor
		'supports'            => array( 'title', 'editor', 'revisions', 'custom-fields' ),
		// You can associate this CPT with a taxonomy or custom taxonomy. 
		'taxonomies'          => array( 'genres' ),
		/* A hierarchical CPT is like Pages and can have
		* Parent and child items. A non-hierarchical CPT
		* is like Posts.
		*/	
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'rewrite' => array('slug' => 'wyznania'),
	);
	
	register_post_type( 'confession', $args );
}
add_action( 'init', 'confes_create_post_type' );


/* 
 * Set custom menu order
 */
function confes_custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    return array(
        'index.php', // the dashboard link
        'edit.php?post_type=confession',
        'edit.php?post_type=page', 
        'edit.php' // posts
    );
}
add_filter('custom_menu_order', 'confes_custom_menu_order');
add_filter('menu_order', 'confes_custom_menu_order');


function confes_css() {
	
	wp_register_style('confes_style', plugins_url('/css/style.css',__FILE__ ));
	wp_enqueue_style('confes_style');
}
add_action( 'wp_head', 'confes_css' );


/*
 * Custom post type form processing
 */
function confes_insert_custom_post()
{
	if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {

		// Do some minor form validation to make sure there is content
		if (isset ($_POST['title'])) {
			$title =  $_POST['title'];
		} else {
			$title = 'conf#'.rand();
		}
		if (isset ($_POST['description'])) {
			$description = $_POST['description'];
		} else {
			echo 'Please enter the content';
		}

		// Add the content of the form to $post as an array
		$new_post = array(
			'post_title'    => $title,
			'post_content'  => $description,
			'post_status'   => 'draft',           // Choose: publish, preview, future, draft, etc.
			'post_type' => 'confession'
		);
		
		//save the new post
		$pid = wp_insert_post($new_post); 
		
		//ads custom fields
		add_post_meta( $pid, 'me_too', 0 );
		add_post_meta( $pid, 'like', 0 );
		add_post_meta( $pid, 'understood', 0 );
	}
}
add_action('init', 'confes_insert_custom_post');


// Shortcode to display confession form on any page or post
function confes_form_shortcode(){
	
	$nonce = wp_nonce_field( 'new-post' );
	
	$back_btn = '<a class="custom" href="javascript:history.back()">Wróć
					<span class="bar bar-1"></span>
					<span class="bar bar-2"></span>
					<span class="bar bar-3"></span>
					<span class="bar bar-4"></span>
					<span class="bar bar-1 hover"></span>
					<span class="bar bar-2 hover"></span>
					<span class="bar bar-3 hover"></span>
					<span class="bar bar-4 hover"></span>
				</a>';
	
	if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {
		$form_htm = '<div class="row">
							<div class="col-md-10 col-md-offset-1">
								<div class="conf-thanks">
									<p>Dziękujemy. Twoje wyznanie trafiło do moderacji i wkrótce pojawi się na stronie.</p>
									'.$back_btn.'
								</div>
							</div>
						</div>';
	}else{
		$form_htm = '
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<form style="" class="confes-form" id="new_post" name="new_post" method="post" action="" >
					<h3 class="form-title"><span>Twoje wyznanie</span></h3>
					<!-- post Content -->
					<p>
						<textarea id="description" tabindex="3" name="description" cols="50" rows="4" placeholder="" required></textarea>
					</p>
					<input type="submit" value="wyślij" />
					<input type="hidden" name="action" value="new_post" />
					'.$nonce.'
				</form>
			</div>
		</div>
		';
	}
	return $form_htm ;
}
add_shortcode('confession_form', 'confes_form_shortcode');


// Shortcode to display confessions on any page or post
function confes_listing_shortcode(){
	
	global $wp_query, $paged;
	
	$temp = $wp_query; 
	$wp_query = null; 
	
	$confessions_html = '';
	
	$args = array( 'post_type' => 'confession', 'showposts' => '10', 'paged' => $paged);
	$wp_query = new WP_Query( $args ); 
	
	//bootstrap layout
	$confessions_html .= '<div class="row"><div class="col-md-10 col-md-offset-1">';
	
	if ( $wp_query->have_posts() ) :
		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			
			$post_id = get_the_ID();
			
			$me_too = get_post_meta( $post_id, 'me_too', true );
			$like = get_post_meta( $post_id, 'like', true );
			$understood = get_post_meta( $post_id, 'understood', true );
			
			$confessions_html .= '<div id="'.$post_id.'" class="entry-content confession">'.get_the_content().'<div class="clear"></div>';
			
			$confessions_html .= '<div class="buttons-cont">';
			
			$confessions_html .= '
			<a class="button vote-button me_too" post_id="'.$post_id.'" button_type="me_too" >
				ja też (<span class="value">'.$me_too.'</span>)
			</a>';
			
			$confessions_html .= '
			<a class="button vote-button like" post_id="'.$post_id.'" button_type="like">
				lubię (<span class="value">'.$like.'</span>)
			</a>';
			
			$confessions_html .= '
			<a class="button vote-button understood" post_id="'.$post_id.'" button_type="understood">
				rozumiem (<span class="value">'.$understood.'</span>)
			</a>';
			
			$confessions_html .= '</div></div>';
		}
		
		//pagination
		$confessions_html.='<div class="conf-pagination">
								<div class="conf-prev-link">'.get_next_posts_link( "&laquo; Starsze wyznania").'</div>
								<div class="conf-next-link">'.get_previous_posts_link( "Nowsze wyznania &raquo;").'</div>
							</div>';
		
	else:
		$confessions_html = '<p>Brak wyznań do wyświetlenia. Bądź pierwsza i dodaj swoje wyznanie.</p>';
	endif;
	
	//closing bootstrap layout
	$confessions_html .= '</div></div>';
	
	$wp_query = null; 
	$wp_query = $temp;  // Reset
	
	return $confessions_html;
}
add_shortcode('confessions_list', 'confes_listing_shortcode');



// Shortcode to display confessions counter on any page or post
function confes_counter_shortcode(){
	
	$counter_html = '';
	
	$args = array( 'post_type' => 'confession', 'post_status' => 'publish' );
	$the_query = new WP_Query( $args ); 
	
	$count = $the_query->found_posts;
	
	//bootstrap layout
	$counter_html .= '<div class="col-md-10 col-md-offset-1">';
	
	$counter_html .= '<p>Wszystkich wyznań: <strong>'.$count.'</strong></p>';
	
	//closing bootstrap layout
	$counter_html .= '</div>';
	
	return $counter_html;
}
add_shortcode('confessions_counter', 'confes_counter_shortcode');



/**
 * Meta box(es) for Confession
 */
 
 
// Register meta boxes
function confes_register_meta_boxes() {
	add_meta_box( 'buttons-values', 'Wartości buttonów:', 'confes_my_display_callback', 'confession' );
}
add_action( 'add_meta_boxes', 'confes_register_meta_boxes' );


// Meta box display callback.
function confes_my_display_callback( $post ) {
	
	$outline = '<label for="me_too" style="width:100px; display:inline-block;">'. esc_html__('Ja też', 'text-domain') .'</label>';
	$me_too = get_post_meta( $post->ID, 'me_too', true );
	$outline .= '<input type="text" name="me_too" id="me_too" class="me_too" value="'. esc_attr($me_too) .'" style="width:50px;"/><br />';
	
	$outline .= '<label for="like" style="width:100px; display:inline-block;">'. esc_html__('Lubię', 'text-domain') .'</label>';
	$like = get_post_meta( $post->ID, 'like', true );
	$outline .= '<input type="text" name="like" id="like" class="like" value="'. esc_attr($like) .'" style="width:50px;"/><br />';
	
	$outline .= '<label for="understood" style="width:100px; display:inline-block;">'. esc_html__('Rozumiem', 'text-domain') .'</label>';
	$understood = get_post_meta( $post->ID, 'understood', true );
	$outline .= '<input type="text" name="understood" id="understood" class="understood" value="'.$understood.'" style="width:50px;"/>';
	
	echo $outline;
}

//Save meta box content.
function confes_save_meta_box( $post_id ) {

    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
        return;
    }
    
    if( !current_user_can( 'edit_post', $post_id ) ){
        return;
    } elseif ( !current_user_can ( 'edit_page', $post_id) ){
        return;
    }
    
    if( isset( $_REQUEST['me_too'] ) ){
        update_post_meta( $post_id, 'me_too', sanitize_text_field( $_POST['me_too'] ) );
    }
    if( isset( $_REQUEST['like'] ) ){
        update_post_meta( $post_id, 'like', sanitize_text_field( $_POST['like'] ) );
    }
    if( isset( $_REQUEST['understood'] ) ){
        update_post_meta( $post_id, 'understood', sanitize_text_field( $_POST['understood'] ) );
    }
}
add_action( 'save_post', 'confes_save_meta_box' );


/**
 * Clickable buttons logic:
 */

//updates votes using Ajax
function confes_update_button_clicks(){
	
	$post_id = $_POST['post_id'];
	$button_type = $_POST['button_type'];
	
	
	
	if (confes_update_button_value($post_id, $button_type, 1)) {
		$clicks = get_post_meta($post_id, $button_type, true);
		echo 'You have voted on post ID = '.$post_id.', button type = '.$button_type.'!';
		echo $clicks;
    }else{   
        echo 'Voted failed!';
    }     
    die();
}
add_action( 'wp_ajax_update_button_clicks', 'confes_update_button_clicks');
add_action( 'wp_ajax_nopriv_update_button_clicks', 'confes_update_button_clicks');


//update buttons vals f.
function confes_update_button_value($post_id, $button_type, $amount) {
	
	$clicks = get_post_meta($post_id, $button_type, true);
	
	$clicks = intval($clicks) + intval($amount);
	return update_post_meta($post_id, $button_type, $clicks);
}



// Using WP Ajax to handle voting
function confes_enqueue_ajax_scripts(){
	wp_enqueue_script('jquery');
	
	//nasz plik z którego wyślemy zapytanie
	//wp_enqueue_script('ajax-main', get_bloginfo('template_url').'/js/main.js' );
	wp_enqueue_script('ajax-main', plugins_url('/js/clicks.js',__FILE__ ));
	
	//ustalamy odpowiedni protokół
	if ( isset($_SERVER['HTTPS']) )
		$protocol = 'https://';
	else  
		$protocol = 'http://';

	//pobieramy adres do pliku admin-ajax.php
	$admin_ajax_url = admin_url( 'admin-ajax.php', $protocol );

	//za pomocą tej funkcji przekazujemy zmienną zawierająca adres, do javascript
	wp_localize_script( 'ajax-main', 'ajax_options', array('admin_ajax_url' => $admin_ajax_url) );
}
//add_action( 'wp_enqueue_scripts', 'confes_enqueue_ajax_scripts');
add_action( 'plugins_loaded', 'confes_enqueue_ajax_scripts');







/* testing - don't work at the moment */
/*
function confes_page_template( $page_template )
{
    if ( is_page( 'confessions' ) ) {
        $page_template = dirname( __FILE__ ) . '/confessions-page-template.php';
    }
    return $page_template;
}
add_filter( 'page_template', 'confes_page_template' );
*/

/*
Options Page
*/
/*
function hdfys_options() {
	
	echo '
	<div class="wrap">
	<h1>'. __('Options','hellodollyforyoursong').' › Hello Dolly For Your Song</h1>
	
	<form method="post" action="options.php">';
	
	do_settings_sections( 'hdfys-options' );
	settings_fields( 'hdfys_settings' );
	submit_button();

	echo '</form></div><div class="clear"></div>';
}

function hdfys_options_display_songtext() {
	echo '<textarea style="width:600px;height:400px;" class="regular-text" type="text" name="hdfys_song" id="hdfys_song">'. get_option('hdfys_song') .'</textarea>';
}

function hdfys_options_content_description() { 
	echo '<p>'. __('Post your lyrics.','hellodollyforyoursong').'</p>'; 
}

function hdfys_options_display() {
	
	add_settings_section("content_settings_section", __('Just one thing','hellodollyforyoursong') , "hdfys_options_content_description", "hdfys-options");
	
	add_settings_field("hdfys_song", __('Text','hellodollyforyoursong') , "hdfys_options_display_songtext", "hdfys-options", "content_settings_section");
	
	register_setting("hdfys_settings", "hdfys_song", "hdfys_validate_songtext");
}
add_action("admin_init", "hdfys_options_display");

function hdfys_validate_songtext ( $songtext ) {

    return $songtext;
} 

function hdfys_show_options() {
	add_options_page('Hello Dolly For Your Song', 'Hello Dolly Your Song', 10, basename(__FILE__), "hdfys_options");
}
add_action( 'admin_menu', 'hdfys_show_options');
*/

