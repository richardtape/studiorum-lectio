<?php

	/**
	 * Assignment Taxonomy Meta
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/Taxonomies/Meta
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	if( !class_exists( 'AdminPageFramework' ) ){
		require_once STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/library/admin-page-framework.min.php';
	}

	class Studiorum_Lectio_Assignment_Taxonomy_Meta extends AdminPageFramework_TaxonomyField
	{

		/**
		 * Ensure we're all set up and then run actions and filters
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function start_Studiorum_Lectio_Assignment_Taxonomy_Meta()
		{

			// Load 3rd party extensions
			$this->includeThirdPartyExtensions();

			// Set up our taxonomy fields
			add_action( 'studiorum_lectio_taxonomy_meta_setup', array( $this, 'addLectioSettingFields' ) );

		}/* Studiorum_Lectio_Assignment_Taxonomy_Meta() */


		/**
		 * Run automatically by APF. Set up a custom action so we can hook in and add fields
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		function setUp()
		{

			do_action( 'studiorum_lectio_taxonomy_meta_setup' );

		}/* setUp() */


		/**
		 * Add our settings fields to our taxonomy
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function addLectioSettingFields()
		{

			// Add the fields for the max number of submissions
			$this->addMaxSubmissionsFields();

			// Also add fields for deadline of submissions (if WP_CRON is enabled)
			// @todo: Fix this as it causes a JS error on defaul tags and category pages
			// $this->addDeadlineFields();

		}/* addLectioSettingFields() */

		/**
		 * Add the settings field to allow people to add a maximum number of submissions per user
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function addMaxSubmissionsFields()
		{

			// Add the max number of submissions settings fields
			$this->addSettingFields(

				array(
					'field_id'		=> 'assignment_max_submissions',
					'type'			=> 'text',
					'title'			=> __( 'Maximum # Of Submissions', 'studiorum-lectio' ),
					'default'		=> __( '1', 'studiorum-lectio' ),
					'attributes'	=>	array(
						'size'		=>	48,
					),
					'description' 	=> __( 'How many submissions should each student (or group of students) be allowed to make for this assignment? After a student has submitted this number of times, they will be unable to submit further.', 'studiorum-lectio' )
				)

			);

		}/* addMaxSubmissionsFields() */

		/**
		 * Add fields for deadline reminders. Only added if cron is enabled as we need to add
		 * tasks to WP_CRON for the reminders
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function addDeadlineFields()
		{

			// If we have WP_CRON enabled, add the deadline reminder fields
			if( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ){
				return;
			}

			// Reminder times/dates MUST be number of seconds
			$emailReminderTimes = array( 
				DAY_IN_SECONDS 			=> __( 'One Day Before', 'studiorum-lectio' ),
				( DAY_IN_SECONDS * 2 )	=> __( 'Two Days Before', 'studiorum-lectio' ),
				WEEK_IN_SECONDS 		=> __( 'One Week Before', 'studiorum-lectio' ),
			);
			
			$emailReminderTimes = apply_filters( 'studiorum_lectio_reminder_email_times_list', $emailReminderTimes );

			$this->addSettingFields(

				array(
					'field_id'		=> 'assignment_has_deadline',
					'type'			=> 'revealer',
					'title'			=> __( 'Assignment Deadline', 'studiorum-lectio' ),
					'value'			=> 'undefined',
					'label'			=> array(
						'undefined' => __( 'No Deadline', 'studiorum-lectio' ),
						'#fields-assignment_deadline_datetime,#fields-assignment_send_reminder_email' => __( 'Has Deadline', 'studiorum-lectio' )
					),
					'description' 	=> __( 'Does this assignment have a deadline? If so, would you like to send a reminder email to students before that date?', 'studiorum-lectio' )
				),	
				array(
					'field_id'		=> 'assignment_deadline_datetime',
					'type'			=> 'date_time',
					'title'			=> __( 'Deadline Time and Date', 'studiorum-lectio' ),
					'hidden'		=> true,
				),
				array(
					'field_id'		=> 'assignment_send_reminder_email',
					'type'			=> 'revealer',
					'title'			=> __( 'Would you like to send a reminder email to those who have not submitted?', 'studiorum-lectio' ),
					'value' 		=> 'undefined',
					'label'			=> array(
						'undefined' => __( 'No reminder email', 'studiorum-lectio' ),
						'#fields-assignment_send_reminder_before_date,#fields-assigment_reminder_email_content,#fields-assigment_reminder_email_subject' => __( 'Yes, send a reminder email', 'studiorum-lectio' )
					),
					'hidden'		=> true,
				),
				array(
					'field_id'		=> 'assignment_send_reminder_before_date',
					'type'			=> 'select',
					'title'			=> __( 'When to send the reminder', 'studiorum-lectio' ),
					'description'	=> __( 'How long prior to the deadline do you want to send the reminder email?', 'studiorum-lectio' ),
					'label' 		=> $emailReminderTimes,
					'default' 		=> DAY_IN_SECONDS,
					'hidden'		=> true,
				),	
				array(
					'field_id'		=> 'assigment_reminder_email_subject',
					'type'			=> 'text',
					'title'			=> __( 'Email Subject', 'studiorum-lectio' ),
					'default'		=> __( 'An Assignment Deadline is approaching', 'studiorum-lectio' ),
					'attributes'	=>	array(
						'size'		=>	48,
					),
					'hidden'		=> true,
				),
				array(
					'field_id' 		=> 'assigment_reminder_email_content',
					'type' 			=> 'textarea',
					'title' 		=> __( 'Reminder Email Message', 'studiorum-lectio' ),
					'hidden'		=> true,
				)

				// Wysiwyg not saving in taxonomy meta yet
				// array(	// Rich Text Editor
				// 	'field_id' 		=> 'assigment_reminder_email_content',
				// 	'type' 			=> 'textarea',
				// 	'title' 		=> __( 'Reminder Email Message', 'studiorum-lectio' ),
				// 	'rich' 			=> array(
				// 		'media_buttons'	=> false,
				// 		'teeny' 		=> true,
				// 		'quicktags' 	=> false
				// 	),
				// 	'hidden'		=> true,
				// )
			);

		}/* addDeadlineFields() */


		/**
		 * Include the 3rd party settings extensions and instantiate their classes
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function includeThirdPartyExtensions()
		{

			// 1. Include the file that defines the custom field type.
			$thirdPartyFiles = array(
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/geometry-custom-field-type/GeometryCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/date-time-custom-field-types/DateCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/date-time-custom-field-types/TimeCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/date-time-custom-field-types/DateTimeCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/dial-custom-field-type/DialCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/font-custom-field-type/FontCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/revealer-custom-field-type/RevealerCustomFieldType.php',
				STUDIORUM_PLUGIN_DIR . 'includes/admin/libraries/settings/third-party/autocomplete-custom-field-type/AutocompleteCustomFieldType.php'
			);

			foreach( $thirdPartyFiles as $sFilePath )
			{

				if( file_exists( $sFilePath ) ){
					include_once( $sFilePath );
				}

			}

			// 2. Instantiate the classes
			$sClassName = get_class( $this );
			new GeometryCustomFieldType( $sClassName );
			new DateCustomFieldType( $sClassName );
			new TimeCustomFieldType( $sClassName );
			new DateTimeCustomFieldType( $sClassName );
			new DialCustomFieldType( $sClassName );
			new FontCustomFieldType( $sClassName );
			new RevealerCustomFieldType( $sClassName );
			new AutocompleteCustomFieldType( $sClassName );

		}/* includeThirdPartyExtensions() */

	}/* class Studiorum_Lectio_Assignment_Taxonomy_Meta */

	$Studiorum_Lectio_Assignment_Taxonomy_Meta = new Studiorum_Lectio_Assignment_Taxonomy_Meta( 'lectio-submission-category' );