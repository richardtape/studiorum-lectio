<?php

	/**
	 * Template for the 'print' endpoint
	 *
	 * @since 0.1
	 * @var (int) $postID
	 * @var (array) $linearComments
	 * @var (array) $sideComments
	 * @var (array) $contentWithSideComments
	 *
	 */

?>

	<!doctype html>
	<html class="no-js" lang="">

		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<title></title>
			<meta name="description" content="">
			<meta name="viewport" content="width=device-width, initial-scale=1">

			<link rel="stylesheet" href="<?php echo LECTIO_PLUGIN_URL; ?>includes/assets/css/studiorum-lectio-submission-print-stylesheet.css">

		</head>
		<body>

			<h1><?php echo esc_html( $post->post_title ); ?></h1>
			<div class="content-and-comments">

				<?php foreach( $contentWithSideComments as $key => $pAndSideComments ) : ?>

					<?php echo $pAndSideComments; ?>

				<?php endforeach; ?>
			</div><!-- content-and-comments -->

			<div class="linear-comments">
				<?php comments_template(); ?>
			</div><!-- .linear-comments -->

		</body>
	</html>