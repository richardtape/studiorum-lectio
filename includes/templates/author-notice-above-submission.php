<?php

	/**
	 * Template for the extra content appended to lectio submissions for authors to say
	 * this is your post, we received it
	 *
	 * @since 0.1
	 *
	 */
	
	$message = __( 'This is one of your submissions. It is visible to you and your instructor(s).', 'studiorum-lectio' );
	$message = apply_filters( 'studiorum_lectio_author_note_above_submission', $message );

?>

	<div class="author-submission-notice">

		<p><?php echo esc_html( $message ); ?></p>

	</div><!-- .author-submission-notice -->