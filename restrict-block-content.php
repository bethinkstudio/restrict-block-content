<?php
/**
 * Plugin Name:       Restrict Block Content
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       restrict-block-content
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


function create_block_restrict_block_content_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}

	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'create_block_restrict_block_content_block_init' );

/**
 * Based on implementation in Kadence Blocks
 *
 * @link https://github.com/stellarwp/kadence-blocks/blob/67e47610ab78274f6235d69b0e7b4c08940d2346/includes/class-kadence-blocks-editor-assets.php#L235-L254
 */
add_action( 'init', function() {
	$access_levels = [];
	$level_ids     = [];
	if ( function_exists( 'rcp_get_access_levels' ) ) {
		foreach ( rcp_get_access_levels() as $key => $access_level_label ) {
			$access_levels[] = [
				'value' => $key,
				/* translators: %s is the access level name. */
				'label' => sprintf( __( '%s and higher' ), $key ),
			];
		}
		foreach ( rcp_get_membership_levels( [ 'number' => 999 ] ) as $level ) {
			$level_ids[] = [
				'value' => $level->get_id(),
				'label' => esc_attr( $level->get_name() ),
			];
		}
	}
	if ( empty( $level_ids ) ) {
		$level_ids = false;
	}
	wp_localize_script(
		'bethink-restrict-block-content-editor-script',
		'restrictBlockOptions',
		array(
			'access_levels' => $access_levels,
			'level_ids'     => $level_ids,
		)
	);
});
