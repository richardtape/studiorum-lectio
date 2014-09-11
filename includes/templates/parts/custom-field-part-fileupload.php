<?php

	/**
	 * Template part for custom field file uploads
	 *
	 * @since 0.1
	 * @var array $cfData 			- An array of custom fields
	 *
	 */

	$metaKey = $cfData['meta_key'];
	$metaValue = get_post_meta( get_the_ID(), $metaKey, true );
	
?>

	<a href="<?php echo esc_url( $metaValue ); ?>" target="_new" class="custom-field-fileupload"><?php echo esc_url( $metaValue ); ?></a>