<?php
	/*
	 * Plugin Name: Studiorum Lectio
	 * Description: Lectio is an add-on for Studiorum which adds a lesson management solution for WordPress
	 * Version:     0.1
	 * Plugin URI:  #
	 * Author:      UBC, CTLT, Richard Tape
	 * Author URI:  http://ubc.ca/
	 * Text Domain: studiorum-lectio
	 * License:     GPL v2 or later
	 * Domain Path: languages
	 *
	 * lectio is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 2 of the License, or
	 * any later version.
	 *
	 * lectio is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with lectio. If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @package lectio
	 * @category Core
	 * @author Richard Tape
	 * @version 0.1.0
	 */

	if( !defined( 'ABSPATH' ) ){
		die( '-1' );
	}

	if( !defined( 'LECTIO_PLUGIN_DIR' ) ){
		define( 'LECTIO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL
	if( !defined( 'LECTIO_PLUGIN_URL' ) ){
		define( 'LECTIO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Load lectio
	 *
	 * @since 0.1
	 *
	 * @param null
	 * @return null
	 */
	function studiorum_after_includes__loadLectio()
	{

		require_once( trailingslashit( LECTIO_PLUGIN_DIR ) . 'includes/class-studiorum-lectio.php' );

	}/* studiorum_after_includes__loadLectio() */

	add_action( 'studiorum_after_includes', 'studiorum_after_includes__loadLectio' );