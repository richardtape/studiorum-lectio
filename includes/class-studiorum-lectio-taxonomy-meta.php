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

		public function start_Studiorum_Lectio_Assignment_Taxonomy_Meta()
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

		}/* Studiorum_Lectio_Assignment_Taxonomy_Meta() */

		function setUp()
		{

			$this->addSettingFields(

				array(
					'field_id'		=> 'assignment_has_deadline',
					'type'			=> 'revealer',
					'title'			=> __( 'Assignment Deadline', 'studiorum-lectio' ),
					'value'			=> 'undefined',
					'label'			=> array(
						'undefined' => __( 'No Deadline', 'studiorum-lectio' ),
						'#fields-assignment_deadline_datetime,#fields-assignment_send_reminder_email' => __( 'Has Deadline', 'studiorum-lectio' )
					)
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
					'label' => array( 
						DAY_IN_SECONDS 			=> __( 'One Day Before', 'studiorum-lectio' ),
						( DAY_IN_SECONDS * 2 )	=> __( 'Two Days Before', 'studiorum-lectio' ),
						WEEK_IN_SECONDS 		=> __( 'One Week Before', 'studiorum-lectio' ),
					),
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

		}/* setUp() */

	}/* class Studiorum_Lectio_Assignment_Taxonomy_Meta */


	add_action( 'set_current_user', 'set_current_user__registerTaxMeta', 7 );

	function set_current_user__registerTaxMeta()
	{

		$Studiorum_Lectio_Assignment_Taxonomy_Meta = new Studiorum_Lectio_Assignment_Taxonomy_Meta( 'lectio-submission-category' );

	}/* set_current_user__registerTaxMeta() */