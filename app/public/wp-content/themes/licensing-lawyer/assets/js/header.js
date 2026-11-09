/**
 * Мобильное меню шапки: бургер открывает панель (DESIGN.md §5 «Шапка»).
 * Без JS бургер скрыт (hidden), а меню показано списком — сайт остаётся проходимым.
 */
( function () {
	'use strict';

	// «Получить консультацию» и т. п. ведут на #form. Если формы на странице нет — на форму в «Контактах».
	var fallback = document.body.dataset.formFallback;
	if ( fallback && ! document.getElementById( 'form' ) ) {
		document.querySelectorAll( 'a[href="#form"]' ).forEach( function ( a ) {
			a.href = fallback;
		} );
	}

	var header = document.getElementById( 'site-header' );
	var toggle = header && header.querySelector( '.site-header__toggle' );
	var nav = document.getElementById( 'site-nav' );
	if ( ! toggle || ! nav ) {
		return;
	}

	var desktop = window.matchMedia( '(min-width: 1024px)' );

	header.classList.add( 'has-js' );
	toggle.hidden = false;

	function setOpen( open, returnFocus ) {
		header.classList.toggle( 'is-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', open ? 'Закрыть меню' : 'Открыть меню' );
		if ( ! open && returnFocus ) {
			toggle.focus();
		}
	}

	toggle.addEventListener( 'click', function () {
		setOpen( ! header.classList.contains( 'is-open' ) );
	} );

	// Esc закрывает и возвращает фокус на бургер.
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && header.classList.contains( 'is-open' ) ) {
			setOpen( false, true );
		}
	} );

	// Клик мимо панели закрывает её.
	document.addEventListener( 'click', function ( e ) {
		if ( header.classList.contains( 'is-open' ) && ! header.contains( e.target ) ) {
			setOpen( false );
		}
	} );

	// Переход по якорю (#form) — панель не должна закрывать форму.
	nav.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( 'a' ) ) {
			setOpen( false );
		}
	} );

	// На десктопе меню всегда в строку — сбросить открытое состояние.
	desktop.addEventListener( 'change', function () {
		setOpen( false );
	} );
} )();
