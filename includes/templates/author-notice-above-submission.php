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

	$allowedHTML = apply_filters( 'studiorum_lectio_author_note_above_submission_allowed_html', array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	) );

?>

	<div class="notice dark author-submission-notice">

		<p><?php echo wp_kses( $message, $allowedHTML ); ?></p>

	</div><!-- .author-submission-notice -->