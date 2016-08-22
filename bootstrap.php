<?php

/*
 * Plugin Name: Papi: Shortcake property
 * Description: Shortcake property for Papi
 * Version: 1.0.0
 * Author: Fredrik Forsmo
 */

/**
 * Include table property.
 */
add_action( 'papi/init', function () {
	include_once __DIR__ . '/class-papi-property-shortcake.php';
} );

// Add shortcode ajax action for backward compatibility.
// This action does only exist in Papi 3.2+
if ( ! has_action( 'papi/ajax/get_shortcode' ) ) {
	add_action( 'papi/ajax/get_shortcode', function () {
		$shortcode = papi_get_qs( 'shortcode' ) ?: '';
		$shortcode = html_entity_decode( $shortcode );
		$shortcode = wp_unslash( $shortcode );

		wp_send_json( [
			'html' => do_shortcode( $shortcode )
		] );
	} );
}
