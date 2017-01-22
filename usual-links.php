<?php
/**
 * Plugin Name: The Usual Links
 * Plugin URI: http://typewheel.xyz/plugin/usual-links
 * Description: Adds custom links to the link query
 * Version: 0.1
 * Author: UaMV
 * Author URI: http://vandercar.net
 *
 * The Usual Links plugin was created to extend the link modal query.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package The Usual Links
 * @version 0.1
 * @author UaMV
 * @copyright Copyright (c) 2016, UaMV
 * @link http://typewheel.xyz/plugin/usual-links
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Define plugins globals.
 */

define( 'UL_VERSION', '0.1' );
define( 'UL_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'UL_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Get instance of class if in admin.
 */

global $pagenow;

if ( is_admin() ) {
	Usual_Links::get_instance();
}

/**
 * Usual Link Class
 *
 * Extends functionality of the link modal
 *
 * @package Link That
 * @author  UaMV
 */
class Usual_Links {

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
	protected $version = UL_VERSION;

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

	/**
	 * Notices.
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	protected $editable;

	/*---------------------------------------------------------------------------------*
	 * Consturctor
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0
	 */
	private function __construct() {

		// add_action( 'plugins_loaded', array( $this, 'check_user_cap' ) );
		$this->editable = true;

		add_action( 'init', array( $this, 'register_post_type_usual_link' ), 0 );

		// Load the administrative Stylesheets and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'add_stylesheets_and_javascript' ) );

		add_action( 'media_buttons', array( $this, 'add_buttons' ), 100 );

		add_action( 'admin_footer', array( $this, 'add_popup' ), 105 );

		// Load up an administration notice to guide users to the next step
		// add_action( 'admin_notices', array( $this, 'show_notices' ) );

		add_filter( 'wp_link_query_args', array( $this, 'query_usual_links' ), 10, 1 );
		add_filter( 'wp_link_query', array( $this, 'append_usual_links' ), 10, 2 );

		add_action( 'wp_ajax_add_usual_link', array( $this, 'add_usual_link' ) );
		add_action( 'wp_ajax_remove_usual_link', array( $this, 'remove_usual_link' ) );

	} // end constructor

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
	 * Registers the plugin's administrative stylesheets and JavaScript
	 *
	 * @since    1.0
	 */
	public function check_user_cap() {

		$this->editable = current_user_can( apply_filters( 'edit_usual_links', 'edit_usual_links' ) ) ? TRUE : FALSE;

	} // end check_user_cap

	/**
	 * Registers the plugin's administrative stylesheets and JavaScript
	 *
	 * @since    1.0
	 */
	public function add_stylesheets_and_javascript() {
		wp_enqueue_style( 'usual-links', UL_DIR_URL . 'usual-links.css', array(), UL_VERSION );

		if ( $this->editable ) {

			wp_enqueue_script( 'usual-links', UL_DIR_URL . 'usual-links.js', array( 'jquery' ), UL_VERSION );
			wp_localize_script( 'usual-links', 'UsualLink', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}

	} // end add_stylesheets_and_javascript

	// Register Custom Post Type
	function register_post_type_usual_link() {

		$labels = array(
			'name'                  => _x( 'Usual Links', 'Post Type General Name', 'usual-links' ),
			'singular_name'         => _x( 'Usual Link', 'Post Type Singular Name', 'usual-links' ),
			'menu_name'             => __( 'Post Types', 'usual-links' ),
			'name_admin_bar'        => __( 'Post Type', 'usual-links' ),
			'archives'              => __( 'Item Archives', 'usual-links' ),
			'attributes'            => __( 'Item Attributes', 'usual-links' ),
			'parent_item_colon'     => __( 'Parent Item:', 'usual-links' ),
			'all_items'             => __( 'All Items', 'usual-links' ),
			'add_new_item'          => __( 'Add New Item', 'usual-links' ),
			'add_new'               => __( 'Add New', 'usual-links' ),
			'new_item'              => __( 'New Item', 'usual-links' ),
			'edit_item'             => __( 'Edit Item', 'usual-links' ),
			'update_item'           => __( 'Update Item', 'usual-links' ),
			'view_item'             => __( 'View Item', 'usual-links' ),
			'view_items'            => __( 'View Items', 'usual-links' ),
			'search_items'          => __( 'Search Item', 'usual-links' ),
			'not_found'             => __( 'Not found', 'usual-links' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'usual-links' ),
			'featured_image'        => __( 'Featured Image', 'usual-links' ),
			'set_featured_image'    => __( 'Set featured image', 'usual-links' ),
			'remove_featured_image' => __( 'Remove featured image', 'usual-links' ),
			'use_featured_image'    => __( 'Use as featured image', 'usual-links' ),
			'insert_into_item'      => __( 'Insert into item', 'usual-links' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'usual-links' ),
			'items_list'            => __( 'Items list', 'usual-links' ),
			'items_list_navigation' => __( 'Items list navigation', 'usual-links' ),
			'filter_items_list'     => __( 'Filter items list', 'usual-links' ),
		);
		$args = array(
			'label'                 => __( 'Usual Link', 'usual-links' ),
			'description'           => __( 'Post Type Description', 'usual-links' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'author', ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => false,
			'publicly_queryable'    => false,
			'capability_type'       => 'post',
		);
		register_post_type( 'usual_link', $args );

	}

	/**
	 * Adds inline post buttons to post edit screen
	 *
	 * @since    1.0.0
	 */
	public function add_buttons() {

		// check if supported by current post type
		if ( $this->editable ) {

			echo '<a href="#TB_inline?&inlineId=usual-link-form" class="thickbox button usual-link-button" id="edit-usual-links" title="Edit Usual Links"><span class="dashicons dashicons-admin-links"></span> ' . __( 'Edit Usual Links' ) . '</a>';

		}

	} // end add_buttons

	/**
	 * Renders thickbox triggered by usual links button
	 *
	 * @since    1.0.0
	 */
	public function add_popup() {

		// check if supported by current post type
		if ( $this->editable ) {

			// get the form html
			$this->get_usual_links_form();

		}

	} // end add_popup

	/**
	 * Renders form for inline post management
	 *
	 * @since    1.0.0
	 */
	public function get_usual_links_form() {

		if ( $this->editable ) {

			if ( isset( $error_msg ) && $error_msg ) : ?>
				<div class="error"><?php echo $error_msg; ?></div>
			<?php endif; ?>

			<div id="usual-link-form" style="<?php echo is_admin() ? 'display:none;' : ''; ?>">
				<div class="wrap">
					<div id="usual-link-fields">
						<input type="text" id="new_usual_link_title" class="usual-link-new" tabindex="4" placeholder="<?php echo 'Enter title'; ?>" />
						<input type="text" id="new_usual_link_content" class="usual-link-new" tabindex="5" autocomplete="off" placeholder="<?php echo 'Enter URL' ?>" />
						<input type="button" id="add_usual_link" class="button button-primary" value="Add Link" onclick="AddUsualLink();" tabindex="6" title="Add link"/>
						<input type="hidden" value="<?php echo wp_create_nonce( 'manage-usual-link' ); ?>" id="usual_link_security"/>
					</div>

					<ul id="usual-linked-list">
						<?php
						$usual_links = get_posts( array(
							'posts_per_page' => -1,
							'post_type' => 'usual_link',
							'post_status' => 'publish',
							) );

						$current_user_ID = get_current_user_ID();

						foreach ( $usual_links as $link ) {

							_e( '<li id="usual-link-' . $link->ID . '" class="usual-link"><span class="usual-link-title" title="' . $link->post_title . '">' . mb_strimwidth( $link->post_title, 0, 12, '…' ) . '</span><a href="http://' . $link->post_content . '" title="' . $link->post_content . '">' . mb_strimwidth( $link->post_content, 0, 42, '…' ) . '</a><div id="remove-usual-link-' . $link->ID . '" onclick="RemoveUsualLink(' . $link->ID . ')" class="dashicons dashicons-trash" title="Trash Link" data-link-id="' . $link->ID . '"></div></li>' );

						}
						?>

					</ul>
				</div>
			</div>

			<?php

		}

	} // end get_inline_post_form

	/**
	 * Action target that saves new inline post from AJAX request
	 *
	 * @since    1.0.0
	 */
	public static function add_usual_link() {

		// Check the nonce
		if ( check_ajax_referer( 'manage-usual-link', 'usual_link_security' ) ) {

			if ( null == $_POST['title'] ) {

				$response['success'] = false;
				$response['notice'] = __( '<div class="usual-link-notice error"><p>Please add a link title.</p></div>', 'phq' );

			} else if ( null == $_POST['content'] ) {

				$response['success'] = false;
				$response['notice'] = __( '<div class="usual-link-notice error"><p>Please add a link URL.</p></div>', 'phq' );

			} else {

				$post = array(
					'post_content' => $_POST['content'],
					'post_status' => 'publish',
					'post_type' => 'usual_link',
					'post_title' => $_POST['title'],
					'post_author' => get_current_user_ID(),
					);

				$link_ID = wp_insert_post( $post );

				$usual_link = get_post( $link_ID, ARRAY_A );

				// generate the response
				$response['success'] = true;
				$response['usual_link'] = $usual_link;
				// $response['notice'] = __( '<div class="usual-link-notice updated"><p><strong>' . $usual_link['post_content'] . '</strong> successfully added.</p></div>', 'phq' );

			}

			wp_send_json( $response );

		}

	}

		/**
	 * Action target that displays 'Add Prayers' popup
	 *
	 * @since    0.1
	 */
	public static function remove_usual_link() {

		// Check the nonce
		if ( check_ajax_referer( 'manage-usual-link', 'usual_link_security' ) ) {

			$link_ID = $_POST['link_ID'];

			$usual_link = get_post( $link_ID, ARRAY_A );

			wp_delete_post( $link_ID, FALSE );

			// generate the response
			$response = array( 'success' => true, 'deleted_ID' => $link_ID );
			$response['success'] = true;
			$response['deleted_ID'] = $link_ID;
			// $response['notice'] = __( '<div class="usual-link-notice updated"><p><strong>' . $usual_link['post_content'] . '</strong> successfully removed.</p></div>', 'phq' );

			wp_send_json( $response );

		}

	}

	public function query_usual_links( $query ) {

		// array_push( $query['post_type'], 'usual_link');

    	return $query;

	}

	public function append_usual_links( $results, $query ) {

		foreach ( $results as &$result ) {

			if ( 'usual_link' == get_post_type( $result['ID'] ) ) {

				$usual_link = get_post( $result['ID'] );
				$result['permalink'] = $usual_link->post_content;

			}

		}

	    return $results;

	}

} // end class
