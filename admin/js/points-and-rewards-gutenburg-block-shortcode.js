jQuery(document).ready(function ($) {

    const { registerBlockType }      = wp.blocks;
    const { TextControl, PanelBody } = wp.components;
    const { useState }               = wp.element;
    const { useBlockProps }          = wp.blockEditor;

     // creating user current points block.
     registerBlockType('points/user-points', {
        title      : 'WPSwings User Points Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[MYCURRENTPOINT]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[MYCURRENTPOINT]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });

    // creating user level block.
    registerBlockType('points/user-level', {
        title      : 'WPSwings User Level Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[MYCURRENTUSERLEVEL]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[MYCURRENTUSERLEVEL]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });

    // creating signup block.
    registerBlockType('points/signup-notify', {
        title      : 'WPSwings Signup Notify Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[SIGNUPNOTIFICATION]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[SIGNUPNOTIFICATION]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });

    // creating cart page apply points block.
    registerBlockType('points/cart-section', {
        title      : 'WPSwings Apply Points Cart Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[WPS_CART_PAGE_SECTION]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[WPS_CART_PAGE_SECTION]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });

    // creating checkout page apply points block.
    registerBlockType('points/checkout-section', {
        title      : 'WPSwings Apply Points Checkout Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[WPS_CHECKOUT_PAGE_SECTION]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[WPS_CHECKOUT_PAGE_SECTION]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });

     // creating points logs block.
     registerBlockType('points/points-logs', {
        title      : 'WPSwings Points Logs Shortcode',
        icon       : 'media-document',
        category   : 'widgets',
        attributes : {
            shortcode : { type: 'string', default: '[SHOW_POINTS_LOG]' }
        },
        edit: function(props) {
            return wp.element.createElement('div', useBlockProps(),
                wp.element.createElement(TextControl, {
                    label       : 'Enter Shortcode',
                    value       : props.attributes.shortcode,
                    onChange    : function(shortcode) { props.setAttributes({ shortcode: shortcode }) },
                    placeholder : '[SHOW_POINTS_LOG]'
                }),
                wp.element.createElement('p', {}, 'Shortcode Output: ' + props.attributes.shortcode)
            );
        },
        save: function(props) {
            return wp.element.createElement('div', useBlockProps.save(), props.attributes.shortcode);
        }
    });
});