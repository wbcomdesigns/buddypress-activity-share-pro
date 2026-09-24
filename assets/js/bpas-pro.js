/**
 * Activity Share Pro - repost, send to a friend, who reposted, share tracking.
 * Document-delegated (works for activity BuddyPress appends by AJAX). REST via wp.apiFetch.
 */
( function () {
	'use strict';

	var cfg = window.bpasPro || {};
	var t = cfg.i18n || {};
	var ns = '/' + ( cfg.ns || 'bpas/v1' );
	var dialog = document.querySelector( '[data-bpas-dialog]' );
	var toastEl = document.querySelector( '[data-bpas-toast]' );
	var state = {};
	var toastTimer;

	function api( path, options ) {
		return window.wp.apiFetch( Object.assign( { path: ns + path }, options || {} ) );
	}

	function toast( message, action ) {
		if ( ! toastEl ) {
			return;
		}
		toastEl.textContent = message;
		if ( action ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'bpas-pro-toast__action';
			btn.textContent = action.label;
			btn.addEventListener( 'click', function () {
				toastEl.hidden = true;
				action.run();
			} );
			toastEl.appendChild( document.createTextNode( ' ' ) );
			toastEl.appendChild( btn );
		}
		toastEl.hidden = false;
		window.clearTimeout( toastTimer );
		toastTimer = window.setTimeout( function () {
			toastEl.hidden = true;
		}, action ? 8000 : 3500 );
	}

	function closeMenu( el ) {
		var menu = el.closest( 'details.bpas-share' );
		if ( menu ) {
			menu.open = false;
		}
	}

	function message( error ) {
		return ( error && error.message ) || t.failed;
	}

	/* ---------- dialog ---------- */

	function q( sel ) {
		return dialog ? dialog.querySelector( sel ) : null;
	}

	function setStatus( text ) {
		var s = q( '[data-bpas-dialog-status]' );
		if ( s ) {
			s.textContent = text || '';
		}
	}

	function show( field, visible ) {
		var el = q( '[data-bpas-field="' + field + '"]' );
		if ( el ) {
			el.hidden = ! visible;
		}
	}

	function openDialog( mode, trigger ) {
		if ( ! dialog ) {
			return;
		}
		state = {
			mode: mode,
			trigger: trigger,
			objectType: trigger.getAttribute( 'data-bpas-object' ),
			objectId: parseInt( trigger.getAttribute( 'data-bpas-object-id' ), 10 ) || 0,
			fixedGroup: parseInt( trigger.getAttribute( 'data-bpas-group' ) || '0', 10 ),
			groupsOnly: trigger.hasAttribute( 'data-bpas-groups-only' ),
			friendId: 0
		};

		var title = q( '[data-bpas-dialog-title]' );
		var submit = q( '[data-bpas-dialog-submit]' );
		var form = q( '[data-bpas-dialog-form]' );
		if ( form && form.reset ) {
			form.reset();
		}
		setStatus( '' );

		if ( 'login' === mode ) {
			if ( title ) {
				title.textContent = t.login;
			}
		} else if ( 'send' === mode ) {
			title.textContent = t.send;
			submit.textContent = t.sendBtn;
			q( '[data-bpas-comment-label]' ).textContent = t.note;
			show( 'destination', false );
			show( 'group', false );
			show( 'friend', true );
			q( '[data-bpas-friend-list]' ).textContent = '';
		} else if ( 'reposters' === mode ) {
			title.textContent = t.reposters;
			loadReposters();
		} else {
			var groupMode = state.fixedGroup || state.groupsOnly;
			title.textContent = state.groupsOnly ? t.repostGroup : t.repostComment;
			submit.textContent = t.repost;
			q( '[data-bpas-comment-label]' ).textContent = t.comment;
			show( 'friend', false );
			show( 'destination', ! groupMode );
			show( 'group', !! groupMode );
			if ( groupMode ) {
				loadGroups( '' );
			}
		}

		if ( typeof dialog.showModal === 'function' ) {
			dialog.showModal();
		} else {
			dialog.setAttribute( 'open', '' );
		}
	}

	function closeDialog() {
		if ( dialog && dialog.open ) {
			dialog.close();
		}
		var body = q( '[data-bpas-reposters-list]' );
		if ( body ) {
			body.remove();
		}
		if ( state.trigger ) {
			var toggle = state.trigger.closest( 'details.bpas-share' );
			var summary = toggle && toggle.querySelector( 'summary' );
			if ( summary ) {
				summary.focus();
			}
		}
	}

	/* ---------- groups ---------- */

	function loadGroups( search ) {
		var select = q( '[data-bpas-group-select]' );
		var empty = q( '[data-bpas-group-empty]' );
		var searchBox = q( '[data-bpas-group-search]' );
		select.textContent = '';
		var loadingOpt = document.createElement( 'option' );
		loadingOpt.value = '';
		loadingOpt.textContent = t.loading;
		select.appendChild( loadingOpt );
		api( '/me/groups?per_page=50&search=' + encodeURIComponent( search || '' ) ).then( function ( res ) {
			select.textContent = '';
			( res.groups || [] ).forEach( function ( g ) {
				if ( state.fixedGroup && g.id !== state.fixedGroup ) {
					return;
				}
				var opt = document.createElement( 'option' );
				opt.value = g.id;
				opt.textContent = g.name;
				select.appendChild( opt );
			} );
			if ( state.fixedGroup && ! select.options.length ) {
				var only = document.createElement( 'option' );
				only.value = state.fixedGroup;
				only.textContent = '#' + state.fixedGroup;
				select.appendChild( only );
			}
			empty.hidden = select.options.length > 0;
			select.hidden = select.options.length === 0;
			searchBox.hidden = ! res.has_more && ! search;
			select.disabled = !! state.fixedGroup;
		}, function ( error ) {
			setStatus( message( error ) );
		} );
	}

	/* ---------- friends ---------- */

	var friendTimer;
	function searchFriends( term ) {
		var list = q( '[data-bpas-friend-list]' );
		api( '/me/friends?per_page=20&search=' + encodeURIComponent( term ) ).then( function ( res ) {
			list.textContent = '';
			var friends = res.friends || [];
			if ( ! friends.length ) {
				var none = document.createElement( 'li' );
				none.className = 'bpas-pro-options__empty';
				none.textContent = t.noFriends;
				list.appendChild( none );
				return;
			}
			friends.forEach( function ( f ) {
				var li = document.createElement( 'li' );
				var btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'bpas-pro-option';
				btn.setAttribute( 'role', 'option' );
				btn.setAttribute( 'aria-selected', 'false' );
				btn.setAttribute( 'data-friend-id', f.id );
				if ( f.avatar ) {
					var img = document.createElement( 'img' );
					img.src = f.avatar;
					img.alt = '';
					img.width = 24;
					img.height = 24;
					btn.appendChild( img );
				}
				btn.appendChild( document.createTextNode( f.name ) );
				li.appendChild( btn );
				list.appendChild( li );
			} );
		}, function ( error ) {
			setStatus( message( error ) );
		} );
	}

	/* ---------- reposters ---------- */

	function loadReposters() {
		var body = q( '.bpas-pro-dialog__body' );
		var list = document.createElement( 'ul' );
		list.className = 'bpas-pro-reposters';
		list.setAttribute( 'data-bpas-reposters-list', '' );
		list.textContent = t.loading;
		body.hidden = true;
		body.parentNode.appendChild( list );
		api( '/activity/' + state.objectId + '/reposters?per_page=50' ).then( function ( res ) {
			list.textContent = '';
			( res.reposters || [] ).forEach( function ( m ) {
				var li = document.createElement( 'li' );
				var a = document.createElement( 'a' );
				a.href = m.url;
				if ( m.avatar ) {
					var img = document.createElement( 'img' );
					img.src = m.avatar;
					img.alt = '';
					img.width = 32;
					img.height = 32;
					a.appendChild( img );
				}
				a.appendChild( document.createTextNode( m.name ) );
				li.appendChild( a );
				list.appendChild( li );
			} );
		}, function ( error ) {
			list.textContent = message( error );
		} );
	}

	/* ---------- actions ---------- */

	function quickRepost( trigger ) {
		closeMenu( trigger );
		api( '/reshare', {
			method: 'POST',
			data: {
				object_type: trigger.getAttribute( 'data-bpas-object' ),
				object_id: parseInt( trigger.getAttribute( 'data-bpas-object-id' ), 10 ),
				destination: 'profile'
			}
		} ).then( function ( res ) {
			toast( t.reposted, {
				label: t.undo,
				run: function () {
					api( '/reshare/' + res.id, { method: 'DELETE' } ).then( function () {
						toast( t.undone );
					}, function ( error ) {
						toast( message( error ) );
					} );
				}
			} );
		}, function ( error ) {
			toast( message( error ) );
		} );
	}

	function submitDialog() {
		var submit = q( '[data-bpas-dialog-submit]' );
		var comment = q( '[name="comment"]' ).value;
		var request;

		if ( 'send' === state.mode ) {
			if ( ! state.friendId ) {
				setStatus( t.pickFriend );
				return;
			}
			request = api( '/send', { method: 'POST', data: { object_type: state.objectType, object_id: state.objectId, friend_id: state.friendId, note: comment } } );
		} else {
			var dest = state.fixedGroup || state.groupsOnly ? 'group' : ( ( q( '[name="destination"]:checked' ) || {} ).value || 'profile' );
			var groupId = parseInt( q( '[data-bpas-group-select]' ).value, 10 ) || state.fixedGroup || 0;
			if ( 'group' === dest && ! groupId ) {
				setStatus( t.pickGroup );
				return;
			}
			request = api( '/reshare', { method: 'POST', data: { object_type: state.objectType, object_id: state.objectId, destination: dest, group_id: groupId, comment: comment } } );
		}

		submit.disabled = true;
		setStatus( t.working );
		request.then( function () {
			closeDialog();
			toast( 'send' === state.mode ? t.sent : t.reposted );
		}, function ( error ) {
			setStatus( message( error ) );
		} ).finally( function () {
			submit.disabled = false;
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var el = event.target;
		var trigger;

		if ( ( trigger = el.closest( '[data-bpas-repost]' ) ) ) {
			event.preventDefault();
			quickRepost( trigger );
		} else if ( ( trigger = el.closest( '[data-bpas-compose]' ) ) ) {
			event.preventDefault();
			closeMenu( trigger );
			openDialog( 'compose', trigger );
		} else if ( ( trigger = el.closest( '[data-bpas-send]' ) ) ) {
			event.preventDefault();
			closeMenu( trigger );
			openDialog( 'send', trigger );
		} else if ( ( trigger = el.closest( '[data-bpas-login]' ) ) ) {
			event.preventDefault();
			closeMenu( trigger );
			openDialog( 'login', trigger );
		} else if ( ( trigger = el.closest( '[data-bpas-reposters]' ) ) ) {
			event.preventDefault();
			closeMenu( trigger );
			openDialog( 'reposters', trigger );
		} else if ( el.closest( '[data-bpas-dialog-close]' ) ) {
			event.preventDefault();
			closeDialog();
		} else if ( ( trigger = el.closest( '[data-friend-id]' ) ) ) {
			state.friendId = parseInt( trigger.getAttribute( 'data-friend-id' ), 10 );
			dialog.querySelectorAll( '[data-friend-id]' ).forEach( function ( b ) {
				b.setAttribute( 'aria-selected', b === trigger ? 'true' : 'false' );
			} );
		} else if ( dialog && el === dialog ) {
			closeDialog(); // Backdrop click.
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( dialog && dialog.contains( event.target ) && 'destination' === event.target.name ) {
			var isGroup = 'group' === event.target.value;
			show( 'group', isGroup );
			if ( isGroup ) {
				loadGroups( '' );
			}
		}
	} );

	document.addEventListener( 'input', function ( event ) {
		if ( ! dialog || ! dialog.contains( event.target ) ) {
			return;
		}
		if ( event.target.hasAttribute( 'data-bpas-friend-search' ) ) {
			window.clearTimeout( friendTimer );
			var term = event.target.value.trim();
			friendTimer = window.setTimeout( function () {
				searchFriends( term );
			}, 250 );
		} else if ( event.target.hasAttribute( 'data-bpas-group-search' ) ) {
			window.clearTimeout( friendTimer );
			var groupTerm = event.target.value.trim();
			friendTimer = window.setTimeout( function () {
				loadGroups( groupTerm );
			}, 250 );
		}
	} );

	if ( dialog ) {
		dialog.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			submitDialog();
		} );
		dialog.addEventListener( 'close', function () {
			var body = q( '.bpas-pro-dialog__body' );
			if ( body ) {
				body.hidden = false;
			}
		} );
	}

	/* ---------- share tracking (only when the owner enabled analytics) ---------- */

	if ( cfg.track ) {
		document.addEventListener( 'bpas:share', function ( event ) {
			var d = event.detail || {};
			if ( ! d.id || ! d.network ) {
				return;
			}
			var body = JSON.stringify( { object_type: d.type, object_id: d.id, network: d.network } );
			var url = cfg.root + ( cfg.ns || 'bpas/v1' ) + '/track';
			if ( navigator.sendBeacon ) {
				navigator.sendBeacon( url, new Blob( [ body ], { type: 'application/json' } ) );
			}
		} );
	}
}() );
