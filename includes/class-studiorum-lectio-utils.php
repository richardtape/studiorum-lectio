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

	}/* class Studiorum_Lectio_Utils() */