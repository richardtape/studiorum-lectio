<?php

	/**
	 * Template part for custom field select dropdowns
	 *
	 * @since 0.1
	 * @var array $cfData 			- An array of custom fields
	 *
	 */

	$metaKey = $cfData['meta_key'];
	$metaValue = get_post_meta( get_the_ID(), $metaKey, true );
	echo esc_html( $metaValue );
?>