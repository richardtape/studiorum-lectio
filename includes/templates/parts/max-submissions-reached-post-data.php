<?php

	/**
	 * Template part for the individual post data for each submission made by this student 
	 *
	 * @since 0.1
	 * @var array $postData 	- The data about this post; [ID] [permalink] [excerpt] [title] [date] [author]
	 *
	 */

?>

	<li class="sub-<?php echo absint( ( $key + 1 ) ); ?>">

		<h4>
			<a href="<?php echo esc_url( $postData['permalink'] ); ?>" title="">
				<?php _e( 'Submission', 'studiorum-lectio' ); ?> <?php echo absint( ( $key + 1 ) ); ?>
			</a>
		</h4>

		<h5>
			<?php _e( 'Submitted on', 'studiorum-lectio' ); ?> <?php echo esc_html( $postData['date'] ); ?> by <?php echo esc_attr( $postData['author'] ); ?>
		</h5>

		<p>
			<?php echo apply_filters( 'the_excerpt', esc_html( $postData['excerpt'] ) ); ?>
		</p>

		<p>
			<a href="<?php echo esc_url( $postData['permalink'] ); ?>" title="">
				<?php _e( 'View Submission', 'studiorum-lectio' ); ?>
			</a>
		</p>

	</li>