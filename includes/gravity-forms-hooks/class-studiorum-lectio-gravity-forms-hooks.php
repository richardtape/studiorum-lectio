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

	}/* class Studiorum_Lectio_Gravity_Forms_Hooks */

	$Studiorum_Lectio_Gravity_Forms_Hooks = new Studiorum_Lectio_Gravity_Forms_Hooks;