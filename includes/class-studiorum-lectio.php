<?php 

	/**
	 * An add-on for Studiorum
	 *
	 * @package     Lectio
	 * @subpackage  
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio extends Studiorum_Addon
	{


		/**
		 * Actions and filters
		 *
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function __construct()
		{

			// Load our necessary post types and taxonomies
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme__includes' ), 1 );

			// We have a filter for the WYSIWYG add-on for gForms allowing us to modify the type of editor
			add_filter( 'gforms_wysiwyg_wp_editor_args', array( $this, 'gforms_wysiwyg_wp_editor_args__adjustWPEditor' ), 10, 2 );

		}/* __construct() */


		/**
		 * Load our includes
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public static function after_setup_theme__includes()
		{

			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-post-type.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-taxonomies.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/libraries/Tax-meta-class/Tax-meta-class.php' );

		}/* after_setup_theme__includes() */

		/**
		 * Adjust the wp_editor() call in the gForms WYSIWYG add-on. We require the media buttons etc.
		 *
		 * @since 0.1
		 *
		 * @param (array) $args The arguments passed into wp_editor()
		 * @param (array) $field The entire wysiwyg field
		 * @return (array) $args The modified arguments passed into wp_editor()
		 */

		public function gforms_wysiwyg_wp_editor_args__adjustWPEditor( $args, $field )
		{

			$args['quicktags'] 			= false;
			$args['textarea_rows'] 		= 15;
			$args['drag_drop_upload'] 	= true;
			$args['media_buttons'] 		= true;

			return $args;

		}/* gforms_wysiwyg_wp_editor_args__adjustWPEditor() */


	}/* class Studiorum_Lectio */

	// Initialize ourselves
	if( class_exists( 'Studiorum_Lectio' ) )
	{

		$studiorumLectio = new Studiorum_Lectio();

		$GLOBALS['studiorum_addons'][] = $studiorumLectio;

	}