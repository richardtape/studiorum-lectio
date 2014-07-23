<?php

	/**
	 * Template for the message shown when the max-submissions have been reached
	 *
	 * @since 0.1
	 * @var int $numOfSubmissions 				- The total number of submissions made by this user
	 * @var array $submittedAssignmentDetails 	- details about each submission post for this assignment made by this user
	 * @var string $submissionText 				- Singular or Plural of submission(s) depending on $numOfSubmissions
	 *
	 */

?>

	<div class="pre-form-message already-submitted">

		<h3><?php _e( 'Submissions are closed for this assignment', 'studiorum-lectio' ); ?></h3>

		<p><?php _e( 'You have already made the maximum amount of submissions for this assignment. The details of your ' . $submissionText . ' follows;', 'studiorum-lectio' ); ?></p>

		<ul class="subtotal-<?php echo esc_attr( $numOfSubmissions ); ?>">
			<?php foreach( $submittedAssignmentDetails as $key => $postData ){ include( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/templates/parts/max-submissions-reached-post-data.php' ) ); } ?>
		</ul>

	</div><!-- .pre-form-message -->