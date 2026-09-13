( function() {
	'use strict';

	var config = window.julieprSimilarPostsSelectedPosts;
	var searchInput = document.getElementById( 'juliepr-similar-posts-search-posts' );
	var results = document.getElementById( 'juliepr-similar-posts-search-results' );
	var selectedList = document.getElementById( 'juliepr-similar-posts-selected-posts' );
	var selectedInput = document.getElementById( 'juliepr-similar-posts-selected-post-ids' );
	var searchTimeout;

	if ( ! config || ! searchInput || ! results || ! selectedList || ! selectedInput ) {
		return;
	}

	function selectedIds() {
		return selectedInput.value ? selectedInput.value.split( ' ' ) : [];
	}

	function syncSelectedIds() {
		var items = selectedList.querySelectorAll( '[data-post-id]' );
		var ids = [];

		items.forEach( function( item ) {
			ids.push( item.getAttribute( 'data-post-id' ) );
		} );

		selectedInput.value = ids.join( ' ' );
	}

	function addSelectedPost( post ) {
		if ( selectedIds().indexOf( String( post.id ) ) !== -1 ) {
			return;
		}

		var item = document.createElement( 'li' );
		var title = document.createElement( 'span' );
		var remove = document.createElement( 'button' );

		item.setAttribute( 'data-post-id', post.id );
		title.textContent = post.title;
		remove.type = 'button';
		remove.className = 'button-link-delete';
		remove.textContent = config.remove;
		remove.setAttribute( 'aria-label', config.remove + ': ' + post.title );
		item.appendChild( title );
		item.appendChild( remove );
		selectedList.appendChild( item );
		syncSelectedIds();
	}

	function renderResults( posts ) {
		results.innerHTML = '';

		if ( ! posts.length ) {
			results.textContent = config.noResults;
			return;
		}

		posts.forEach( function( post ) {
			if ( selectedIds().indexOf( String( post.id ) ) !== -1 ) {
				return;
			}

			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'button';
			button.textContent = post.title;
			button.addEventListener( 'click', function() {
				addSelectedPost( post );
				searchInput.value = '';
				results.innerHTML = '';
				searchInput.focus();
			} );
			results.appendChild( button );
		} );

		if ( ! results.children.length ) {
			results.textContent = config.noResults;
		}
	}

	function searchPosts() {
		var search = searchInput.value.trim();

		results.innerHTML = '';
		if ( search.length < config.minimumLength ) {
			return;
		}

		results.textContent = config.loading;
		var request = new XMLHttpRequest();
		request.open( 'GET', config.ajaxUrl + '?action=juliepr_similar_posts_search_selected_posts&nonce=' + encodeURIComponent( config.nonce ) + '&post_id=' + encodeURIComponent( config.postId ) + '&search=' + encodeURIComponent( search ), true );
		request.onload = function() {
			if ( search !== searchInput.value.trim() ) {
				return;
			}

			if ( 200 !== request.status ) {
				results.textContent = config.noResults;
				return;
			}

			try {
				var response = JSON.parse( request.responseText );
				renderResults( response.success ? response.data : [] );
			} catch ( error ) {
				results.textContent = config.noResults;
			}
		};
		request.send();
	}

	searchInput.addEventListener( 'input', function() {
		window.clearTimeout( searchTimeout );
		searchTimeout = window.setTimeout( searchPosts, 250 );
	} );

	selectedList.addEventListener( 'click', function( event ) {
		if ( 'BUTTON' === event.target.tagName ) {
			event.target.parentNode.remove();
			syncSelectedIds();
		}
	} );
}() );
