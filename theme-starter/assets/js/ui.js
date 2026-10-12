/**
 * Интерактив компонентов (DESIGN.md §5, §10). Без JS всё содержимое видно.
 *   [data-tabs]      — doc-tabs, каталог на главной: вкладки, ← → ↑ ↓ Home End, автоматическая активация;
 *   [data-accordion] — faq: кнопки с aria-expanded, первый вопрос открыт;
 *   [data-tariffs]   — tariffs с segments: кнопки aria-pressed «тип компании» подсвечивают тариф.
 */
( function () {
	'use strict';

	/* ── Вкладки ── */
	function initTabs( root ) {
		var list = root.querySelector( '[role="tablist"]' );
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '[role="tab"]' ) );
		if ( ! list || ! tabs.length ) {
			return;
		}

		function select( tab, focus ) {
			tabs.forEach( function ( t ) {
				var on = t === tab;
				var panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
				t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				t.tabIndex = on ? 0 : -1;
				if ( panel ) {
					panel.hidden = ! on;
				}
			} );
			if ( focus ) {
				tab.focus();
			}
		}

		root.classList.add( 'is-enhanced' ); // прячет заголовки панелей — их роль берут вкладки
		// Ссылки в спрятанных заголовках не должны ловить Tab: фокус на невидимом элементе.
		root.querySelectorAll( '[data-tabs-heading] a' ).forEach( function ( a ) {
			a.tabIndex = -1;
		} );
		list.hidden = false;
		select( tabs.filter( function ( t ) {
			return 'true' === t.getAttribute( 'aria-selected' );
		} )[ 0 ] || tabs[ 0 ], false );

		list.addEventListener( 'click', function ( e ) {
			var tab = e.target.closest( '[role="tab"]' );
			if ( tab ) {
				select( tab, false );
			}
		} );

		list.addEventListener( 'keydown', function ( e ) {
			var i = tabs.indexOf( document.activeElement );
			if ( i < 0 ) {
				return;
			}
			// Вертикальные вкладки (каталог на главной) — те же клавиши плюс ↑ ↓.
			var next = {
				ArrowRight: tabs[ ( i + 1 ) % tabs.length ],
				ArrowDown: tabs[ ( i + 1 ) % tabs.length ],
				ArrowLeft: tabs[ ( i - 1 + tabs.length ) % tabs.length ],
				ArrowUp: tabs[ ( i - 1 + tabs.length ) % tabs.length ],
				Home: tabs[ 0 ],
				End: tabs[ tabs.length - 1 ],
			}[ e.key ];
			if ( next ) {
				e.preventDefault();
				select( next, true );
			}
		} );
	}

	/* ── Аккордеон ── */
	function initAccordion( root ) {
		var toggles = Array.prototype.slice.call( root.querySelectorAll( '[aria-controls][aria-expanded]' ) );

		function set( btn, open ) {
			var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( panel ) {
				panel.hidden = ! open;
			}
		}

		// Открыт только первый вопрос (DESIGN.md §5).
		toggles.forEach( function ( btn, i ) {
			set( btn, 0 === i );
			btn.addEventListener( 'click', function () {
				set( btn, 'true' !== btn.getAttribute( 'aria-expanded' ) );
			} );
		} );
	}

	/* ── Тарифы: «тип компании» подсвечивает подходящий тариф ── */
	function initTariffs( root ) {
		var sw = root.querySelector( '[data-tariff-switch]' );
		var btns = Array.prototype.slice.call( root.querySelectorAll( '.tariff-switch__btn' ) );
		var hint = root.querySelector( '.tariff-switch__hint' );
		var items = Array.prototype.slice.call( root.querySelectorAll( '.tariffs__item' ) );
		if ( ! sw || ! btns.length ) {
			return;
		}

		function pick( btn ) {
			var n = parseInt( btn.dataset.featured, 10 );
			btns.forEach( function ( b ) {
				b.setAttribute( 'aria-pressed', b === btn ? 'true' : 'false' );
			} );
			hint.textContent = btn.dataset.hint || '';
			items.forEach( function ( item, i ) {
				var on = i + 1 === n;
				var link = item.querySelector( '.tariffs__button' );
				var flag = item.querySelector( '.tariffs__flag' );
				item.classList.toggle( 'tariffs__item--featured', on );
				if ( link ) {
					link.classList.toggle( 'btn--light', on );
					link.classList.toggle( 'btn--secondary', ! on );
				}
				if ( flag ) {
					flag.hidden = ! on;
				}
			} );
		}

		sw.hidden = false;
		btns.forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				pick( b );
			} );
		} );
		pick( btns.filter( function ( b ) {
			return 'true' === b.getAttribute( 'aria-pressed' );
		} )[ 0 ] || btns[ 0 ] );
	}

	document.querySelectorAll( '[data-tabs]' ).forEach( initTabs );
	document.querySelectorAll( '[data-tariffs]' ).forEach( initTariffs );
	document.querySelectorAll( '[data-accordion]' ).forEach( initAccordion );
} )();
