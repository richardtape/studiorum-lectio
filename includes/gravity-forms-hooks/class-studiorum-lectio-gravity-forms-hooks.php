<?php 

	/**
	 * Gravity Forms interactions
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/Gravity Forms Hooks
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Gravity_Forms_Hooks
	{

		var $hasDoneAJAX = false;

		var $hasUserSubmittedAssignment = false;

		/**
		 * Set up actions and filters
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function __construct()
		{

			// When someone submits a course form, we redirect them to the post just created
			add_filter( 'gform_confirmation', array( $this, 'gform_confirmation__redirectToPostJustCreated' ), 20, 4 );

			add_action( 'gform_after_submission', array( $this, 'gform_after_submission__redirectForNonAJAXSubmissions' ), 20, 2 );

			// We have a filter for the WYSIWYG add-on for gForms allowing us to modify the type of editor
			add_filter( 'gforms_wysiwyg_wp_editor_args', array( $this, 'gforms_wysiwyg_wp_editor_args__adjustWPEditor' ), 10, 2 );

			// Add a 'private' option to the gForms post fields so only authors of the post and educators & above can view the post
			add_filter( 'gform_post_status_options', array( $this, 'gform_post_status_options__addPrivateToDropdown' ) );

			// Add a message to the user if they have already submitted for this assignment
			add_filter( 'gform_pre_render', array( $this, 'gform_pre_render__hideFormIfAlreadySubmitted' ), 10, 2 );
			add_filter( 'gform_form_tag', array( $this, 'gform_form_tag__showMessageIfAlreadySubmitted' ), 10, 2 );

		}/* __construct() */

		/**
		 * Redirect the student to the post that has just been created after submission to a course form
		 *
		 * @since 0.1
		 *
		 * @param string|array $confirmation 
		 * @param object $form The current form
		 * @param array $lead Current entry array
		 * @param bool $ajax Specifies if this form is configured to be submitted via AJAX
		 * @return null
		 */

		public function gform_confirmation__redirectToPostJustCreated( $confirmation, $form, $lead, $ajax )
		{

			// Get the post ID if it's set
			$postID = isset( $lead['post_id'] ) ? intval( $lead['post_id'] ) : false;

			if( !$postID ){
				return $confirmation;
			}

			// Check that this is a lectio submission post
			$postType = get_post_type( $postID );

			if( $postType !== Studiorum_Lectio_Utils::$postTypeSlug ){
				return $confirmation;
			}

			// Get the permalink for this post so we can redirect to it
			$permalink = get_permalink( $postID );

			// Check whether we are using AJAX submission or not.
			if( !$ajax )
			{

				// For non-AJAX we just return so we can hook in elsewhere
				// wp_redirect( $permalink );
				// exit;
				return $confirmation;

			}
			
			// We set this so when we hook into 'gform_post_submission' later, we'll know not to exit;
			$this->hasDoneAJAX = true;

			$confirmation = array( 'redirect' => $permalink );
			return $confirmation;

		}/* gform_confirmation__redirectToPostJustCreated() */


		/**
		 * If we're on a Non-AJAX form we can just do a hard redirect
		 *
		 * @since 0.1
		 *
		 * @param object $entry The entry that was just created
		 * @param object $form The current form
		 * @return null
		 */

		public function gform_after_submission__redirectForNonAJAXSubmissions( $entry, $form )
		{

			// Get the post ID if it's set
			$postID = isset( $entry['post_id'] ) ? intval( $entry['post_id'] ) : false;

			if( !$postID ){
				return;
			}

			// Check that this is a lectio submission post
			$postType = get_post_type( $postID );

			if( $postType !== Studiorum_Lectio_Utils::$postTypeSlug ){
				return;
			}

			// Get the permalink for this post so we can redirect to it
			$permalink = get_permalink( $postID );

			// Check if we're doing an AJAX redirect, if we are, just go with the flow
			if( $this->hasDoneAJAX ){
				return;
			}
			
			wp_redirect( $permalink );
			exit;

		}/* gform_after_submission__redirectForNonAJAXSubmissions() */


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
			$args['textarea_rows'] 		= 25;
			$args['drag_drop_upload'] 	= true;
			$args['media_buttons'] 		= true;
			$args['dfw'] 				= true;

			return $args;

		}/* gforms_wysiwyg_wp_editor_args__adjustWPEditor() */


		/**
		 * Add a private option to the gForm dropdown list of post statuses
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public function gform_post_status_options__addPrivateToDropdown( $postStatuses )
		{

			$postStatuses['private'] = 'Private';

			return $postStatuses;

		}/* gform_post_status_options__addPrivateToDropdown() */


		/**
		 * If a user has already submitted this form, we show a message telling them that is the case
		 *
		 * @since 0.1
		 *
		 * @param object $form The form object
		 * @param bool $ajax The string containing the <form> tag
		 * @return object $form The form
		 */

		public function gform_pre_render__hideFormIfAlreadySubmitted( $form, $ajax )
		{

			// First check we're on a page with a valid gForm (set in options)
			if( !Studiorum_Lectio_Utils::isAssignmentEntryPage() || !is_user_logged_in() ){
				return $form;
			}

			// OK, we're on a page which contains a gForm for assignment submissions. Let's determine if the current user already has a submission
			$currentUserID 				= get_current_user_ID();
			$userIDToFetchSubmissions 	= apply_filters( 'studiorum_lectio_already_submitted_user_submissions_id', $currentUserID, $form );

			// See which submission category this is for
			$formFields = $form['fields'];

			$submissionCatTermID = Studiorum_Lectio_Utils::getTermIDFromFormFields( $formFields );

			if( !$submissionCatTermID ){
				return $form;
			}

			if( !is_array( $this->hasUserSubmittedAssignment ) ){

				$hasUserSubmittedAssignment = Studiorum_Lectio_Utils::hasUserSubmittedAssignment( $userIDToFetchSubmissions, $submissionCatTermID );

				$this->hasUserSubmittedAssignment = $hasUserSubmittedAssignment;

			}

			if( $this->hasUserSubmittedAssignment )
			{

				// So, the user has already hit the max number of submissions, empty the form
				$form['fields'] = array();

				// We also want to remove the submit button, too.
				$formID = $form['id'];
				add_filter( 'gform_submit_button_' . $formID, '__return_empty_string' );

			}

			return $form;

		}/* gform_pre_render__hideFormIfAlreadySubmitted() */


		/**
		 * If this user has already submitted, we show them a message
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public function gform_form_tag__showMessageIfAlreadySubmitted( $form_tag, $form )
		{

			if( !$this->hasUserSubmittedAssignment || !is_array( $this->hasUserSubmittedAssignment ) || empty( $this->hasUserSubmittedAssignment ) ){
				return $form_tag;
			}

			// Let's grab some basic info about each submission so we can show some details 
			$submittedAssignmentDetails = array();

			foreach( $this->hasUserSubmittedAssignment as $key => $postID )
			{

				$postObject 	= get_post( $postID );

				$permalink 		= get_permalink( $postObject->ID );
				$excerpt 		= Studiorum_Utils::getExcerptFromPostID( $postID );
				$title 			= get_the_title( $postID );
				$submittedOn 	= get_the_time( 'F j, Y g:i a', $postID );
				$author 		= get_the_author_meta( 'user_nicename', $postObject->post_author );

				$submittedAssignmentDetails[] = array(
					'ID' 			=> $postID,
					'permalink' 	=> $permalink,
					'excerpt' 		=> $excerpt,
					'title' 		=> $title,
					'date' 			=> $submittedOn,
					'author' 		=> $author
				);

			}

			$numOfSubmissions = count( $submittedAssignmentDetails );

			$submissionText = ( $numOfSubmissions == 1 ) ? __( 'submission', 'studiorum-lectio' ) : __( 'submissions', 'studiorum-lectio' );

			$maxSubmissionsReachedTemplate = apply_filters( 'studiorum_lectio_max_submissions_reached_template_path', Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/templates/max-submissions-reached.php' ), $form_tag, $form, $submittedAssignmentDetails );
			
			if( !empty( $maxSubmissionsReachedTemplate ) ){
				include( $maxSubmissionsReachedTemplate );
			}

			return $form_tag;

		}/* gform_form_tag__showMessageIfAlreadySubmitted() */

	}/* class Studiorum_Lectio_Gravity_Forms_Hooks */

	$Studiorum_Lectio_Gravity_Forms_Hooks = new Studiorum_Lectio_Gravity_Forms_Hooks;