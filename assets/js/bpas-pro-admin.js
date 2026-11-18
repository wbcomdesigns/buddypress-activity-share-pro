/**
 * Activity Share Pro admin: Features / Content types saves, Analytics report.
 */
( function () {
	'use strict';

	var cfg = window.bpasProAdmin || {};
	var t = cfg.i18n || {};
	var numberFormat;
	try {
		numberFormat = new Intl.NumberFormat( cfg.locale );
	} catch ( e ) {
		numberFormat = new Intl.NumberFormat();
	}

	function num( n ) {
		return numberFormat.format( n || 0 );
	}
	var toastEl = document.querySelector( '.bpas-admin__toast' );
	var toastTimer;

	function toast( message, isError ) {
		if ( ! toastEl ) {
			return;
		}
		toastEl.textContent = message;
		toastEl.classList.toggle( 'is-error', !! isError );
		toastEl.hidden = false;
		window.clearTimeout( toastTimer );
		toastTimer = window.setTimeout( function () {
			toastEl.hidden = true;
		}, 3000 );
	}

	function api( path, options ) {
		return window.wp.apiFetch( Object.assign( { path: cfg.ns + path }, options || {} ) );
	}

	/* ---------- settings forms ---------- */

	// A placement only matters for a ticked content type.
	document.addEventListener( 'change', function ( event ) {
		var input = event.target;
		if ( input.hasAttribute && input.hasAttribute( 'data-bpas-type' ) ) {
			var select = document.querySelector( '[data-bpas-placement="' + input.value + '"]' );
			if ( select ) {
				select.disabled = ! input.checked;
			}
		}
	} );

	document.querySelectorAll( '[data-bpas-pro-form]' ).forEach( function ( form ) {
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			var data = {};
			if ( 'features' === form.getAttribute( 'data-bpas-pro-form' ) ) {
				data.features = {};
				form.querySelectorAll( 'input[type="checkbox"]' ).forEach( function ( input ) {
					data.features[ input.name ] = input.checked;
				} );
			} else {
				data.content_types = {};
				form.querySelectorAll( '[data-bpas-type]:checked' ).forEach( function ( input ) {
					var select = form.querySelector( '[data-bpas-placement="' + input.value + '"]' );
					data.content_types[ input.value ] = select ? select.value : 'below';
				} );
			}
			var button = form.querySelector( '[type="submit"]' );
			button.disabled = true;
			api( '/pro-settings', { method: 'PATCH', data: data } ).then( function () {
				toast( t.saved );
			}, function ( error ) {
				toast( ( error && error.message ) || t.failed, true );
			} ).finally( function () {
				button.disabled = false;
			} );
		} );
	} );

	/* ---------- analytics ---------- */

	var box = document.querySelector( '[data-bpas-analytics]' );
	if ( ! box ) {
		return;
	}

	var page = 1;
	var last = null;
	var status = box.querySelector( '[data-bpas-analytics-status]' );
	var tbody = box.querySelector( '[data-bpas-top] tbody' );
	var pager = box.querySelector( '[data-bpas-pager]' );
	var bars = box.querySelector( '[data-bpas-networks]' );

	function cell( tag, text, cls ) {
		var el = document.createElement( tag );
		el.textContent = text;
		if ( cls ) {
			el.className = cls;
		}
		return el;
	}

	function render( report ) {
		last = report;
		[ 'share', 'repost', 'send', 'visit' ].forEach( function ( key ) {
			var el = box.querySelector( '[data-bpas-stat="' + key + '"]' );
			if ( el ) {
				el.textContent = num( report.totals[ key ] );
			}
		} );

		bars.textContent = '';
		if ( ! report.networks.length ) {
			bars.appendChild( cell( 'li', t.noNetworks, 'bpas-pro-bars__empty' ) );
		}
		var max = report.networks.reduce( function ( m, n ) {
			return Math.max( m, n.count );
		}, 0 );
		report.networks.forEach( function ( n ) {
			var li = document.createElement( 'li' );
			li.appendChild( cell( 'span', n.network, 'bpas-pro-bars__label' ) );
			var track = cell( 'span', '', 'bpas-pro-bars__track' );
			var fill = cell( 'span', '', 'bpas-pro-bars__fill' );
			fill.style.inlineSize = ( max ? Math.round( ( n.count / max ) * 100 ) : 0 ) + '%';
			track.appendChild( fill );
			li.appendChild( track );
			li.appendChild( cell( 'span', num( n.count ), 'bpas-pro-bars__value' ) );
			bars.appendChild( li );
		} );

		tbody.textContent = '';
		report.top.forEach( function ( row ) {
			var tr = document.createElement( 'tr' );
			var td = document.createElement( 'td' );
			if ( row.url ) {
				var a = cell( 'a', row.title );
				a.href = row.url;
				a.target = '_blank';
				a.rel = 'noopener';
				td.appendChild( a );
			} else {
				td.textContent = row.title;
			}
			tr.appendChild( td );
			tr.appendChild( cell( 'td', 'post' === row.type ? t.post : t.activity ) );
			tr.appendChild( cell( 'td', num( row.count ), 'num' ) );
			tbody.appendChild( tr );
		} );

		status.textContent = report.top.length ? '' : t.empty;

		pager.textContent = '';
		if ( report.pages > 1 ) {
			var prev = cell( 'button', t.prev, 'button' );
			prev.type = 'button';
			prev.disabled = page <= 1;
			prev.addEventListener( 'click', function () {
				page--;
				load();
			} );
			var next = cell( 'button', t.next, 'button' );
			next.type = 'button';
			next.disabled = ! report.has_more;
			next.addEventListener( 'click', function () {
				page++;
				load();
			} );
			pager.appendChild( prev );
			pager.appendChild( cell( 'span', t.pageOf.replace( '%1$s', num( page ) ).replace( '%2$s', num( report.pages ) ), 'bpas-pro-pager__label' ) );
			pager.appendChild( next );
		}
	}

	function load() {
		var days = box.querySelector( '[data-bpas-days]' ).value;
		var type = box.querySelector( '[data-bpas-object-type]' ).value;
		status.textContent = t.loading;
		box.setAttribute( 'aria-busy', 'true' );
		api( '/analytics?days=' + days + '&object_type=' + encodeURIComponent( type ) + '&page=' + page + '&per_page=20' ).then( render, function () {
			status.textContent = t.error;
		} ).finally( function () {
			box.removeAttribute( 'aria-busy' );
		} );
	}

	box.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( '[data-bpas-days], [data-bpas-object-type]' ) ) {
			page = 1;
			load();
		}
	} );

	/*
	 * CSV of the whole "Most shared" list for the chosen period and content, fetched 100 rows at a
	 * time. Titles are member-written: a leading = + - @ is neutralised so spreadsheets never run it.
	 */
	var quote = function ( v ) {
		v = String( v );
		if ( /^[=+\-@\t\r]/.test( v ) ) {
			v = "'" + v;
		}
		return '"' + v.replace( /"/g, '""' ) + '"';
	};

	box.querySelector( '[data-bpas-csv]' ).addEventListener( 'click', function () {
		var button = this;
		var days = box.querySelector( '[data-bpas-days]' ).value;
		var type = box.querySelector( '[data-bpas-object-type]' ).value;
		var lines = [ [ t.item, t.type, t.shares, t.url ].map( quote ).join( ',' ) ];
		var label = button.textContent;
		button.disabled = true;
		button.textContent = t.exporting;

		function fetchPage( p ) {
			return api( '/analytics?days=' + days + '&object_type=' + encodeURIComponent( type ) + '&page=' + p + '&per_page=100' ).then( function ( res ) {
				res.top.forEach( function ( row ) {
					lines.push( [ row.title, 'post' === row.type ? t.post : t.activity, row.count, row.url ].map( quote ).join( ',' ) );
				} );
				return res.has_more ? fetchPage( p + 1 ) : null;
			} );
		}

		fetchPage( 1 ).then( function () {
			var blob = new Blob( [ '\ufeff' + lines.join( '\r\n' ) ], { type: 'text/csv;charset=utf-8' } );
			var link = document.createElement( 'a' );
			link.href = URL.createObjectURL( blob );
			link.download = 'activity-share-' + days + '-days.csv';
			document.body.appendChild( link );
			link.click();
			link.remove();
		}, function () {
			toast( t.failed, true );
		} ).finally( function () {
			button.disabled = false;
			button.textContent = label;
		} );
	} );

	load();
}() );
