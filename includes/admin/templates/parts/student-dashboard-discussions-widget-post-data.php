<?php

	/**
	 * Template for each post in the discussions details student widget
	 *
	 * @since 0.1
	 * @var array $postInfo 		- details about this post
	 *
	 */

?>

	<li>

		<p class="details">
			<?php _e( 'Submission made on ', 'studiorum-lectio' ); ?> <?php echo $postInfo['date']; ?>.
			<a href="<?php echo esc_url( $postInfo['permalink'] ); ?>" title="" target="_BLANK"><?php _e( 'View Submission', 'studiorum-lectio' ); ?></a>
		</p>

	</li>