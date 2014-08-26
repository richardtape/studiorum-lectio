<?php 

	/**
	 * The settings for Lectio
	 *
	 * @package     Lectio/Settings
	 * @subpackage  
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Settings
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

			// Add our options to the options panel
			add_action( 'studiorum_settings_setup_start', array( $this, 'studiorum_settings_setup_start__addFilters' ) );

		}/* __construct() */


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
				'type'			=>	'select',
				'title'			=>	__( 'Posts/Pages Containing Forms', 'studiorum-lectio' ) . '<span class="label-note">' . __( 'Which post(s) or page(s) have you placed a submission form?', 'studiorum-lectio' ) . '</span>',
				// 'description' 	=> __( '<img src="http://www.dummyimage.com/400x200" />', 'studiorum-lectio' ),
				'help'			=>	__( 'Currently we are unable to automatically detect which gravity form(s) you wish to use for submissions - and hence which page you have those on. So, please let us know which page(s) you use for submissions from your students.', 'studiorum-lectio' ),
				'help_aside'	=>	__( '', 'studiorum-lectio' ),
				'default'		=>	0,	// the index key of the label array below which yields 'Yellow'.
				'repeatable' 	=> true,
				'label'			=>	$dropdownValues
			);

			/*$settingsFields[] = array(
				'field_id'		=>	'posts_containing_forms_test',
				'section_id'	=>	'lectio_options',
				'type'			=>	'select',
				'title'			=>	__( 'Posts Containing Forms', 'studiorum-lectio' ) . '<span class="label-note">' . __( 'Which post(s) or page(s) have you placed a submission form?', 'studiorum-lectio' ) . '</span>',
				'description' 	=> __( 'This is a whatever field and typed text will be saved. This text is inserted with the <code>help</code> key in the field definition array.', 'studiorum-lectio' ),
				'help'			=>	__( 'Currently we are unable to automatically detect which gravity form(s) you wish to use for submissions - and hence which page you have those on. So, please let us know which page(s) you use for submissions from your students.', 'studiorum-lectio' ),
				'help_aside'	=>	__( '', 'studiorum-lectio' ),
				'default'		=>	0,	// the index key of the label array below which yields 'Yellow'.
				'label'			=>	$dropdownValues
			);

			$settingsFields[] = array(	// Single text field
				'field_id'	=>	'text_test',
				'section_id'	=>	'lectio_options',
				'title'	=>	__( 'Text', 'studiorum' ),
				'description'	=>	__( 'Type something here. This text is inserted with the <code>description</code> key in the field definition array.', 'studiorum' ),
				'help'	=>	__( 'This is a text field and typed text will be saved. This text is inserted with the <code>help</code> key in the field definition array.', 'studiorum' ),
				'type'	=>	'text',
				'default'	=>	123456,
				'attributes'	=>	array(
					'size'	=>	40,
				),
			);*/

			return $settingsFields;

		}/* studiorum_settings_settings_fields__addLectioSettingsFields */

	}/* class Studiorum_Lectio_Settings */

	$studiorumLectioSettings = new Studiorum_Lectio_Settings();
