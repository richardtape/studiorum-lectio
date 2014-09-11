<?php

	/**
	 * Template for the extra content appended to lectio submissions if there are form custom fields
	 *
	 * @since 0.1
	 * @var array $data 			- An array of custom fields
	 *
	 */

?>

	<div class="lectio-submission-custom-fields">

		<?php foreach( $data as $key => $cfData  ) : ?>

			<h4><?php echo esc_html( $cfData['meta_title'] ); ?></h4>

			<?php

				// If the template part exists, load it, otherwise just output the value
				if( file_exists( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/templates/parts/custom-field-part-' . $cfData['input_type'] .'.php' ) ) ) :
			
					include( Studiorum_Utils::locateTemplateInPlugin( LECTIO_PLUGIN_DIR, 'includes/templates/parts/custom-field-part-' . $cfData['input_type'] .'.php' ) );

				else :

					$metaKey = $cfData['meta_key'];
					$metaValue = get_post_meta( get_the_ID(), $metaKey, true );
					echo esc_html( $metaValue );

				endif;

			?>

		<?php endforeach; ?>

	</div><!-- .lectio-submission-custom-fields -->