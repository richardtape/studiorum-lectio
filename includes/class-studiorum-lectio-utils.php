<?php 

	/**
	 * Lectio Utility functions
	 *
	 * @package     Studiorum Lectio Utils
	 * @subpackage  Studiorum/Lectio/Utils
	 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	 * @since       0.1.0
	 */

	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ){
		exit;
	}

	class Studiorum_Lectio_Utils
	{

		// The post type slug
		static $postTypeSlug = 'lectio-submission';

		// The submission category slug
		static $submissionCategorySlug = 'lectio-submission-category';


		/**
		 * Fetch which submissions a specified user has made
		 *
		 * @since 0.1
		 *
		 * @param int $userID Which user to fetch
		 * @return array The user's lectio submission post IDs, organized by submission category
		 * 				 i.e. array( '5' => array( 123, 456 ), '6' => array( 789 ) )
		 * 				 means for submission category term ID 5, this user has 2 submissions with IDs 123 and 456, etc.
		 */
		public static function fetchUsersSubmissions( $userID = false )
		{

			if( !$userID ){
				return false;
			}

			$userID = intval( $userID );

			// Set up our output
			$userSubmissions = array();

			$queryArgs = array(
				'post_type' 				=> static::$postTypeSlug,
				'posts_per_page' 			=> -1,
				'post_status' 				=> array( 'publish', 'private' ),
				'author' 					=> $userID,
				'update_post_meta_cache' 	=> false,
				'update_post_term_cache' 	=> false,
			);

			$query = new WP_Query( $queryArgs );

				if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post();

					// We need this post's ID
					$postID = get_the_ID();

					// Which category is this in?
					$subCats = wp_get_object_terms( $postID, static::$submissionCategorySlug, array( 'fields' => 'ids' ) );

					if( !empty( $subCats ) && !is_wp_error( $subCats ) )
					{

						foreach( $subCats as $key => $subCatObject )
						{

							// If this subcat for this user already has entries, just add to it, otherwise add entry and then post
							if( !array_key_exists( $subCatObject, $userSubmissions ) ){
								$userSubmissions[$subCatObject] = array();
							}

							$userSubmissions[$subCatObject][] = $postID;

						}

					}

				endwhile; wp_reset_postdata(); endif;

				return $userSubmissions;


		}/* fetchCurrentGroups() */


		/**
		 * Fetch the current user's lectio submission posts
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return array The user's lectio submission post IDs
		 */

		public static function fetchCurrentUsersSubmissions()
		{

			$userID = get_current_user_id();

			if( !$userID ){
				return false;
			}

			return static::fetchUsersSubmissions( $userID );

		}/* fetchCurrentUsersSubmissions() */

	}/* class Studiorum_Lectio_Utils() */