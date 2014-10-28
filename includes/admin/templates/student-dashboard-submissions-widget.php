<?php

	/**
	 * Template for the main part of the student dashboard submissions widget
	 *
	 * @since 0.1
	 * @var array $postData 		- details about each submission post made by this user
	 *
	 */

?>

	<div class="submissions-container">

	<p>
		<?php _e( 'Here is a list of your assignment submissions' ); ?>
	</p>

		<ul>
			<?php foreach( $postData as $key => $nameAndPosts ) : ?>
				<li>
					<h2><?php echo $nameAndPosts['name']; ?></h2>
					<ul>
						<?php foreach( $nameAndPosts['posts'] as $key => $postInfo ){ include( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/admin/templates/parts/student-dashboard-submissions-widget-post-data.php' ) ); } ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>

	</div><!-- .submissions-container -->