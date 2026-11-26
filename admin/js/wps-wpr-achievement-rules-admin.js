/**
 * Achievement rules settings: add / remove rules and conditions.
 *
 * @package points-and-rewards-for-wooCommerce
 */
( function () {
	'use strict';

	var counter = Date.now();

	function uniqueKey( prefix ) {
		counter += 1;
		return prefix + counter.toString( 36 );
	}

	function fromTemplate( id, replacements ) {
		var tpl = document.getElementById( id );
		if ( ! tpl ) {
			return null;
		}
		var html = tpl.innerHTML;
		Object.keys( replacements ).forEach( function ( key ) {
			html = html.split( key ).join( replacements[ key ] );
		} );
		var holder = document.createElement( 'div' );
		holder.innerHTML = html.trim();
		return holder.firstElementChild;
	}

	// Show only the operators that fit the condition type, and its hint.
	function syncCondition( row ) {
		var type = row.querySelector( '.wps_wpr_ach_cond_type' );
		var operator = row.querySelector( '.wps_wpr_ach_cond_operator' );
		var value = row.querySelector( '.wps_wpr_ach_cond_value' );
		if ( ! type || ! operator || ! value ) {
			return;
		}
		var option = type.options[ type.selectedIndex ];
		var kind = option ? option.getAttribute( 'data-kind' ) : 'list';
		var firstVisible = null;
		var currentFits = false;
		Array.prototype.forEach.call( operator.options, function ( op ) {
			var fits = op.getAttribute( 'data-kind' ) === kind;
			op.hidden = ! fits;
			op.disabled = ! fits;
			if ( fits && ! firstVisible ) {
				firstVisible = op;
			}
			if ( fits && op.selected ) {
				currentFits = true;
			}
		} );
		if ( ! currentFits && firstVisible ) {
			firstVisible.selected = true;
		}
		value.type = 'number' === kind ? 'number' : 'text';
		value.step = 'number' === kind ? 'any' : '';
		value.placeholder = option ? option.getAttribute( 'data-hint' ) || '' : '';
	}

	function syncAll( scope ) {
		Array.prototype.forEach.call( scope.querySelectorAll( '.wps_wpr_ach_condition' ), syncCondition );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var wrapper = document.querySelector( '.wps_wpr_achievement_rules_wrapper' );
		if ( ! wrapper ) {
			return;
		}
		syncAll( wrapper );

		wrapper.addEventListener( 'change', function ( event ) {
			if ( event.target.classList.contains( 'wps_wpr_ach_cond_type' ) ) {
				syncCondition( event.target.closest( '.wps_wpr_ach_condition' ) );
			}
		} );

		wrapper.addEventListener( 'click', function ( event ) {
			var target = event.target;

			if ( target.classList.contains( 'wps_wpr_ach_add_rule' ) ) {
				event.preventDefault();
				var rule = fromTemplate( 'wps_wpr_ach_rule_template', { __RULE__: uniqueKey( 'n' ) } );
				if ( rule ) {
					wrapper.querySelector( '.wps_wpr_ach_rules' ).appendChild( rule );
					var content = rule.querySelector( '.wps_wpr_section_content' );
					if ( content ) {
						content.style.display = 'block';
					}
					var name = rule.querySelector( '.wps_wpr_ach_name' );
					if ( name ) {
						name.focus();
					}
				}
				return;
			}

			if ( target.classList.contains( 'wps_wpr_ach_add_condition' ) ) {
				event.preventDefault();
				var ruleEl = target.closest( '.wps_wpr_ach_rule' );
				var row = fromTemplate( 'wps_wpr_ach_condition_template', {
					__RULE__: ruleEl.getAttribute( 'data-rule-key' ),
					__COND__: uniqueKey( 'c' ),
				} );
				if ( row ) {
					ruleEl.querySelector( '.wps_wpr_ach_conditions' ).appendChild( row );
					syncCondition( row );
				}
				return;
			}

			if ( target.classList.contains( 'wps_wpr_ach_remove_condition' ) ) {
				event.preventDefault();
				target.closest( '.wps_wpr_ach_condition' ).remove();
				return;
			}

			if ( target.classList.contains( 'wps_wpr_ach_remove_rule' ) ) {
				event.preventDefault();
				target.closest( '.wps_wpr_ach_rule' ).remove();
			}
		} );
	} );
}() );
