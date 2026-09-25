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
		}, action ? 8000 : 5000 );
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

	function setStatus( text, isError ) {
		var s = q( '[data-bpas-dialog-status]' );
		if ( s ) {
			s.textContent = text || '';
			s.classList.toggle( 'is-error', !! isError );
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
			friendId: 0,
			friendName: ''
		};

		var title = q( '[data-bpas-dialog-title]' );
		var submit = q( '[data-bpas-dialog-submit]' );
		var form = q( '[data-bpas-dialog-form]' );
		if ( form && form.reset ) {
			form.reset();
		}
		setStatus( '' );
		noFriends( false );

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
			searchFriends( '' );
		} else if ( 'reposters' === mode ) {
			title.textContent = t.reposters;
			loadReposters();
		} else {
			// "Repost in this group" needs no picker: the title and the menu row already name the group.
			title.textContent = state.fixedGroup ? t.repostHere : ( state.groupsOnly ? t.repostGroup : t.repostComment );
			submit.textContent = t.repost;
			q( '[data-bpas-comment-label]' ).textContent = t.comment;
			show( 'friend', false );
			show( 'destination', ! state.fixedGroup && ! state.groupsOnly );
			show( 'group', state.groupsOnly );
			if ( state.groupsOnly ) {
				loadGroups( '' );
			}
		}

		if ( typeof dialog.showModal === 'function' ) {
			dialog.showModal();
		} else {
			dialog.setAttribute( 'open', '' );
		}
		mentionLists( dialog );
		// Start where the member types, not on the close button.
		var first = { send: '[data-bpas-friend-search]', compose: '[name="comment"]', reposters: '[data-bpas-reposters-list]', login: '[data-bpas-mode="login"] .bpas-pro-btn--primary' }[ mode ];
		first = first ? q( first ) : null;
		if ( first ) {
			first.focus();
		}
	}

	/*
	 * @mention suggestions (BuddyPress At.js) render in <body>, which sits under a modal
	 * dialog's top layer. Host them inside the dialog while it is open; At.js positions
	 * them with jQuery .offset(), which accounts for the new parent.
	 */
	function mentionLists( host ) {
		document.querySelectorAll( '.atwho-container' ).forEach( function ( el ) {
			host.appendChild( el );
		} );
	}

	function closeDialog() {
		if ( dialog && dialog.open ) {
			dialog.close();
		}
		mentionLists( document.body );
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
			var groups = res.groups || [];
			select.textContent = '';
			var prompt = document.createElement( 'option' );
			prompt.value = '';
			prompt.textContent = t.chooseGroup;
			select.appendChild( prompt );
			groups.forEach( function ( g ) {
				var opt = document.createElement( 'option' );
				opt.value = g.id;
				opt.textContent = g.name;
				select.appendChild( opt );
			} );
			if ( 1 === groups.length ) {
				select.value = groups[ 0 ].id;
			}
			empty.hidden = groups.length > 0 || !! search;
			select.hidden = ! groups.length && ! search;
			searchBox.hidden = ! res.has_more && ! search;
		}, function ( error ) {
			setStatus( message( error ), true );
		} );
	}

	/* ---------- friends ---------- */

	var friendTimer;
	function listNote( list, text ) {
		var li = document.createElement( 'li' );
		li.className = 'bpas-pro-options__empty';
		li.textContent = text;
		list.textContent = '';
		list.appendChild( li );
	}

	function searchFriends( term ) {
		var list = q( '[data-bpas-friend-list]' );
		state.friendId = 0;
		state.friendName = '';
		listNote( list, t.loading );
		api( '/me/friends?per_page=20&search=' + encodeURIComponent( term ) ).then( function ( res ) {
			list.textContent = '';
			var friends = res.friends || [];
			if ( ! friends.length ) {
				listNote( list, term ? t.noFriends : t.noFriendsYet );
				noFriends( ! term );
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
				var name = document.createElement( 'span' );
				name.className = 'bpas-pro-option__name';
				name.textContent = f.name;
				btn.appendChild( name );
				li.appendChild( btn );
				list.appendChild( li );
			} );
		}, function ( error ) {
			listNote( list, message( error ) );
		} );
	}

	// No friends at all: only the notice and Cancel remain; nothing to search, write or send.
	function noFriends( none ) {
		[ '[data-bpas-friend-search]', '#bpas-pro-friend-hint', 'label[for="bpas-pro-friend"]', '[data-bpas-dialog-submit]' ].forEach( function ( sel ) {
			var el = q( sel );
			if ( el ) {
				el.hidden = none;
			}
		} );
		show( 'comment', ! none );
	}

	function pickFriend( btn ) {
		state.friendId = parseInt( btn.getAttribute( 'data-friend-id' ), 10 );
		state.friendName = btn.textContent;
		dialog.querySelectorAll( '[data-friend-id]' ).forEach( function ( b ) {
			b.setAttribute( 'aria-selected', b === btn ? 'true' : 'false' );
		} );
		setStatus( '' );
	}

	/* ---------- reposters ---------- */

	function loadReposters() {
		var body = q( '.bpas-pro-dialog__body' );
		var list = document.createElement( 'ul' );
		list.className = 'bpas-pro-reposters';
		list.setAttribute( 'data-bpas-reposters-list', '' );
		list.tabIndex = -1;
		list.textContent = t.loading;
		body.hidden = true;
		body.parentNode.appendChild( list );
		api( '/activity/' + state.objectId + '/reposters?per_page=50' ).then( function ( res ) {
			list.textContent = '';
			if ( ! ( res.reposters || [] ).length ) {
				list.textContent = t.noReposters;
			}
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

	/* The Repost row flips to "Undo repost" and back, so the menu always tells the truth. */
	function setReposted( row, repostId ) {
		var name = row.querySelector( '.bpas-share__name' );
		if ( repostId ) {
			row.removeAttribute( 'data-bpas-repost' );
			row.setAttribute( 'data-bpas-undo', repostId );
			name.textContent = t.undoRepost;
		} else {
			row.removeAttribute( 'data-bpas-undo' );
			row.setAttribute( 'data-bpas-repost', '' );
			name.textContent = t.repost;
		}
	}

	function undoRepost( row, repostId ) {
		api( '/reshare/' + repostId, { method: 'DELETE' } ).then( function () {
			setReposted( row, 0 );
			toast( t.undone );
		}, function ( error ) {
			toast( message( error ) );
		} );
	}

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
			setReposted( trigger, res.id );
			toast( t.reposted, {
				label: t.undo,
				run: function () {
					undoRepost( trigger, res.id );
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
				setStatus( t.pickFriend, true );
				return;
			}
			request = api( '/send', { method: 'POST', data: { object_type: state.objectType, object_id: state.objectId, friend_id: state.friendId, note: comment } } );
		} else {
			var dest = state.fixedGroup || state.groupsOnly ? 'group' : ( ( q( '[name="destination"]:checked' ) || {} ).value || 'profile' );
			var groupId = parseInt( q( '[data-bpas-group-select]' ).value, 10 ) || state.fixedGroup || 0;
			if ( 'group' === dest && ! groupId ) {
				setStatus( t.pickGroup, true );
				return;
			}
			request = api( '/reshare', { method: 'POST', data: { object_type: state.objectType, object_id: state.objectId, destination: dest, group_id: groupId, comment: comment } } );
		}

		var label = submit.textContent;
		submit.disabled = true;
		submit.textContent = 'send' === state.mode ? t.sending : t.reposting;
		setStatus( '' );
		request.then( function ( res ) {
			closeDialog();
			toast( 'send' === state.mode ? t.sentTo.replace( '%s', state.friendName ) : t.reposted, res && res.url ? {
				label: t.view,
				run: function () {
					window.location.href = res.url;
				}
			} : null );
		}, function ( error ) {
			setStatus( message( error ), true );
		} ).finally( function () {
			submit.disabled = false;
			submit.textContent = label;
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var el = event.target;
		var trigger;

		if ( ( trigger = el.closest( '[data-bpas-repost]' ) ) ) {
			event.preventDefault();
			quickRepost( trigger );
		} else if ( ( trigger = el.closest( '[data-bpas-undo]' ) ) ) {
			event.preventDefault();
			closeMenu( trigger );
			undoRepost( trigger, parseInt( trigger.getAttribute( 'data-bpas-undo' ), 10 ) );
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
			pickFriend( trigger );
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
		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key && event.target.hasAttribute( 'data-bpas-friend-search' ) ) {
				event.preventDefault();
				var firstFriend = q( '[data-friend-id]' );
				if ( firstFriend ) {
					pickFriend( firstFriend );
					firstFriend.focus();
				}
			} else if ( 'Enter' === event.key && event.target.hasAttribute( 'data-bpas-group-search' ) ) {
				event.preventDefault();
			}
		} );
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
