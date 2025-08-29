/**
 * Default Image Expand Editor Script
 * 
 * Modifies the core/image block to default lightbox.enabled to true
 */

( function() {
	'use strict';

	// Wait for WordPress to be ready
	wp.domReady( function() {
		// Hook into block registration to modify defaults
		wp.hooks.addFilter(
			'blocks.registerBlockType',
			'default-image-expand/modify-image-defaults',
			function( settings, name ) {
				// Only modify the core/image block
				if ( name !== 'core/image' ) {
					return settings;
				}

				// Ensure attributes object exists
				if ( ! settings.attributes ) {
					settings.attributes = {};
				}

				// Set default lightbox enabled
				if ( settings.attributes.lightbox ) {
					// If lightbox attribute already exists, modify its default
					settings.attributes.lightbox = {
						...settings.attributes.lightbox,
						default: { enabled: true }
					};
				} else {
					// If lightbox attribute doesn't exist, create it with default
					settings.attributes.lightbox = {
						type: 'object',
						default: { enabled: true }
					};
				}

				return settings;
			}
		);
	} );
} )();