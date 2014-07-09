<?php 

	/**
	 * Assignment Taxonomies
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/Taxonomies
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Assignment_Taxonomy
	{

		var $name 			= 'submission-categories';
		var $singularName 	= 'submission-category';
		var $slug 			= 'lectio-submission-category';
		var $menuName 		= 'Subm. Categories';
		var $attachedOjects = array( 'lectio-submission' );

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

			// Register our taxonomies
			add_action( 'init', array( $this, 'init__registerTaxonomy' ), 10 );

			// Add custom meta to taxonomies
			add_action( 'init', array( $this, 'init__addMetaToTaxonomies' ), 20 );

		}/* __construct() */


		/**
		 * Register our taxonomies
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function init__registerTaxonomy()
		{

			$this->registerSubmissionCategoryTaxonomy();

		}/* init__registerTaxonomy() */


		/**
		 * Register the submission category taxonomy
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function registerSubmissionCategoryTaxonomy()
		{

			$labels = array(
				'name'				=> _x( $this->name, 'taxonomy general name' ),
				'singular_name'		=> _x( $this->singularName, 'taxonomy singular name' ),
				'search_items'		=> __( 'Search ' . $this->name ),
				'all_items'			=> __( 'All ' . $this->name ),
				'parent_item'		=> __( 'Parent ' . $this->singularName ),
				'parent_item_colon'	=> __( 'Parent ' . $this->singularName . ' :' ),
				'edit_item'			=> __( 'Edit ' . $this->singularName ),
				'update_item'		=> __( 'Update ' . $this->singularName ),
				'add_new_item'		=> __( 'Add New ' . $this->singularName ),
				'new_item_name'		=> __( 'New ' . $this->singularName . ' Name' ),
				'menu_name'			=> __( $this->menuName ),
			);

			$args = array(
				'hierarchical'			=> false,
				'labels'				=> $labels,
				'show_ui'				=> true,
				'show_admin_column'		=> true,
				'query_var'				=> true,
				'rewrite'				=> array( 'slug' => $this->slug ),
			);

			register_taxonomy( $this->slug, $this->attachedOjects, $args );

			foreach( $this->attachedOjects as $key => $object ){
				register_taxonomy_for_object_type( $this->slug, $object );
			}

		}/* registerSubmissionCategoryTaxonomy() */


		/**
		 * Add meta to our taxonomies
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function init__addMetaToTaxonomies()
		{

			if( !is_admin() ){
				return;
			}

			$this->addMetaToSubmissionCategories();

		}/* init__addMetaToTaxonomies() */


		/**
		 * We need some meta for our submission categories. 
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function addMetaToSubmissionCategories()
		{

			$prefix = 'sub_cat_meta_';

			$config = array(
				'id' => $prefix . 'meta_box',		// meta box id, unique per meta box
				'title' => 'Assignment Settings',	// meta box title
				'pages' => array( $this->slug ),	// taxonomy name, accept categories, post_tag and custom taxonomies
				'context' => 'normal',				// where the meta box appear: normal (default), advanced, side; optional
				'fields' => array(),				// list of meta fields (can be added by field arrays)
				'local_images' => false,			// Use local or hosted images (meta box images for add/remove)
				'use_with_theme' => false			// change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			);

			// Register the meta box
			$subCatMeta = new Tax_Meta_Class( $config );

			// Add fields to it : https://raw.githubusercontent.com/bainternet/Tax-Meta-Class/master/class-usage-demo.php
			// $subCatMeta->addText( 
			// 	$prefix . 'text_field_id',
			// 	array( 
			// 		'name'=> __( 'My Text ', 'studiorum-lectio' )
			// 	)
			// );

			// Finish
			$subCatMeta->Finish();

		}/* addMetaToSubmissionCategories() */

	}/* class Studiorum_Lectio_Assignment_Taxonomy() */

	$Studiorum_Lectio_Assignment_Taxonomy = new Studiorum_Lectio_Assignment_Taxonomy();