<?php 

	/**
	 * Assignment Submissions Post Type
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/PostType
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Assignment_Submission_Post_Type
	{

		var $name 			= 'submissions';
		var $singularName 	= 'submission';
		var $slug 			= 'lectio-submission';
		var $menuName 		= 'Submissions';

		/**
		 * Actions and filters
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function __construct()
		{

			// Register the post type
			add_action( 'init', array( $this, 'init__registerPostType' ) );

			// Add the dropdown to be able to filter by author
			add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts__addAuthorFilter' ) );

		}/* __construct() */


		/**
		 * Register the post types
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function init__registerPostType()
		{

			$this->registerAssignmentSubmissionPostType();

		}/* init__registerPostType() */


		/**
		 * Register the assignment submission post type
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function registerAssignmentSubmissionPostType()
		{

			$labels = array(
				'name'					=> _x( $this->name, 'post type general name', 'studiorum-lectio' ),
				'singular_name'			=> _x( $this->singularName, 'post type singular name', 'studiorum-lectio' ),
				'menu_name'				=> _x( $this->menuName, 'admin menu', 'studiorum-lectio' ),
				'name_admin_bar'		=> _x( $this->singularName, 'add new on admin bar', 'studiorum-lectio' ),
				'add_new'				=> _x( 'Add New', $this->singularName, 'studiorum-lectio' ),
				'add_new_item'			=> __( 'Add New ' . $this->singularName, 'studiorum-lectio' ),
				'new_item'				=> __( 'New ' . $this->singularName, 'studiorum-lectio' ),
				'edit_item'				=> __( 'Edit ' . $this->singularName, 'studiorum-lectio' ),
				'view_item'				=> __( 'View ' . $this->singularName, 'studiorum-lectio' ),
				'all_items'				=> __( 'All ' . $this->name, 'studiorum-lectio' ),
				'search_items'			=> __( 'Search ' . $this->name, 'studiorum-lectio' ),
				'parent_item_colon'		=> __( 'Parent ' . $this->name, 'studiorum-lectio' ),
				'not_found'				=> __( 'No ' . $this->name . ' found.', 'studiorum-lectio' ),
				'not_found_in_trash'	=> __( 'No ' . $this->name . ' found in Trash.', 'studiorum-lectio' )
			);

			$args = array(
				'labels'				=> $labels,
				'public'				=> true,
				'publicly_queryable'	=> true,
				'show_ui'				=> true,
				'show_in_menu'			=> true,
				'query_var'				=> true,
				'rewrite'				=> array( 'slug' => $this->singularName ),
				'capability_type'		=> 'post',
				'has_archive'			=> true,
				'hierarchical'			=> false,
				'menu_position'			=> null,
				'supports'				=> array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
			);

			register_post_type( $this->slug, $args );

		}/* registerAssignmentSubmissionPostType() */


		/**
		 * Add a filter to be able to see posts by a specific author
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public function restrict_manage_posts__addAuthorFilter()
		{

			$arguments = array( 
				'name' => 'author', 
				'show_option_all' => __( 'All authors', 'studiorum-lectio' )
			);

			if( isset( $_GET['user'] ) ){
				$arguments['selected'] = sanitize_text_field( $_GET['user'] );
			}

			wp_dropdown_users( $arguments );

		}/* restrict_manage_posts__addAuthorFilter() */

	}/* Studiorum_Lectio_Assignment_Submission_Post_Type() */

	$Studiorum_Lectio_Assignment_Submission_Post_Type = new Studiorum_Lectio_Assignment_Submission_Post_Type;