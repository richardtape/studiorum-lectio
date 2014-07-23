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

			// We need for students to be able to edit the page on which the upload form is on - so they can add media to the WYSIWYG
			add_filter( 'user_has_cap', array( $this, 'user_has_cap__giveStudentAbilityToEditFormPage' ), 100, 3 );

			add_filter( 'user_has_cap', array( $this, 'user_has_cap__alterSubmissionsVisibility' ), 100, 3 );

			// Remove 'private' and 'protected' from titles
			add_filter( 'private_title_format', array( $this, 'title_format__removePrivatePublicFromTitle' ) );
			add_filter( 'protected_title_format', array( $this, 'title_format__removePrivatePublicFromTitle' ) );

			// Prevent Edit/New links in admin bar for student
			add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render__removeAdminBarLinksForStudents' ) );

			// Add our options to the options panel
			add_action( 'studiorum_settings_setup_start', array( $this, 'studiorum_settings_setup_start__addFilters' ) );

			// Add the editor styles, hopefully making it easier for a user to create content as it will be produced
			add_action( 'init', array( $this, 'init__addEditorStyles' ), 999 );

			// Add some inline styles
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts__inlineStyles' ) );

			// Some extra styles for warning messages etc.
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts__frontEndStyles' ) );

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

			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-utils.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-post-type.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-taxonomy-meta.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio-taxonomies.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/admin/class-studiorum-lectio-educator-dashboard.php' );
			require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/gravity-forms-hooks/class-studiorum-lectio-gravity-forms-hooks.php' );

		}/* after_setup_theme__includes() */


		/**
		 * As we want students to be able to upload images when they are using the WYSIWYG, we need to ensure they have the capbility
		 * to 'edit' the page on which the form is located. (i.e. when they use the media uploader, it attaches the media to that page,
		 * so they basically need the ability to edit it - from the front end. We also need to stop them from being able to edit the
		 * actual content of the page itself)
		 *
		 * @since 0.1
		 *
		 * @param array $capauser All the capabilities of the user
		 * @param array $capask   [0] Required capability
		 * @param array $param    [0] Requested capability
		 *                        [1] User ID
		 *                        [2] Associated object ID
		 * @return array $capauser All the capabilities of the user
		 */

		public function user_has_cap__giveStudentAbilityToEditFormPage( $capauser, $capask, $param )
		{

			// global $wpdb;

			// Ensure we're talking about post/page capabilities and that we have a post/page
			if( !isset( $param[2] ) ){
				return $capauser;
			}

			// This only applies to students
			if( !isset( $capauser['studiorum_student'] ) || ( isset( $capauser['studiorum_student'] ) && $capauser['studiorum_student'] != 1 ) ){
				return $capauser;
			}

			// Which posts/pages is the user able to edit
			// Fetch from the option and run through a filter
			$selectedPages = get_studiorum_option( 'lectio_options', 'posts_containing_forms' );
			$allowedPostIDsForCurrentUser = apply_filters( 'studiorum_lectio_post_ids_to_allow_students_to_upload_media', array_values( $selectedPages ), $capauser, $capask, $param );

			// Grab the current post/page
			$post = get_post( intval( $param[2] ) );

			if( !$post || !isset( $post->ID ) ){
				return $capauser;
			}

			// Is this post ID in the allowed array? If not, bail
			if( !in_array( $post->ID,  $allowedPostIDsForCurrentUser ) ){
				return $capauser;
			}

			// Add the capability temporarily to this user to be able to edit this page
			$capauser['edit_others_posts'] 		= 1;
			$capauser['edit_published_pages'] 	= 1;
			$capauser['edit_published_posts'] 	= 1;

			return $capauser;

		}/* user_has_cap__giveStudentAbilityToEditFormPage() */


		/**
		 * When a submission is made, the posts are private. By default this means only the author and admins can see the posts
		 * We run this method to ensure that we can modify this behaviour on a post-by-post basis from elsewhere
		 *
		 * @since 0.1
		 *
		 * @param array $capauser All the capabilities of the user
		 * @param array $capask   [0] Required capability
		 * @param array $param    [0] Requested capability
		 *                        [1] User ID
		 *                        [2] Associated object ID
		 * @return array $capauser All the capabilities of the user
		 */

		public function user_has_cap__alterSubmissionsVisibility( $capauser, $capask, $param )
		{

			// Ensure we're talking about post/page capabilities and that we have a post/page
			if( !isset( $param[2] ) ){
				return $capauser;
			}

			// This only applies to students
			if( !isset( $capauser['studiorum_student'] ) || ( isset( $capauser['studiorum_student'] ) && $capauser['studiorum_student'] != 1 ) ){
				return $capauser;
			}
			// Grab the post/page
			$post = get_post( intval( $param[2] ) );

			// Ensure this is a submission
			if( !$post || !isset( $post->post_type ) || ( isset( $post->post_type ) && $post->post_type != 'lectio-submission' ) ){
				return $capauser;
			}

			$currentUserID = get_current_user_id();

			// The post author should be able to see this by default
			if( !$post->post_author || $currentUserID == $post->post_author ){
				return $capauser;
			}

			// Specific user IDs that are able to see this post
			$specificUsersAbleToSeeThisPost = apply_filters( 'studiorum_lectio_specific_users_who_can_see_private_submissions', array(), $currentUserID, $post );


			// As these are private posts by default, we need to ensure that the users are able to view them and this post specifically
			if( !in_array( $currentUserID, $specificUsersAbleToSeeThisPost ) ){
				return $capauser;
			}

			$capauser['read_private_posts'] = 1;
			$capauser['read_post'] = 1;

			return $capauser;

		}/* user_has_cap__alterSubmissionsVisibility() */


		/**
		 * Remove the words 'Private' and 'Protected' from titles
		 *
		 * @since 0.1
		 *
		 * @param string $content the title content
		 * @return string The modified title
		 */

		public function title_format__removePrivatePublicFromTitle( $content )
		{

			return '%s';

		}/* title_format__removePrivatePublicFromTitle() */


		/**
		 * Students don't need to see links such as edit or add items in the admin bar
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_before_admin_bar_render__removeAdminBarLinksForStudents()
		{

			global $wp_admin_bar;

			$userID = get_current_user_id();

			$isStudent = Studiorum_Utils::usersRoleIs( 'studiorum_student' );

			if( !$isStudent ){
				return;
			}

			$wp_admin_bar->remove_menu( 'comments' );
			$wp_admin_bar->remove_menu('new-content');
			$wp_admin_bar->remove_menu('edit');

		}/* wp_before_admin_bar_render__removeAdminBarLinksForStudents() */


		/**
		 * Add our filters to add our options to the main studiorum settings panel
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function studiorum_settings_setup_start__addFilters()
		{

			// Add the tab
			add_filter( 'studiorum_settings_in_page_tabs', array( $this, 'studiorum_settings_in_page_tabs__addLectioSettingsTab' ) );

			// Add the settings section
			add_filter( 'studiorum_settings_settings_sections', array( $this, 'studiorum_settings_settings_sections__addLectioSettingsSection' ) );

			// Add the fields to the new section
			add_filter( 'studiorum_settings_settings_fields', array( $this, 'studiorum_settings_settings_fields__addLectioSettingsFields' ) );

		}/* studiorum_settings_setup_start__addFilters() */


		/**
		 * Add the lectio tab to the Studiorum settings panel
		 *
		 * @since 0.1
		 *
		 * @param array $studiorumSettingsTabs Existing settings tabs
		 * @return array $studiorumSettingsTabs Modified settings tabs
		 */

		public function studiorum_settings_in_page_tabs__addLectioSettingsTab( $studiorumSettingsTabs )
		{

			if( !$studiorumSettingsTabs || !is_array( $studiorumSettingsTabs ) ){
				$studiorumSettingsTabs = array();
			}

			$studiorumSettingsTabs[] = array(
				'tab_slug'	=>	'lectio',
				'title'		=>	__( 'Lectio', 'studiorum-lectio' )
			);

			return $studiorumSettingsTabs;

		}/* studiorum_settings_in_page_tabs__addLectioSettingsTab */


		/**
		 * Add the lectio settings section to the Studiorum settings panel
		 *
		 * @since 0.1
		 *
		 * @param array $settingsSections existing settings sections
		 * @return array $settingsSections modified settings sections
		 */

		public function studiorum_settings_settings_sections__addLectioSettingsSection( $settingsSections )
		{

			if( !$settingsSections || !is_array( $settingsSections ) ){
				$settingsSections = array();
			}

			$settingsSections[] = array(
				'section_id'	=>	'lectio_options',
				'tab_slug'		=>	'lectio',
				'order'			=> 	1,
				'title'			=>	__( 'Lectio Settings', 'studiorum-lectio' ),
				'description'	=>	__( 'Lectio adds the ability for students to submit content through your WordPress-powered website. These settings define the basic functionality for Lectio.', 'studiorum-lectio' ),
			);

			return $settingsSections;

		}/* studiorum_settings_settings_sections__addLectioSettingsSection */


		/**
		 * Add the fields to the Studiorum settings panel
		 *
		 * @since 0.1
		 *
		 * @param array $settingsFields existing settings fields
		 * @return array $settingsFields modified settings fields
		 */

		public function studiorum_settings_settings_fields__addLectioSettingsFields( $settingsFields )
		{

			if( !$settingsFields || !is_array( $settingsFields ) ){
				$settingsFields = array();
			}

			$dropdownValues = Studiorum_Lectio_Utils::getAllPostTypesIDsAndTitles();

			$settingsFields[] = array(	// Single Drop-down List
				'field_id'		=>	'posts_containing_forms',
				'section_id'	=>	'lectio_options',
				'title'			=>	__( 'Posts Containing Forms', 'studiorum-lectio' ),
				'type'			=>	'select',
				// 'help'			=>	__( 'This is the <em>select</em> field type.', 'studiorum-lectio' ),
				'default'		=>	0,	// the index key of the label array below which yields 'Yellow'.
				'repeatable' 	=> true,
				'label'			=>	$dropdownValues,
				'description'	=>	__( 'We need to know one which posts or pages you have placed a submission form.', 'studiorum-lectio' )
			);

			return $settingsFields;

		}/* studiorum_settings_settings_fields__addLectioSettingsFields */


		/**
		 * Add editor styles so it's easier for students to create content that appears as it will on the front-end.
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function init__addEditorStyles()
		{

			$stylesheet = trailingslashit( LECTIO_PLUGIN_URL ) . 'includes/assets/css/front-end-editor-style.css';

			// add_editor_style only works in the admin. *sigh*. So, let's try and manipulate the globa $editor_styles array
			global $editor_styles;
			$editor_styles = (array) $editor_styles;
			$stylesheet    = (array) $stylesheet;
			$editor_styles = array_merge( $editor_styles, $stylesheet );

			// Also add this for the admin
			add_editor_style( $stylesheet );

		}/* init__addEditorStyles() */


		/**
		 * Inline styles (mostly to remove some items from the DFW window)
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_enqueue_scripts__inlineStyles()
		{

			$removeUpdateButton = '#wp-fullscreen-save input{ display: none !important; }';

			wp_add_inline_style( 'editor-buttons', $removeUpdateButton );
			wp_add_inline_style( 'editor-buttons-css', $removeUpdateButton );

		}/* wp_enqueue_scripts__inlineStyles() */


		/**
		 * Enqueue some front end styles
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_enqueue_scripts__frontEndStyles()
		{

			// First check we're on a page with a valid gForm (set in options)
			if( !Studiorum_Lectio_Utils::isAssignmentEntryPage() ){
				return false;
			}

			wp_enqueue_style( 'studiorum-lectio-front-end-styles', trailingslashit( LECTIO_PLUGIN_URL ) . 'includes/assets/css/studiorum-lectio-front-end-styles.css' );

		}/* wp_enqueue_scripts__frontEndStyles() */

	}/* class Studiorum_Lectio */

	// Initialize ourselves
	if( class_exists( 'Studiorum_Lectio' ) )
	{

		$studiorumLectio = new Studiorum_Lectio();

		$GLOBALS['studiorum_addons'][] = $studiorumLectio;

	}