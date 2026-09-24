/**
 * Activity Share Pro admin: Features / Content types saves, Analytics report.
 */
( function () {
	'use strict';

	var cfg = window.bpasProAdmin || {};
	var t = cfg.i18n || {};
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
				el.textContent = ( report.totals[ key ] || 0 ).toLocaleString();
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
			li.appendChild( cell( 'span', n.count.toLocaleString(), 'bpas-pro-bars__value' ) );
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
			tr.appendChild( cell( 'td', row.count.toLocaleString(), 'num' ) );
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
			pager.appendChild( cell( 'span', ( t.pageOf || '%1$d / %2$d' ).replace( '%1$d', page ).replace( '%2$d', report.pages ), 'bpas-pro-pager__label' ) );
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

	box.querySelector( '[data-bpas-csv]' ).addEventListener( 'click', function () {
		if ( ! last ) {
			return;
		}
		var quote = function ( v ) {
			return '"' + String( v ).replace( /"/g, '""' ) + '"';
		};
		var lines = [ [ t.item, t.type, t.shares, 'URL' ].map( quote ).join( ',' ) ];
		last.top.forEach( function ( row ) {
			lines.push( [ row.title, row.type, row.count, row.url ].map( quote ).join( ',' ) );
		} );
		var blob = new Blob( [ lines.join( '\n' ) ], { type: 'text/csv;charset=utf-8' } );
		var link = document.createElement( 'a' );
		link.href = URL.createObjectURL( blob );
		link.download = 'activity-share-' + box.querySelector( '[data-bpas-days]' ).value + '-days.csv';
		document.body.appendChild( link );
		link.click();
		link.remove();
	} );

	load();
}() );
