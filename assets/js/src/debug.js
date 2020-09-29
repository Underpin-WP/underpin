$( document ).ready( () => {
	const debugTarget = $( '#wp-admin-bar-underpin_debugger' );
	const debugTabs = $( '#underpin-debug-bar-tabs a' );
	const debugBarWrap = $( '#debug-bar-wrap' );
	const debugClose = $( debugBarWrap ).find( '.debug-bar-close' );
	const debugEventLogs = $( debugBarWrap ).find( '.console-wrap' );
	const debugMenuItems = $( '#underpin-debug-bar-menu a' );
	const debugSections = $( '.debug-bar-section' );

	debugTarget.on( 'click', ( e ) => {
		e.preventDefault();
		debugBarWrap.toggle();
	} );

	debugClose.on( 'click', ( e ) => {
		e.preventDefault();
		debugBarWrap.hide();
	} );

	debugTabs.on( 'click', function( e ){
		e.preventDefault();
		const event = $( this ).data( 'event' );
		debugEventLogs.hide();
		debugTabs.removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' );
		$( '#' + event ).show();
	} );

	debugMenuItems.on( 'click', function( e ){
		e.preventDefault();
		const section = $( this ).data( 'section' );
		debugSections.hide();
		debugMenuItems.removeClass( 'section-active' );
		$( this ).addClass( 'section-active' );
		$( '#' + section ).show();
	} );

	$( document ).keyup( ( e ) => {
		if( e.key === 'Escape' ){
			debugBarWrap.hide();
		}
	} );
} );