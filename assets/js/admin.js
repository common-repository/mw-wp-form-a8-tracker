jQuery( function( $ ) {

	$( '#mw_wp_form_a8_tracker_metabox' ).mw_wp_form_repeatable();

	$( '#mw_wp_form_a8_tracker_metabox .repeatable-boxes' ).sortable( {
		items : '> .repeatable-box',
		handle: '.sortable-icon-handle'
	} );

} );
