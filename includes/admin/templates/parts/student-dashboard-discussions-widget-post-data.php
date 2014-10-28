<?php

	/**
	 * Template for each post in the discussions details student widget
	 *
	 * @since 0.1
	 * @var array $postInfo 		- details about this post
	 *
	 */

	// Get the author details
	$postID = $postInfo['postID'];
	$authorID = get_post_field( 'post_author', $postID );
	$author = ( get_the_author_meta( 'user_nicename', $authorID ) != '' ) ? get_the_author_meta( 'user_nicename', $authorID ) : get_the_author_meta( 'user_login', $authorID );

?>

	<li>

		<p class="details">
			<?php _e( 'Submission made on ', 'studiorum-lectio' ); ?> <?php echo $postInfo['date']; ?>.
			<a href="<?php echo esc_url( $postInfo['permalink'] ); ?>" title="" target="_BLANK"><?php _e( 'View Submission', 'studiorum-lectio' ); ?></a> <?php _e( 'by', 'studiorum-lectio' ); ?> <?php echo $author; ?>
		</p>

	</li>