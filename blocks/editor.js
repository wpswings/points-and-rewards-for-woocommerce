/**
 * Points and Rewards blocks pack - editor UI.
 *
 * Plain ES5 using WordPress globals, so it works without a build step.
 * Block metadata (title, attributes, ...) comes from each block.json, which
 * is registered on the server. The editor shows a live server-side preview.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element ) {
		return;
	}

	var el                = wp.element.createElement;
	var __                = wp.i18n.__;
	var blockEditor       = wp.blockEditor || wp.editor;
	var useBlockProps     = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var components        = wp.components;
	var ServerSideRender  = wp.serverSideRender;
	var TD                = 'points-and-rewards-for-woocommerce';

	var guestControls = [
		{ key: 'showLoginPrompt', type: 'toggle', label: __( 'Show a login link to guests', TD ) },
		{ key: 'loginText', type: 'text', label: __( 'Login link text', TD ), placeholder: __( 'Log in to see your reward points.', TD ) }
	];

	var controls = {
		'wps-wpr/points-balance': [
			{ key: 'label', type: 'text', label: __( 'Label', TD ), placeholder: __( 'Your Points', TD ) },
			{ key: 'showLevel', type: 'toggle', label: __( 'Show membership level', TD ) }
		].concat( guestControls ),
		'wps-wpr/points-log': guestControls,
		'wps-wpr/referral-link': [
			{ key: 'heading', type: 'text', label: __( 'Heading', TD ), placeholder: __( 'Refer a friend', TD ) }
		].concat( guestControls ),
		'wps-wpr/earn-points': [
			{ key: 'heading', type: 'text', label: __( 'Heading', TD ), placeholder: __( 'Ways to earn points', TD ) }
		],
		'wps-wpr/membership-level': [
			{ key: 'label', type: 'text', label: __( 'Label', TD ), placeholder: __( 'Your membership level', TD ) },
			{ key: 'noLevelText', type: 'text', label: __( 'Text when the customer has no level', TD ) }
		].concat( guestControls ),
		'wps-wpr/leaderboard': [
			{ key: 'heading', type: 'text', label: __( 'Heading', TD ), placeholder: __( 'Top members', TD ) },
			{ key: 'limit', type: 'range', label: __( 'Number of customers', TD ), min: 1, max: 50 },
			{ key: 'showPoints', type: 'toggle', label: __( 'Show points', TD ) },
			{
				key: 'nameFormat',
				type: 'select',
				label: __( 'Customer names', TD ),
				options: [
					{ value: 'first_initial', label: __( 'First name and last initial', TD ) },
					{ value: 'display_name', label: __( 'Display name', TD ) },
					{ value: 'anonymous', label: __( 'Anonymous (Customer #1)', TD ) }
				]
			}
		]
	};

	function renderControl( control, props ) {
		var value    = props.attributes[ control.key ];
		var onChange = function ( next ) {
			var update = {};
			update[ control.key ] = next;
			props.setAttributes( update );
		};

		switch ( control.type ) {
			case 'toggle':
				return el( components.ToggleControl, { key: control.key, label: control.label, checked: !! value, onChange: onChange } );
			case 'range':
				return el( components.RangeControl, { key: control.key, label: control.label, value: value, min: control.min, max: control.max, onChange: onChange } );
			case 'select':
				return el( components.SelectControl, { key: control.key, label: control.label, value: value, options: control.options, onChange: onChange } );
			default:
				return el( components.TextControl, { key: control.key, label: control.label, value: value || '', placeholder: control.placeholder || '', onChange: onChange } );
		}
	}

	Object.keys( controls ).forEach( function ( name ) {
		wp.blocks.registerBlockType( name, {
			edit: function ( props ) {
				var blockProps = useBlockProps ? useBlockProps() : {};
				return el(
					'div',
					blockProps,
					el(
						InspectorControls,
						{},
						el(
							components.PanelBody,
							{ title: __( 'Settings', TD ), initialOpen: true },
							controls[ name ].map( function ( control ) {
								return renderControl( control, props );
							} )
						)
					),
					el( ServerSideRender, {
						block: name,
						attributes: props.attributes,
						EmptyResponsePlaceholder: function () {
							return el( components.Placeholder, {
								label: wp.blocks.getBlockType( name ).title,
								instructions: __( 'Nothing to show for the current user. Customers will see their own data here.', TD )
							} );
						}
					} )
				);
			},
			save: function () {
				return null;
			}
		} );
	} );
} )( window.wp );
