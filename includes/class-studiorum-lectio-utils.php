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

		// This site's attached users
		static $thisSitesUsers = false;

		/**
		 * Fetch which submissions a specified user has made
		 *
		 * @since 0.1
		 *
		 * @param int $userID Which user to fetch
		 * @return array The user's lectio submission post IDs, organized by submission category
		 * 				 i.e. array( '1' => array( '5' => array( 123, 456 ), '6' => array( 789 ) ) )
		 * 				 means for submission category term ID 5, user ID 1 has 2 submissions with IDs 123 and 456, etc.
		 */
		public static function fetchUsersSubmissions( $userID = false )
		{

			// Set up our output
			$userSubmissions = array();

			$queryArgs = array(
				'post_type' 				=> static::$postTypeSlug,
				'posts_per_page' 			=> -1,
				'post_status' 				=> array( 'publish', 'private' ),
				'update_post_meta_cache' 	=> false,
				'update_post_term_cache' 	=> false
			);

			// If we specify an author ID, we limit the query just to that user ID
			if( $userID )
			{

				$userID = intval( $userID );

				$queryArgs['author'] = $userID;

			}


			$query = new WP_Query( $queryArgs );

				if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post();

					// We need this post's ID
					$postID = get_the_ID();

					// Author ID
					$authorID = get_the_author_meta( 'ID' );

					// Which category is this in?
					$subCats = wp_get_object_terms( $postID, static::$submissionCategorySlug, array( 'fields' => 'ids' ) );
					
					if( !empty( $subCats ) && !is_wp_error( $subCats ) )
					{

						foreach( $subCats as $key => $subCatObject )
						{

							// Ensure this user has an array
							if( !array_key_exists( $authorID, $userSubmissions ) ){
								$userSubmissions[$authorID] = array();
							}

							// If this subcat for this user already has entries, just add to it, otherwise add entry and then post
							if( !array_key_exists( $subCatObject, $userSubmissions[$authorID] ) ){
								$userSubmissions[$authorID][$subCatObject] = array();
							}

							$userSubmissions[$authorID][$subCatObject][] = $postID;

						}

					}

				endwhile; wp_reset_postdata(); endif;

				return $userSubmissions;


		}/* fetchUsersSubmissions() */


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


		/**
		 * We need a list of all available public post type posts for the dropdowns in the options
		 * so admins are able to tell us which posts/pages have a gForm on it on which students can
		 * upload media
		 *
		 * @since 0.1
		 *
		 * @param null
		 * @return array Associative array of arrays array( 'post_type_1' => array( 1 => 'PostID 1 Title' ), 'post_type_2' => array( 23 => 'PostID 23 Title' ) )
		 */

		public static function getAllPostTypesIDsAndTitles()
		{

			// We want to grab all publicly available post types, excluding the built-in ones, meaning we 
			// can manually add posts/pages and ensure we have all CPTs We also exclude lectio-submissions
			$args = array(
				'public'   => true,
				'_builtin' => false
			);

			$postTypes = get_post_types( $args, 'names' );

			$postTypes['post'] = 'post';
			$postTypes['page'] = 'page';

			if( isset( $postTypes[static::$postTypeSlug] ) ){
				unset( $postTypes[static::$postTypeSlug] );
			}

			if( !$postTypes ){
				return false;
			}

			// Set up our WP_Query args
			$WPQueryArgs = array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> array_keys( $postTypes ),
				'post_status' 		=> 'any'
			);

			// Set up our output
			$output = array( 0 => __( 'Select Post', 'studiorum-lectio' ) );

			$allPostsQuery = new WP_Query( $WPQueryArgs );

			if( $allPostsQuery->have_posts() ) : while ( $allPostsQuery->have_posts() ) : $allPostsQuery->the_post();

				$postID 	= get_the_ID();
				$postType 	= get_post_type();
				$title 		= get_the_title();

				// Ensure this post type has an array key
				if( !array_key_exists( $postType, $output ) ){
					$output[$postType] = array();
				}

				$output[$postType][$postID] = '(ID: ' . $postID . ') ' .  $title;

			endwhile; wp_reset_postdata(); endif;

			return $output;

		}/* getAllPostTypesIDsAndTitles() */


		/**
		 * Get an array of user objects for this site
		 *
		 * @since 0.1
		 *
		 * @param string $param description
		 * @return string|int returnDescription
		 */

		public static function getThisSitesUsers()
		{

			if( isset( static::$thisSitesUsers ) && is_array( static::$thisSitesUsers ) && !empty( static::$thisSitesUsers ) )
			{
				$thisSitesUsers = static::$thisSitesUsers;
			}
			else
			{

				$roleToFetch = apply_filters( 'studiorum_user_groups_fetch_users_role', 'studiorum_student' );
		
				$thisSitesUsers = static::getUsersOfRole( $roleToFetch );

				static::$thisSitesUsers = $thisSitesUsers;

			}

			return $thisSitesUsers;

		}/* getThisSitesUsers() */


		public static function getUsersOfRole( $role = 'subscriber' )
		{

			if( !$role ){
				return new WP_Error( '1', 'getUsersOfRole() requires a $role argument' );
			}

			$args = array(
				'role' => $role
			);

			$wp_user_search = new WP_User_Query( $args );

			$users = $wp_user_search->get_results();

			return $users;

		}/* getUsersOfRole() */

	}/* class Studiorum_Lectio_Utils() */