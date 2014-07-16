<?php 

	/**
	 * Dashboard additions (not necessarily on the *actual* dashboard) for an educator
	 *
	 * @package     Lectio
	 * @subpackage  Studiorum/Lectio/Educator Dashboard
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Educator_Dashboard
	{

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

			// Add dashboard widget
			add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup__addDashboardWidget' ) );

		}/* __construct() */


		/**
		 * Add the Lectio dashboard widget which shows an overview of submissions and stats
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return null
		 */

		public function wp_dashboard_setup__addDashboardWidget()
		{

			// Add the dashboard widget
			wp_add_dashboard_widget( 'studiorum_lectio_educator_widget', 'Submissions Details', array( $this, 'dashboard_widget_output' ) );

			// Put our widget at the top of the pile
			// Globalize the metaboxes array, this holds all the widgets for wp-admin
			global $wp_meta_boxes;
			
			// Get the regular dashboard widgets array 
			// (which has our new widget already but at the end)
			$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
			
			// Backup and delete our new dashboard widget from the end of the array
			$example_widget_backup = array( 'studiorum_lectio_educator_widget' => $normal_dashboard['studiorum_lectio_educator_widget'] );
			unset( $normal_dashboard['studiorum_lectio_educator_widget'] );

			// Merge the two arrays together so our widget is at the beginning
			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes 
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;

		}/* wp_dashboard_setup__addDashboardWidget() */


		/**
		 * The output for the Lectio widget
		 *
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public static function dashboard_widget_output()
		{



		}/* dashboard_widget_output() */

	}/* class Studiorum_Lectio_Educator_Dashboard */

	$Studiorum_Lectio_Educator_Dashboard = new Studiorum_Lectio_Educator_Dashboard;