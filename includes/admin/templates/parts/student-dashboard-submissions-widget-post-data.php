<?php

	/**
	 * Template for each post in the submission details student widget
	 * @todo Move grade stuff out of here into gradebook and add appropriate actions/filters
	 *
	 * @since 0.1
	 * @var array $postInfo 		- details about this post
	 *
	 */

?>

	<li>

		<div class="grade-details">
			<span class="grade-note"><?php _e( 'Your Grade', 'studiorum-gradebook' ); ?></span>
			<span class="actual-grade"><?php echo esc_html( $postInfo['grade'] ); ?></span>
		</div><!-- .grade -->

		<p class="details">
			<?php _e( 'Submission made on ', 'studiorum-lectio' ); ?> <?php echo $postInfo['date']; ?>.
			<a href="<?php echo esc_url( $postInfo['permalink'] ); ?>" title="" target="_BLANK"><?php _e( 'View Submission', 'studiorum-lectio' ); ?></a>
		</p>

		<p class="excerpt">
			<?php echo esc_html( $postInfo['excerpt'] ); ?>
		</p>

	</li>