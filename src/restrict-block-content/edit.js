
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, useInnerBlocksProps, InnerBlocks } from "@wordpress/block-editor";
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from "@wordpress/components";

export default function Edit( { attributes: { membership_tier }, setAttributes } ) {
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( "Restriction Details" ) }>
					<NumberControl
						label={ __( "Tier" ) }
						value={ membership_tier }
						min={ 0 }
						max={ 10 }
						onChange={ (val) => setAttributes( { membership_tier: val } ) }
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<p>The following blocks will only be visible by { membership_tier } and above.</p>
				<InnerBlocks />
			</div>
		</>
	);
}
