<?php
/**
 * Plugin Name: Common Links
 * Plugin URI: http://typewheel.xyz/wp
 * Description: Adds custom links to the editor's link query
 * Version: 1.1
 * Author: UaMV
 * Author URI: http://vandercar.net
 *
 * Common Links plugin was created to extend the link modal query.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package Common Links
 * @version 1.1
 * @author UaMV
 * @copyright Copyright (c) 2016, UaMV
 * @link http://typewheel.xyz/wp
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


// Define plugins globals.
define( 'COMMON_LINKS_VERSION', '1.1' );
define( 'COMMON_LINKS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Get instance of class if in admin.
global $pagenow;

if ( is_admin() ) {
	Common_Links::get_instance();
}

/**
 * Common Link Class
 *
 * Extends functionality of the link modal
 *
 * @package Link That
 * @author  UaMV
 */
class Common_Links {

	/*---------------------------------------------------------------------------------*
	 * Attributes
	 *---------------------------------------------------------------------------------*/

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0
	 *
	 * @var     string
	 */
	protected $version = COMMON_LINKS_VERSION;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Notices.
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	protected $notices;

	/*---------------------------------------------------------------------------------*
	 * Consturctor
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'register_post_type_common_link' ), 10 );

		// Load the administrative Stylesheets and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'add_stylesheet' ) );

		add_filter( 'enter_title_here', array( $this, 'enter_title_here_common_link' ), 10, 2 );
		add_action( 'edit_form_after_title', array( $this, 'add_url_field' ) );

		add_action( 'save_post', array( $this, 'save_url' ), 10, 2 );

		add_filter( 'manage_common_link_posts_columns', array( $this, 'modify_admin_columns' ), 10, 1 );
		add_action( 'manage_common_link_posts_custom_column', array( $this, 'custom_admin_column' ), 10, 2 );

		add_filter( 'wp_link_query', array( $this, 'append_common_links' ), 10, 2 );

	} // end __construct

	/*---------------------------------------------------------------------------------*
	 * Public Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		} // end if

		return self::$instance;

	} // end get_instance

	/**
	 * Registers the plugin's administrative stylesheet
	 *
	 * @since    1.0
	 */
	public function add_stylesheet() {

		wp_enqueue_style( 'common-links', COMMON_LINKS_DIR_URL . 'common-links.css', array(), COMMON_LINKS_VERSION );

	} // end add_stylesheet

	/**
	 * Registers the custom post type
	 *
	 * @since    1.0
	 */
	function register_post_type_common_link() {

		$labels = array(
			'name'                  => _x( 'Common Links', 'Post Type General Name', 'common-links' ),
			'singular_name'         => _x( 'Common Link', 'Post Type Singular Name', 'common-links' ),
			'menu_name'             => __( 'Common Links', 'common-links' ),
			'name_admin_bar'        => __( 'Common Link', 'common-links' ),
			'all_items'             => __( 'Common Links', 'common-links' ),
			'add_new_item'          => __( 'Add New Common Link', 'common-links' ),
			'add_new'               => __( 'Add New', 'common-links' ),
			'new_item'              => __( 'New Common Links', 'common-links' ),
			'edit_item'             => __( 'Edit Common Link', 'common-links' ),
			'update_item'           => __( 'Update Common Link', 'common-links' ),
			'view_item'             => __( 'View Common Link', 'common-links' ),
			'view_items'            => __( 'View Common Links', 'common-links' ),
			'search_items'          => __( 'Search Common Links', 'common-links' ),
			'not_found'             => __( 'Not found', 'common-links' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'common-links' ),
		);
		$capabilities = array(
            'edit_post' => 'manage_options',
            'read_post' => 'manage_options',
            'delete_post' => 'manage_options',
            'delete_posts' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options'
        );
		$args = array(
			'label'                 => __( 'Common Link', 'common-links' ),
			'description'           => __( 'Post Type Description', 'common-links' ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => 'tools.php',
			'menu_position'         => 81,
			'menu_icon'             => 'dashicons-admin-links',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => false,
			'publicly_queryable'    => false,
			'capabilities'          => $capabilities,
			'capability_post'       => 'post'
		);
		register_post_type( 'common_link', $args );

	} // end register_post_type_common_link

	/**
	 * Modify Enter title placeholder
	 *
	 * @since    1.0
	 */
	function enter_title_here_common_link( $title, WP_Post $post ) {

		if ( 'common_link' === $post->post_type ) {

			$title = esc_html__( 'Enter link title', 'typewheel' );

		}

		return $title;
	} // end enter_title_here_common_link

	/**
	 * Adds URL field
	 *
	 * @since    1.0
	 */
	public function add_url_field() {

		global $post;

		if ( 'common_link' == $post->post_type ) {

			$url = $post->post_content ? $post->post_content : '';

			?>
			<input type="text" name="common_link_content" size="30" value="<?php echo $url; ?>" id="common-link-content" placeholder="Enter URL" spellcheck="false" autocomplete="off">
			<?php

		}

	} // end add_url_field

	/**
	 * Saves URL field to database
	 *
	 * @since    1.0
	 */
	public function save_url( $post_id ) {

		global $pagenow;

		if ( isset( $_POST['common_link_content'] ) && ( 'post.php' == $pagenow ) ) {

			$common = array(
				'ID'           => $post_id,
				'post_content' => str_replace( array( 'http://', 'https://' ), '', $_POST['common_link_content'] )
			);

			remove_action( 'save_post', array( $this, 'save_url' ) );
			wp_update_post( $common, true );
			add_action( 'save_post', array( $this, 'save_url' ) );

		}

	} // end save_url

	/**
	 * Contral available admin columns
	 *
	 * @since    1.0
	 */
	public function modify_admin_columns( $columns ) {

		$columns['common-link-url'] = __( 'URL', 'common-links' );

		unset( $columns['date'] );
		$columns['date'] = __( 'Date', 'common-links' );

		return $columns;

	} // end modify_admin_columns

	/**
	 * Add custom column for URLs
	 *
	 * @since    1.0
	 */
	public function custom_admin_column( $column, $post_id ) {

		if ( 'common-link-url' == $column ) {
			echo '<a href="http://' . get_post( $post_id )->post_content . '">' . get_post( $post_id )->post_content . '</a>';
		}

	} // end custom_admin_column

	/**
	 * Add common links to link query
	 *
	 * @since    1.0
	 */
	public function append_common_links( $results, $query ) {

		foreach ( $results as &$result ) {

			if ( 'common_link' == get_post_type( $result['ID'] ) ) {

				$common_link = get_post( $result['ID'] );
				$result['permalink'] = $common_link->post_content;

			}

		}

	    return $results;

	} // end append_common_links

} // end class
