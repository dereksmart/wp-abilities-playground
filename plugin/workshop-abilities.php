<?php
/**
 * Plugin Name: Workshop Abilities
 * Description: Starter plugin for building WordPress abilities.
 * Version: 1.0.0
 * Requires at least: 6.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_abilities_api_init', function () {

	// -- Autoload ability files from abilities/ directory --
	$abilities_dir = __DIR__ . '/abilities';
	if ( is_dir( $abilities_dir ) ) {
		foreach ( glob( $abilities_dir . '/*.php' ) as $file ) {
			require_once $file;
		}
	}
} );
