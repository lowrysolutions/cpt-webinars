<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	Lowry_CPT_Webinars
 * @subpackage Lowry_CPT_Webinars/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		Lowry_CPT_Webinars
 */
function LOWRYCPTWEBINARS() {
	return Lowry_CPT_Webinars::instance();
}