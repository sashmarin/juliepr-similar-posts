( function( blocks, blockEditor, components, element ) {
	var createElement = element.createElement;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;

	blocks.registerBlockType( 'juliepr/similar-posts', {
		edit: function( props ) {
			var minYear = props.attributes.minYear || 0;
			var number = props.attributes.number || 3;

			return createElement(
				element.Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{
							title: 'Similar Posts settings',
						},
						createElement( TextControl, {
							label: 'Number of posts',
							type: 'number',
							min: 1,
							value: number,
							onChange: function( value ) {
								var count = parseInt( value, 10 );
								props.setAttributes( {
									number: isNaN( count ) || count < 1 ? 3 : count,
								} );
							},
						} ),
						createElement( TextControl, {
							label: 'Minimum publication year',
							type: 'number',
							min: 0,
							value: minYear || '',
							onChange: function( value ) {
								var year = parseInt( value, 10 );
								props.setAttributes( {
									minYear: isNaN( year ) || year < 1 ? 0 : year,
								} );
							},
						} )
					)
				),
				createElement(
					'div',
					{
						className: 'wp-block-juliepr-similar-posts',
					},
					'Juliepr Similar Posts'
				)
			);
		},
		save: function() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element );
