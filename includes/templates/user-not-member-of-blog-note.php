<?php

	/**
	 * Template for the note displayed if a user is logged in but they are not a member of the blog instead of
	 * showing the actual submission form
	 *
	 * @since 0.1
	 *
	 */
	
	$message = __( 'You are unable to make a submission as, even though you are signed in, you are not a member of this individual site. Please accept the invitation you have been sent or, if you have not received an invitation, please request an invite from your teacher.', 'studiorum-lectio' );
	$message = apply_filters( 'studiorum_lectio_user_not_member_of_blog_note', $message );

?>

	<div class="notice dark user-not-member-of-blog">

		<p><?php echo esc_html( $message ); ?></p>

	</div><!-- .author-submission-notice -->