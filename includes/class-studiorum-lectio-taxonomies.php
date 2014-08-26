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
			add_action( 'init', array( $this, 'init__registerTaxonomy' ), 15 );

			// When a new submission category/assignment is made, we look to see if there's any meta to send emails on schedules etc
			add_action( 'update_option_Studiorum_Lectio_Assignment_Taxonomy_Meta', array( $this, 'update_option__handleSubmissionDeadlines' ), 999, 2 );
			add_action( 'add_option_Studiorum_Lectio_Assignment_Taxonomy_Meta', array( $this, 'add_option__handleSubmissionDeadlines' ), 999, 2 );

			// When a new sub cat/assignment is made a custom event is triggered via wp_schedule_single_event() which we hook into here to send emails
			add_action( 'studiorum_lectio_send_reminder_email', array( $this, 'studiorum_lectio_send_reminder_email__calculateWhoToSendEmailToAndSend' ), 10, 3 );

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
				'add_new_item'		=> __( 'Add New ' . $this->menuName ),
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
		 * When a new submission/term is created we hook in to see if there is a deadline and 
		 * if we're sending reminder emails. If so, we set up a wp_cron job to handle that
		 *
		 * @since 0.1
		 *
		 * @param array $oldValue - The whole option before saving
		 * @param array $newValue - The whole options after saving
		 * @return null
		 */

		public function update_option__handleSubmissionDeadlines( $oldValue, $newValue )
		{

			if( !$oldValue || !$newValue ){
				return;
			}

			// First thing to do is do a diff between old and new to see what has been added
			$added = array_diff_key( $newValue, $oldValue );

			if( !$added || !is_array( $added ) || empty( $added ) ){
				return;
			}

			$this->processSubmissionDeadlineData( $added );

		}/* update_option__handleSubmissionDeadlines() */


		/**
		 * If this is the first submission cat to be added, then add_option is called
		 *
		 * @since 0.1
		 *
		 * @param string $option the option name
		 * @param array $added data added to the $option
		 * @return null
		 */

		public function add_option__handleSubmissionDeadlines( $option, $added )
		{

			$this->processSubmissionDeadlineData( $added );

		}/* add_option__handleSubmissionDeadlines() */


		/**
		 * Helper method to process new submission categories and the meta attached to them
		 * to determine if any scheduled events need to be sent
		 *
		 * @since 0.1
		 *
		 * @param array $added Data that has just been added
		 * @return string|int returnDescription
		 */

		public function processSubmissionDeadlineData( $added )
		{

			// Should be an array where the keys are the term ID that has just been added and the values, an assoc. array
			// of meta field ID => content

			// Test whether this assignment has a deadline and that there is a need for a reminder email
			$subsWithDeadlines = $this->checkForDeadlinesWithEmails( $added );

			if( !$subsWithDeadlines || !is_array( $subsWithDeadlines ) || empty( $subsWithDeadlines ) ){
				return;
			}

			$usableForScheduledEvents = $this->convertToUsableEvents( $subsWithDeadlines );

			if( !$usableForScheduledEvents || !is_array( $usableForScheduledEvents ) || empty( $usableForScheduledEvents ) ){
				return;
			}

			// Array of arrays: array( array( 'emailTimestamp' => 1274123617, 'termID' => 23, 'subject' => '', 'content' => '' ) );
			// Now schedule a single event based on each of these
			foreach( $usableForScheduledEvents as $key => $emailData )
			{
				
				$when 		= $emailData['emailTimestamp'];
				$subject 	= $emailData['subject'];
				$content 	= $emailData['content'];
				$termID 	= $emailData['termID'];

				wp_schedule_single_event( $when, 'studiorum_lectio_send_reminder_email', array( $subject, $content, $termID ) );

			}

		}/* processSubmissionDeadlineData() */


		/**
		 * From a given array of new submission categories, work out which ones have deadlines
		 * that also have an email to be sent
		 *
		 * @since 0.1
		 *
		 * @param array $added Item[s] that have been added
		 * @return array An array of items that have deadlines and an email needs to be sent
		 */

		private function checkForDeadlinesWithEmails( $added = array() )
		{

			// Start fresh
			$deadlinesWithEmails = array();

			foreach( $added as $termID => $termMeta )
			{
				
				// Does this have a deadline?
				if( !isset( $termMeta['assignment_has_deadline'] ) || $termMeta['assignment_has_deadline'] == 'undefined' ){
					continue;
				}

				// Does it require a reminder email
				if( !isset( $termMeta['assignment_send_reminder_email'] ) || $termMeta['assignment_send_reminder_email'] == 'undefined' ){
					continue;
				}

				$deadlinesWithEmails[$termID] = $termMeta;

			}

			return $deadlinesWithEmails;

		}/* checkForDeadlinesWithEmails() */


		/**
		 * We have been passed an array of submission categories that require an automated reminder email
		 * the deadline is a datetime and the 'when to send' is a number of seconds. So we need to convert
		 * the datetime into seconds, minus the number of 'when to send' and then convert that back to a date
		 * so we know when to set the cron
		 *
		 * @since 0.1
		 *
		 * @param array $subsWithDeadlines an array with the keys as term IDs and the values are an assoc. array of meta
		 * @return array An array of details we can use to add scheduled events
		 */

		private function convertToUsableEvents( $subsWithDeadlines = array() )
		{

			// Start fresh
			$usableData = array();

			foreach( $subsWithDeadlines as $termID => $termMeta )
			{
				
				$dateTime = ( isset( $termMeta['assignment_deadline_datetime'] ) ) ? $termMeta['assignment_deadline_datetime'] : false;

				if( !$dateTime ){
					continue;
				}

				// Calculate the time/date we need to send the email
				$deadlineTimestamp = strtotime( $dateTime );
				$numberOfSecondsBeforeDeadlineWeSendEmail = $termMeta['assignment_send_reminder_before_date'];

				$timestampOfWhenWeMustSendEmail = $deadlineTimestamp - $numberOfSecondsBeforeDeadlineWeSendEmail;

				$assigmentReminderEmailSubject = $termMeta['assigment_reminder_email_subject'];
				$assigmentReminderEmailContent = $termMeta['assigment_reminder_email_content'];

				$thisItem = array(
					'emailTimestamp' => $timestampOfWhenWeMustSendEmail,
					'termID' => $termID,
					'subject' => $assigmentReminderEmailSubject,
					'content' => $assigmentReminderEmailContent
				);

				$usableData[] = $thisItem;

			}

			return $usableData;

		}/* convertToUsableEvents() */


		/**
		 * We have a call to wp_schedule_single_event() whenever a new submission category is created that requires a reminder email
		 * which fires a custom event 'studiorum_lectio_send_reminder_email' - this method hooks into that event and calculates
		 * who to send the mail to and then passes that info off to another method to send the email
		 *
		 * @since 0.1
		 *
		 * @param string $subject The subject of the email
		 * @param string $content The content of the email
		 * @param int $termID The term ID that has just been added
		 * @return null
		 */

		public function studiorum_lectio_send_reminder_email__calculateWhoToSendEmailToAndSend( $subject, $content, $termID )
		{

			// Fetch all student users
			$allUsersOnSite = Studiorum_Lectio_Utils::getThisSitesUsers();

			// We'll need just the IDs of people who have not submitted
			$usersIDsWhoHaveNotSubmitted = array();

			// If a student has already submitted, we don't need to send them an email
			$usersWhoHaveAlreadySubmitted = $this->getAuthorsWhoHaveSubmittedPostForSubCat( $termID );

			foreach( $allUsersOnSite as $key => $userObject )
			{
			
				$userID = $userObject->ID;

				if( in_array( $userID, array_values( $usersWhoHaveAlreadySubmitted ) ) ){
					continue;
				}

				if( array_key_exists( $userID, $usersIDsWhoHaveNotSubmitted ) ){
					continue;
				}

				$usersIDsWhoHaveNotSubmitted[] = $userID;

			}

			// Run this through a filter as we'll want to edit this for user groups etc.
			$usersIDsWhoHaveNotSubmitted = apply_filters( 'studiorum_lectio_reminder_email_user_ids_who_have_not_submitted', $usersIDsWhoHaveNotSubmitted, $termID );

			// If we now have some User IDs, we should send them the email
			if( !$usersIDsWhoHaveNotSubmitted || !is_array( $usersIDsWhoHaveNotSubmitted ) || empty( $usersIDsWhoHaveNotSubmitted ) )
			{

				do_action( 'studiorum_lectio_reminder_email_no_users_to_send_reminder_to', $termID );
				return;

			}

			$emailAddresses = array();

			foreach( $usersIDsWhoHaveNotSubmitted as $key => $userID )
			{

				$userObject = get_user_by( 'id', $userID );
				$emailAddress = $userObject->user_email;

				if( !in_array( $emailAddress, array_values( $emailAddresses ) ) ){
					$emailAddresses[] = $emailAddress;
				}

			}

			// Some filters
			$emailAddresses 	= apply_filters( 'studiorum_lectio_reminder_email_email_addresses', $emailAddresses, $termID );
			$subject 			= apply_filters( 'studiorum_lectio_reminder_email_subject', $subject, $termID );
			$content 			= apply_filters( 'studiorum_lectio_reminder_email_content', $content );
			$headers 			= apply_filters( 'studiorum_lectio_reminder_email_headers', array(), $termID );
			$attachments 		= apply_filters( 'studiorum_lectio_reminder_email_attachments', array(), $termID );
			
			$this->sendReminderEmail( $emailAddresses, $subject, $content, $headers, $attachments );

		}/* studiorum_lectio_send_reminder_email__calculateWhoToSendEmailToAndSend() */


		/**
		 * Method to determine which students have submitted a post for a particular assignment.
		 * In essence this looks for post authors of submissions in a particular submissions category
		 *
		 * @since 0.1
		 *
		 * @param (int) $termID The term ID of the submissions category for which we are searching
		 * @return array An array of user IDs who have submitted a post for a particular assignment
		 */

		public function getAuthorsWhoHaveSubmittedPostForSubCat( $termID = false )
		{

			if( !$termID ){
				return array();
			}

			// Start fresh
			$alreadySubmittedAuthors = array();

			$allSubmissions = Studiorum_Lectio_Utils::fetchUsersSubmissions();

			if( !$allSubmissions || !is_array( $allSubmissions ) || empty( $allSubmissions ) ){
				return array();
			}

			foreach( $allSubmissions as $userID => $taxIDPostID )
			{
				
				if( !array_key_exists( $termID, $taxIDPostID ) ){
					continue;
				}

				if( !in_array( $userID, array_values( $alreadySubmittedAuthors ) ) ){
					$alreadySubmittedAuthors[] = $userID;
				}

			}

			return $alreadySubmittedAuthors;

		}/* getAuthorsWhoHaveSubmittedPostForSubCat() */


		/**
		 * Method called by cron to send email as a reminder
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public function sendReminderEmail( $to, $subject, $message, $headers, $attachments )
		{

			wp_mail( $to, $subject, $message, $headers, $attachments );

		}/* sendReminderEmail */

	}/* class Studiorum_Lectio_Assignment_Taxonomy() */

	$Studiorum_Lectio_Assignment_Taxonomy = new Studiorum_Lectio_Assignment_Taxonomy;
