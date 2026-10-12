/**
 * Каталог лицензий (template-parts/license/catalog.php): поиск и фильтр по направлениям без перезагрузки.
 * Без JS работает то же самое через ссылки на разделы и GET-форму ?q= — сервер скрывает лишние карточки.
 *
 * Чип направления: фильтр на месте, адрес раздела в history (pushState), H1, подзаголовок, Title и крошки —
 * из data-атрибутов чипа. Ввод в поиске: фильтр сразу, ?q= в адресе (replaceState). Назад/вперёд — popstate.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-catalog]' );
	if ( ! root || ! window.history || ! window.URL ) {
		return;
	}

	var form = root.querySelector( '.catalog-search' );
	var input = root.querySelector( '#catalog-q' );
	var chips = Array.prototype.slice.call( root.querySelectorAll( '.filter-chips__link' ) );
	var cards = Array.prototype.slice.call( root.querySelectorAll( '.lic-card' ) );
	var count = root.querySelector( '[data-catalog-count]' );
	var live = root.querySelector( '[data-catalog-live]' );
	var empty = root.querySelector( '[data-catalog-empty]' );
	var h1 = root.querySelector( '[data-catalog-h1]' );
	var lead = root.querySelector( '[data-catalog-lead]' );
	var crumbs = root.querySelector( '.crumbs__list' );
	var topic = empty && empty.querySelector( 'input[name="message"]' );
	var topicTouched = false;
	var liveTimer = 0;

	var view = ( chips.filter( function ( c ) {
		return c.hasAttribute( 'aria-current' );
	} )[ 0 ] || { dataset: { view: '' } } ).dataset.view;

	// Та же нормализация, что sky_starter_search_norm() в PHP.
	function norm( s ) {
		return String( s ).toLowerCase().replace( /ё/g, 'е' ).replace( /\s+/g, ' ' ).trim();
	}

	function chipFor( slug ) {
		return chips.filter( function ( c ) {
			return c.dataset.view === slug;
		} )[ 0 ];
	}

	function apply() {
		var words = norm( input ? input.value : '' ).split( ' ' ).filter( Boolean );
		var shown = 0;

		cards.forEach( function ( card ) {
			var ok = ( ! view || card.dataset.regulator === view ) && words.every( function ( w ) {
				return card.dataset.search.indexOf( w ) !== -1;
			} );
			card.hidden = ! ok;
			shown += ok ? 1 : 0;
		} );

		var text = 'Найдено лицензий: ' + shown;
		count.textContent = text;
		// Экранному диктору — после паузы в наборе, а не на каждую букву.
		clearTimeout( liveTimer );
		liveTimer = setTimeout( function () {
			live.textContent = text;
		}, 500 );

		if ( empty ) {
			empty.hidden = shown > 0;
			if ( ! shown && topic && ! topicTouched && input ) {
				topic.value = input.value.trim();
			}
		}
	}

	function url( base ) {
		var u = new URL( base, location.href );
		var q = input ? input.value.trim() : '';
		if ( q ) {
			u.searchParams.set( 'q', q );
		} else {
			u.searchParams.delete( 'q' );
		}
		return u.pathname + u.search;
	}

	function crumb( label, href ) {
		var li = document.createElement( 'li' );
		var el = document.createElement( href ? 'a' : 'span' );
		li.className = crumbs.firstElementChild ? crumbs.firstElementChild.className : 'crumbs__item';
		el.textContent = label;
		if ( href ) {
			el.href = href;
		} else {
			el.setAttribute( 'aria-current', 'page' );
		}
		li.appendChild( el );
		return li;
	}

	// Направление: чипы, H1, подзаголовок, Title, крошки, адрес формы поиска.
	function setView( chip ) {
		view = chip.dataset.view;
		chips.forEach( function ( c ) {
			if ( c === chip ) {
				c.setAttribute( 'aria-current', 'page' );
			} else {
				c.removeAttribute( 'aria-current' );
			}
		} );
		h1.textContent = chip.dataset.h1;
		lead.textContent = chip.dataset.lead;
		document.title = chip.dataset.title;
		if ( form ) {
			form.action = chip.href;
		}
		if ( crumbs ) {
			var items = [ crumb( root.dataset.homeLabel, root.dataset.homeUrl ) ];
			if ( view ) {
				items.push( crumb( root.dataset.archiveLabel, root.dataset.archiveUrl ), crumb( chip.textContent ) );
			} else {
				items.push( crumb( root.dataset.archiveLabel ) );
			}
			crumbs.replaceChildren.apply( crumbs, items );
		}
	}

	chips.forEach( function ( chip ) {
		chip.addEventListener( 'click', function ( e ) {
			// Ctrl/Cmd-клик, средняя кнопка — обычное поведение ссылки (новая вкладка).
			if ( e.defaultPrevented || 0 !== e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ) {
				return;
			}
			e.preventDefault();
			if ( chip.dataset.view !== view ) {
				setView( chip );
				history.pushState( { view: view }, '', url( chip.href ) );
				apply();
			}
		} );
	} );

	if ( input ) {
		input.addEventListener( 'input', function () {
			apply();
			history.replaceState( { view: view }, '', url( location.pathname ) );
		} );
	}

	if ( form ) {
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			apply();
		} );
	}

	if ( topic ) {
		topic.addEventListener( 'input', function () {
			topicTouched = true;
		} );
	}

	// Назад/вперёд: направление — по адресу, запрос — из ?q=.
	window.addEventListener( 'popstate', function () {
		var path = location.pathname;
		var chip = chips.filter( function ( c ) {
			return new URL( c.href ).pathname === path;
		} )[ 0 ] || chipFor( '' );
		if ( input ) {
			input.value = new URL( location.href ).searchParams.get( 'q' ) || '';
		}
		if ( chip ) {
			setView( chip );
		}
		apply();
	} );

	history.replaceState( { view: view }, '', location.href );
	root.classList.add( 'is-enhanced' );
} )();
