
import { __, sprintf } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import {
	ExternalLink,
	PanelBody,
	PanelRow,
	SelectControl,
	ToggleControl,
	__experimentalNumberControl as NumberControl
} from '@wordpress/components';

const { access_levels, level_ids, restrictable_blocks } = restrictBlockOptions;

/**
 * Confirm adding our custom attributes to all supported blocks.
 *
 * In theory this should already be managed by PHP through a filter.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function addRbcAttributes( settings, name ) {

	// Only add the attribute to supported blocks.
	if ( restrictable_blocks.includes( name ) ) {
		settings.attributes = {
			...settings.attributes,
			brcp_restrictions: {
				type:    'boolean',
				default: false,
			},
			brcp_restriction_level: {
				type:    'integer',
				default: 0,
			},
			brcp_restriction_type: {
				type:    'string',
				default: '',
			},
		};
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'bethink/restrict-block-content/add-attributes',
	addRbcAttributes
);

const rbcHelpText = (
	<>
		{ __( "Enabling this will restrict this block from displaying for some visitors. ", 'restrict-block-content' ) }
		<ExternalLink
			href={
				'https://bethink.studio/'
			}
		>
			{ __( 'Learn more.', 'restrict-block-content' ) }
		</ExternalLink>
	</>
);

function addRbcInspectorControls( BlockEdit ) {
	return ( props ) => {
		const { name, attributes, setAttributes } = props;

		// Early return if the block is not supported.
		if ( ! restrictable_blocks.includes( name ) ) {
			return <BlockEdit { ...props } />;
		}

		// Retrieve selected attributes from the block.
		const { brcp_restrictions, brcp_restriction_level, brcp_restriction_type } = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Access Restrictions', 'restrict-block-content' ) }
					>
						<PanelRow>
							<ToggleControl
								label={ __( 'Enable Restrictions?', 'restrict-block-content' ) }
								checked={ brcp_restrictions }
								onChange={ ( value ) => {
									setAttributes( { brcp_restrictions: value } );
								} }
								help={ rbcHelpText }
								__nextHasNoMarginBottom
							/>
						</PanelRow>
						{ !! brcp_restrictions && <>
							<PanelRow>
								<NumberControl
									label={ __( "Level" ) }
									value={ brcp_restriction_level }
									min={ 0 }
									max={ 10 }
									onChange={ (value) => setAttributes( { brcp_restriction_level: parseInt( value, 10 ) } ) }
									__next40pxDefaultSize
								/>
							</PanelRow>
							<PanelRow>
								<SelectControl
									label={ __( 'Only show if...', 'restrict-block-content' ) }
									value={ brcp_restriction_type }
									options={ [
										{
											label: sprintf( __( 'User has level %s or higher', 'restrict-block-content' ), brcp_restriction_level ),
											value: '>=',
										},
										{
											label: sprintf( __( 'User does not have level %s', 'restrict-block-content' ), brcp_restriction_level ),
											value: '<',
										},
									] }
									onChange={ (value) => setAttributes( { brcp_restriction_type: value } ) }
									__nextHasNoMarginBottom
									__next40pxDefaultSize
								/>
							</PanelRow>
						</> }
					</PanelBody>
				</InspectorControls>
				</>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'bethink/restrict-block-content/add-inspector-controls',
	addRbcInspectorControls
);
