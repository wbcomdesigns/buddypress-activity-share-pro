/**
 * "Share buttons" block (server-rendered; no build step needed).
 */
( function ( wp ) {
	'use strict';
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'bpas/share', {
		apiVersion: 3,
		title: __( 'Share buttons', 'buddypress-activity-share-pro' ),
		description: __( 'The share menu for this page.', 'buddypress-activity-share-pro' ),
		category: 'widgets',
		icon: 'share',
		supports: { html: false },
		edit: function () {
			var props = wp.blockEditor.useBlockProps();
			return el( 'div', props, el( wp.serverSideRender, { block: 'bpas/share' } ) );
		},
		save: function () {
			return null;
		}
	} );

	wp.blocks.registerBlockType( 'bpas/trending', {
		apiVersion: 3,
		title: __( 'Most shared', 'buddypress-activity-share-pro' ),
		description: __( 'The community posts shared most recently.', 'buddypress-activity-share-pro' ),
		category: 'widgets',
		icon: 'chart-line',
		attributes: {
			days: { type: 'number', default: 7 },
			limit: { type: 'number', default: 5 }
		},
		supports: { html: false },
		edit: function ( props ) {
			var blockProps = wp.blockEditor.useBlockProps();
			return el(
				'div',
				blockProps,
				el(
					wp.blockEditor.InspectorControls,
					null,
					el(
						wp.components.PanelBody,
						{ title: __( 'Settings', 'buddypress-activity-share-pro' ) },
						el( wp.components.SelectControl, {
							label: __( 'Period', 'buddypress-activity-share-pro' ),
							value: String( props.attributes.days ),
							options: [
								{ label: __( 'Last 7 days', 'buddypress-activity-share-pro' ), value: '7' },
								{ label: __( 'Last 30 days', 'buddypress-activity-share-pro' ), value: '30' }
							],
							onChange: function ( v ) {
								props.setAttributes( { days: parseInt( v, 10 ) } );
							}
						} ),
						el( wp.components.RangeControl, {
							label: __( 'Number of posts', 'buddypress-activity-share-pro' ),
							value: props.attributes.limit,
							min: 1,
							max: 20,
							onChange: function ( v ) {
								props.setAttributes( { limit: v } );
							}
						} )
					)
				),
				el( wp.serverSideRender, { block: 'bpas/trending', attributes: props.attributes } )
			);
		},
		save: function () {
			return null;
		}
	} );
}( window.wp ) );
