<?php 

	/**
	 * Dashboard additions (not necessarily on the *actual* dashboard) for a student
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/Studebt Dashboard
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Student_Dashboard
	{

		/**
		 * Set up actions and filters
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public static $userSubmissions = false;

		public function __construct()
		{

			// Add student assignment details dashboard widget - add at 10000 as we hook in at 9999 in the main plugin to clear things
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup__addStudentSubmissionsDashboardWidget' ), 10000 );

			// Add the 'Discussions' dashboard widget
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup__addStudentDiscussionsDashboardWidget' ), 10001 );

		}/* __construct() */


		/**
		 * Add the Lectio dashboard widget which shows an overview of submissions and stats
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_dashboard_setup__addStudentSubmissionsDashboardWidget()
		{

			if( !Studiorum_Utils::usersRoleIs( 'studiorum_student' ) ){
				return false;
			}

			// Add the dashboard widget
			wp_add_dashboard_widget( 'studiorum_lectio_student_widget', 'Assignment Details', array( $this, 'dashboard_widget_output__lectionStudentAssignmentDetails' ) );

			// Put our widget at the top of the pile
			// Globalize the metaboxes array, this holds all the widgets for wp-admin
			global $wp_meta_boxes;
			
			// Get the regular dashboard widgets array 
			// (which has our new widget already but at the end)
			$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
			
			// Backup and delete our new dashboard widget from the end of the array
			$example_widget_backup = array( 'studiorum_lectio_student_widget' => $normal_dashboard['studiorum_lectio_student_widget'] );
			unset( $normal_dashboard['studiorum_lectio_student_widget'] );

			// Merge the two arrays together so our widget is at the beginning
			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes 
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;

		}/* wp_dashboard_setup__addStudentSubmissionsDashboardWidget() */


		/**
		 * The output for the Lectio widget
		 *
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function dashboard_widget_output__lectionStudentAssignmentDetails()
		{

			if( !isset( static::$userSubmissions ) || static::$userSubmissions === false )
			{

				// Grab submissions for this user
				$submissions = Studiorum_Lectio_Utils::fetchCurrentUsersSubmissions();
				static::$userSubmissions = $submissions;

			}
			else
			{

				$submissions = static::$userSubmissions;

			}

			// It's an array with the userID as the front key
			$userID = get_current_user_id();
			$mainSubmissions = ( isset( $submissions[$userID] ) ) ? $submissions[$userID] : false;

			if( !$mainSubmissions || empty( $mainSubmissions ) ){
				return false;
			}

			// This is now an array of arrays, with the top-level keys being the term IDs of the submissions category
			// and the inner arrays being a non-associative list of post IDs
			$postData = array();

			foreach( $mainSubmissions as $termID => $postIDs )
			{
				
				$thisTermsData = array();

				$termObject = get_term_by( 'id', $termID, Studiorum_Lectio_Utils::$submissionCategorySlug );
				
				if( !isset( $termObject ) || !is_object( $termObject ) ){
					continue;
				}

				// Get the 'name' of this term, i.e. "Assignment 1"
				$thisTermsData['name'] = $termObject->name;

				// Now get details about each post
				$thisTermsData['posts'] = array();
				
				foreach( $postIDs as $key => $postID )
				{
					
					$grade 		= get_post_meta( $postID, 'studiorum_grade', true );
					$grade 		= ( $grade && $grade != '' ) ? $grade : 'N/A';
					$permalink 	= get_permalink( $postID );
					$excerpt 	= Studiorum_Utils::getExcerptFromPostID( $postID );
					$date 		= get_the_time( 'l jS \of F Y \a\t h:i:s A', $postID );

					$thisTermsData['posts'][] = array( 'postID' => $postID, 'date' => $date, 'permalink' => $permalink, 'excerpt' => $excerpt, 'grade' => $grade );

				}
				
				$postData[] = $thisTermsData;

			}

			if( empty( $postData ) ){
				return;
			}
			
			include( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/admin/templates/student-dashboard-submissions-widget.php' ) );

		}/* dashboard_widget_output__lectionStudentAssignmentDetails() */


		/**
		 * Add the student discussions dashboard widget. This allows students to see what submissions they are able to
		 * view and discuss
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_dashboard_setup__addStudentDiscussionsDashboardWidget()
		{

			if( !Studiorum_Utils::usersRoleIs( 'studiorum_student' ) ){
				return false;
			}

			// Add the dashboard widget
			wp_add_dashboard_widget( 'studiorum_lectio_student_widget_discussions', 'Discussions', array( $this, 'dashboard_widget_output__lectioStudentDiscussions' ) );

			// Put our widget at the top of the pile
			// Globalize the metaboxes array, this holds all the widgets for wp-admin
			global $wp_meta_boxes;
			
			// Get the regular dashboard widgets array 
			// (which has our new widget already but at the end)
			$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
			
			// Backup and delete our new dashboard widget from the end of the array
			$example_widget_backup = array( 'studiorum_lectio_student_widget_discussions' => $normal_dashboard['studiorum_lectio_student_widget_discussions'] );
			unset( $normal_dashboard['studiorum_lectio_student_widget_discussions'] );

			// Merge the two arrays together so our widget is at the beginning
			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes 
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;

		}/* wp_dashboard_setup__addStudentDiscussionsDashboardWidget() */


		/**
		 * Output for the student discussions widget
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function dashboard_widget_output__lectioStudentDiscussions()
		{

			// Total output - by default is just current users, but with filters, can add group, too
			$allViewableSubmissions = array();

			// Current users
			$currentUserSubmissions = false;

			// First, get the current user's submissions.
			if( !isset( static::$userSubmissions ) || static::$userSubmissions === false )
			{

				// Grab submissions for this user
				$currentUserSubmissions = Studiorum_Lectio_Utils::fetchCurrentUsersSubmissions();
				static::$userSubmissions = $currentUserSubmissions;

			}
			else
			{

				$currentUserSubmissions = static::$userSubmissions;
				
			}

			if( $currentUserSubmissions && is_array( $currentUserSubmissions ) && !empty( $currentUserSubmissions ) ){
				$allViewableSubmissions['current_user_submissions'] = array( 'title' => 'Your Submissions', 'data' => $currentUserSubmissions );
			}

			$userID = get_current_user_id();

			$allViewableSubmissions = apply_filters( 'studiorum_lectio_viewable_submissions', $allViewableSubmissions, $userID );
			
			if( !$allViewableSubmissions || !is_array( $allViewableSubmissions ) || empty( $allViewableSubmissions ) ){
				_e( 'You currently have no assignments to view or discuss', 'studiorum-lectio' );
				return;
			}

			$postData = array();

			foreach( $allViewableSubmissions as $type => $viewableSubmissions )
			{
				// echo '<pre>' . print_r( $viewableSubmissions, true ) . '</pre>';
				$title 	= $viewableSubmissions['title'];
				$data 	= $viewableSubmissions['data'];

				if( !$data || !is_array( $data ) || empty( $data ) ){
					continue;
				}

				foreach( $data as $userID => $termIDandPostsIDs )
				{
					
					foreach( $termIDandPostsIDs as $termID => $postIDs )
					{
						
						$thisTermsData = array();

						$termObject = get_term_by( 'id', $termID, Studiorum_Lectio_Utils::$submissionCategorySlug );
						// var_dump( $termObject );
						if( !isset( $termObject ) || !is_object( $termObject ) ){
							continue;
						}

						// Get the 'name' of this term, i.e. "Assignment 1"
						$thisTermsData['name'] = $termObject->name;

						// Now get details about each post
						$thisTermsData['posts'] = array();
						
						foreach( $postIDs as $key => $postID )
						{
							
							$grade 		= get_post_meta( $postID, 'studiorum_grade', true );
							$grade 		= ( $grade && $grade != '' ) ? $grade : 'N/A';
							$permalink 	= get_permalink( $postID );
							$excerpt 	= Studiorum_Utils::getExcerptFromPostID( $postID );
							$date 		= get_the_time( 'l jS \of F Y \a\t h:i:s A', $postID );

							$thisTermsData['posts'][] = array( 'postID' => $postID, 'date' => $date, 'permalink' => $permalink, 'excerpt' => $excerpt, 'grade' => $grade );

						}
						
						$postData[$type][] = $thisTermsData;

					}

				}

			}

			if( empty( $postData ) ){
				return;
			}
			
			$text = '';

			foreach( $postData as $type => $data ) {

				switch( $type )
				{


					case 'group_submissions':
						$text = __( 'Here are submissions made by your group', 'studiorum-lectio' );
						break;

					case 'current_user_submissions':
					default:
						
						$text = __( 'Here are your submissions', 'studiorum-lectio' );

						break;

				}

				include( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/admin/templates/student-dashboard-discussions-widget.php' ) );
			}



		}/* dashboard_widget_output__lectioStudentDiscussions() */

	}/* class Studiorum_Lectio_Student_Dashboard */

	$Studiorum_Lectio_Student_Dashboard = new Studiorum_Lectio_Student_Dashboard;