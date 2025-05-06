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

namespace Bethink\RestrictBlockContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

const RESTRICTABLE_BLOCKS = array(
	'core/group',
	'core/row',
);


function _enqueue_block_editor_assets() {
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');
	wp_enqueue_script(
		'bethink-restrict-block-content',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_set_script_translations(
		'bethink-restrict-block-content',
		'restrict-block-content'
	);

	/**
	 * Based on implementation in Kadence Blocks
	 *
	 * @link https://github.com/stellarwp/kadence-blocks/blob/67e47610ab78274f6235d69b0e7b4c08940d2346/includes/class-kadence-blocks-editor-assets.php#L235-L254
	 */
	$access_levels = [];
	$level_ids     = [];
	if ( function_exists( 'rcp_get_access_levels' ) ) {
		foreach ( rcp_get_access_levels() as $key => $access_level_label ) {
			$access_levels[] = [
				'value' => $key,
				/* translators: %s is the access level name. */
				'label' => sprintf( __( 'RCP Level %s', 'restrict-block-content' ), $key ),
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
		'bethink-restrict-block-content',
		'restrictBlockOptions',
		array(
			'access_levels'       => $access_levels,
			'level_ids'           => $level_ids,
			'restrictable_blocks' => RESTRICTABLE_BLOCKS,
		)
	);
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\_enqueue_block_editor_assets' );

/**
 * Add in our custom attributes to supported blocks.
 */
function _register_block_type_args( $args, $block_type ) {
	if ( in_array( $block_type, RESTRICTABLE_BLOCKS ) ) {
		if ( ! isset( $args['attributes'] ) ) {
            $args['attributes'] = array();
        }
		$args['attributes']['brcp_restrictions'] = array(
			'type'    => 'boolean',
			'default' => false,
		);
		$args['attributes']['brcp_restriction_level'] = array(
            'type'    => 'integer',
			'default' => 0,
        );
		$args['attributes']['brcp_restriction_type'] = array(
            'type'    => 'string',
			'default' => '',
        );
	}

	return $args;
}
add_filter( 'register_block_type_args', __NAMESPACE__ . '\_register_block_type_args', 10, 2 );

/**
 * Determines whether to show or hide the block based on access levels.
 *
 * @param string|null   $pre_render   The pre-rendered content. Default null.
 * @param array         $parsed_block {
 *     An associative array of the block being rendered. See WP_Block_Parser_Block.
 *
 *     @type string   $blockName    Name of block.
 *     @type array    $attrs        Attributes from block comment delimiters.
 *     @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
 *                                  have the same structure as this one.
 *     @type string   $innerHTML    HTML from inside block comment delimiters.
 *     @type array    $innerContent List of string fragments and null markers where
 *                                  inner blocks were found.
 * }
 * @return string|null
 */
function _pre_render_block( $pre_render, $parsed_block ) {
	if ( in_array( $parsed_block['blockName'], RESTRICTABLE_BLOCKS ) ) {
		if ( ! empty( $parsed_block['attrs']['brcp_restrictions'] ) ) {
			$level   = $parsed_block['attrs']['brcp_restriction_level'];
			$compare = $parsed_block['attrs']['brcp_restriction_type'];

			switch( $compare ) {
				case '>=':
					// If the user doesn't have access to this level, deny it.
					if ( ! rcp_user_has_access( get_current_user_id(), $level ) ) {
						/**
						 * Deny it in a way that we can customize the output if desired to explain why.
						 *
						 * @param string   The reason to return.  Will be output to the user.
						 * @param array    $parsed_block {
						 *     An associative array of the block being rendered. See WP_Block_Parser_Block.
						 *
						 *     @type string   $blockName    Name of block.
						 *     @type array    $attrs        Attributes from block comment delimiters.
						 *     @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
						 *                                  have the same structure as this one.
						 *     @type string   $innerHTML    HTML from inside block comment delimiters.
						 *     @type array    $innerContent List of string fragments and null markers where
						 *                                  inner blocks were found.
						 * }
						 */
						return apply_filters( 'bethink_rbc_denied', '', $parsed_block );
					}
					break;
				case '<':
					if ( rcp_user_has_access( get_current_user_id(), $level ) ) {
						/**
						 * Deny it in a way that we can customize the output if desired to explain why.
						 *
						 * @param string   The reason to return.  Will be output to the user.
						 * @param array    $parsed_block {
						 *     An associative array of the block being rendered. See WP_Block_Parser_Block.
						 *
						 *     @type string   $blockName    Name of block.
						 *     @type array    $attrs        Attributes from block comment delimiters.
						 *     @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
						 *                                  have the same structure as this one.
						 *     @type string   $innerHTML    HTML from inside block comment delimiters.
						 *     @type array    $innerContent List of string fragments and null markers where
						 *                                  inner blocks were found.
						 * }
						 */
						return apply_filters( 'bethink_rbc_denied', '', $parsed_block );
					}
					break;
			}
		}
	}
	return $pre_render;
}
add_filter( 'pre_render_block', __NAMESPACE__ . '\_pre_render_block', 10, 2 );
