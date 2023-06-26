<?php
/*
Plugin Name: Web Dev Agent - Case Studies
Plugin URI: 
Description: Display Web Agency Portfolio Case Studies
Version: 1.0.0
Author: edk
Author URI: evolutiondesuka.com
*/

// ensure application access only
if( !defined('ABSPATH') ) {
   exit;
}


class WedDevAgentCaseStudies {

	public function __construct() {

      // create custom post type 'wda_casestudy'
      add_action( 'init', array($this,'create_casestudy_post_type' ));

      // assets
      add_action('wp_enqueue_scripts',array($this,'enqueue_assets'));

      // 'edit post' page
		add_action('add_meta_boxes', array( $this,'add_casestudy_meta_box')); 
		add_action('save_post',array($this,'save_custom_meta'));

      // front-end UI
      add_shortcode('casestudies',array($this,'shortcode_html'));

   }


   //
   // create custom post type 'wda_casestudy'
   //
   public function create_casestudy_post_type() {

      $labels = array(
         'name' => __('WDA Case Studies','web-dev-agent'),
         'singular_name' =>  __('WDA Case Study','web-dev-agent'),
         'menu_name' => 'Case Studies',
      );
      $args = array(
         'labels' => $labels,
         'description' => 'Case Study Custom Post Type',
         'supports' => array('title','editor','thumbnail'),
         'hierarchical' => true,
         'taxonomies' => array('category'),
         'public' => true,
         'show_ui' => true,
         'show_in_menu' => true,
         'show_in_nav_menus' => true,
         // 'show_in_rest' => true, // in the REST API. Set this to true for the post type to be available in the block editor.
         'has_archive' => true,
         'rewrite' => array( 'slug' => 'casestudy' ),  // custom slug
         'exclude_from_search' => true,
         'publicly_queryable' => true,    // false will exclude archive- and single- templates
         'capabilitiy' => 'manage_options',
         'menu_icon' => 'dashicons-media-text',
      );
      register_post_type('wda_casestudy',$args);
   }


   //
   // assets
   //
   public function enqueue_assets() 
   {
      // to do : these files are included w/ web dev agent theme - how to avoid duplication - plugin can be standalone?
      // check if these are included twice (duplicate) if present
      // - if plugin included in other theme?
      // wp_enqueue_style(
      //    'wda_outline',
      //    plugin_dir_url( __FILE__ ) . 'css/outline.css',
      //    array(),
      //    1,
      //    'all'
      // );  
      // wp_enqueue_style(
      //    'wda_outline_layouts',
      //    plugin_dir_url( __FILE__ ) . 'css/outline-layouts.css',
      //    array(),
      //    1,
      //    'all'
      // );  
      // wp_enqueue_style(
      //    'wda_outline_custom_props',
      //    plugin_dir_url( __FILE__ ) . 'css/outline-custom-props.css',
      //    array(),
      //    1,
      //    'all'
      // );  
      // wp_enqueue_style(
      //    'wda_outline_utilities',
      //    plugin_dir_url( __FILE__ ) . 'css/outline-utilities.css',
      //    array(),
      //    1,
      //    'all'
      // ); 
      // wp_enqueue_script(
      //    'web-dev-agent',
      //    plugin_dir_url( __FILE__ ) . 'js/web-dev-agent.js',
      //    array('jquery'),
      //    1,
      //    true
      // );
   }
   

   //
   // 'edit post' page
   //
	public function add_casestudy_meta_box( $post_type ) {

		// Limit meta box to certain post types
		$post_types = array( 'wda_casestudy' );

		if ( in_array( $post_type, $post_types ) ) {

			add_meta_box(
				'wda_casestudy',
				__( 'Tagline', 'textdomain' ),
				array( $this, 'render_casestudy_meta_box' ),
				$post_types,
				'advanced',
				'high'
			);
		}
	}

   public function render_casestudy_meta_box($post) {

		wp_nonce_field('wda_casestudies_meta_box','wda_casestudies_meta_nonce');

		$tagline = get_post_meta( $post->ID, 'wda_casestudy_tagline', true );
		$url = get_post_meta( $post->ID, 'wda_casestudy_url', true );

      // to do : limit input text lengths - rollout
     ?>
      <div>
         <label for="wda_casestudy_custom_metabox_tagline">tagline
         </label>
         <input
            type="text"
            name="wda_casestudy_tagline_field"
            id="wda_casestudy_tagline_field"
            value="<?php echo $tagline; ?>"
         >
      </div>
      <div>
         <label for="wda_casestudy_custom_metabox_url">url
         </label>
         <input
            type="text"
            name="wda_casestudy_url_field"
            id="wda_casestudy_url_field"
            value="<?php echo $url; ?>"
         >
      </div>
      <?php
   }

	public function save_custom_meta($post_id) {

      //if (isset($_POST)) die(print_r($_POST));     // debug

		if ( ! isset( $_POST['wda_casestudies_meta_nonce'] ) ) {
			return $post_id;
		}
		$nonce = $_POST['wda_casestudies_meta_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'wda_casestudies_meta_box' ) ) {
			return $post_id;
		}

		// autosave, our form has not been submitted
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
      if (!current_user_can('edit_page',$post_id)) {
         return $post_id;
      }

		// Sanitize the user input
		$tagline = sanitize_text_field( $_POST['wda_casestudy_tagline_field'] );
		$url = sanitize_text_field( $_POST['wda_casestudy_url_field'] );
      // if (isset($_POST)) die(print_r('listen'));     // debug

		// Update the meta fields
		update_post_meta( $post_id, 'wda_casestudy_tagline', $tagline);
		update_post_meta( $post_id, 'wda_casestudy_url', $url );
	}


   //
   // front-end UI - shortcode
   //
   public function shortcode_html() {

      ob_start(); // buffer output

      $args = array(
         'post_type' => 'wda_casestudy',
         'posts_per_page' => 10,
      );
      $loop = new WP_Query($args);


      // we limit to 3 most recent projects
      $count = 0;

      ?>
      <section class="animated_tiles">
         <h3>Our work</h3>
         <ul>
            <?php
            while ( $loop->have_posts() ) {
               $loop->the_post();
                  ?>
                  <li>
                     <?php
                     if(has_post_thumbnail()):?>
                        <img src="<?php the_post_thumbnail_url('large'); ?>"/>
                     <?php endif;
                     ?>
                     <h3><?php echo get_the_title();?></h3>
                     <p><?php echo get_post_meta( get_the_ID(), 'wda_casestudy_tagline', true );?></p>
                     <!-- <p><?php echo get_the_excerpt();?></p> -->
                     <button><a href="<?php echo get_permalink(get_the_ID()); ?>">project details</a></button>
                     ?>
                  </li>
               <?php
               $count++;
               if($count > 2) break;
            }
            ?>
         </ul>
         <!-- to do : get link to archive page from a singlepage - eg for same custom post type ? -->
         <button class=""><a href="/wordpress-demo/casestudy/">More Projects</a></button>
      </section>
      <?php

      $buffered_data = ob_get_clean();    // return buffered output
      return $buffered_data;
   }

}


new WedDevAgentCaseStudies;