/**
 * Форма заявки (template-parts/components/lead-form.php): маска телефона, проверка полей,
 * отправка без перезагрузки, состояния «ошибка поля», «отправка», «успех».
 * Без JS форма работает обычным POST — сервер проверяет те же поля (inc/lead-form.php).
 * Тексты приходят из PHP: window.skyLeadForm.messages.
 */
( function () {
	'use strict';

	var cfg = window.skyLeadForm || {};
	var msg = cfg.messages || {};

	/* ── Маска +7 (XXX) XXX-XX-XX ── */

	// 10 цифр номера без кода страны. Ведущие 7 или 8 — код страны.
	function national( value ) {
		var d = String( value ).replace( /\D/g, '' );
		if ( '7' === d.charAt( 0 ) || '8' === d.charAt( 0 ) ) {
			d = d.slice( 1 );
		}
		return d.slice( 0, 10 );
	}

	function format( n ) {
		if ( ! n ) {
			return '';
		}
		var out = '+7 (' + n.slice( 0, 3 );
		if ( n.length > 3 ) {
			out += ') ' + n.slice( 3, 6 );
		}
		if ( n.length > 6 ) {
			out += '-' + n.slice( 6, 8 );
		}
		if ( n.length > 8 ) {
			out += '-' + n.slice( 8, 10 );
		}
		return out;
	}

	// Позиция в отформатированной строке сразу после count цифр номера («+7» не считается).
	function caretAfter( formatted, count ) {
		if ( 0 === count ) {
			return Math.min( 4, formatted.length );
		}
		for ( var i = 2, seen = 0; i < formatted.length; i++ ) {
			if ( /\d/.test( formatted.charAt( i ) ) && ++seen === count ) {
				return i + 1;
			}
		}
		return formatted.length;
	}

	function initMask( input ) {
		var prev = national( input.value );
		var codeTyped = '' !== prev; // первая набранная 7 или 8 — код страны («8 912…»), один раз

		input.addEventListener( 'input', function ( e ) {
			var raw = input.value;
			var caret = input.selectionStart === null ? raw.length : input.selectionStart;
			var digits = raw.replace( /\D/g, '' );
			var hasCode = '7' === digits.charAt( 0 ) || '8' === digits.charAt( 0 );
			var before = raw.slice( 0, caret ).replace( /\D/g, '' ).length - ( hasCode ? 1 : 0 );
			var n = national( raw );

			// Backspace по скобке, пробелу или дефису: удалить цифру перед ними.
			if ( 'deleteContentBackward' === e.inputType && n === prev && before > 0 ) {
				n = n.slice( 0, before - 1 ) + n.slice( before );
				before--;
			}

			if ( '' === prev && ! codeTyped && ( '7' === n || '8' === n ) && 'insertText' === e.inputType ) {
				n = '';
				before = 0;
			}
			codeTyped = codeTyped || '' !== n || 'insertText' === e.inputType;

			before = Math.max( 0, Math.min( before, n.length ) );
			// Пока поле в фокусе, префикс «+7 (» остаётся, даже если цифр нет.
			input.value = n || document.activeElement !== input ? format( n ) : '+7 (';
			prev = n;
			if ( document.activeElement === input ) {
				var pos = caretAfter( input.value, before );
				input.setSelectionRange( pos, pos );
			}
		} );

		// Вставка: номер берётся только из буфера, иначе «+7 (» в поле склеится с «8912…».
		input.addEventListener( 'paste', function ( e ) {
			var text = e.clipboardData && e.clipboardData.getData( 'text' );
			if ( ! text ) {
				return;
			}
			e.preventDefault();
			prev = national( text );
			codeTyped = true;
			input.value = format( prev );
			input.setSelectionRange( input.value.length, input.value.length );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );

		input.addEventListener( 'focus', function () {
			if ( '' === input.value ) {
				input.value = '+7 (';
			}
		} );

		input.addEventListener( 'blur', function () {
			if ( '' === national( input.value ) ) {
				input.value = '';
				prev = '';
				codeTyped = false;
			}
		} );
	}

	/* ── Проверка ── */

	function validate( form ) {
		var errors = {};
		var name = form.elements.name;
		var phone = form.elements.phone;
		var consent = form.elements.consent;

		if ( name && name.value.trim().length < 2 ) {
			errors.name = msg.name;
		}
		if ( phone ) {
			var n = national( phone.value );
			if ( '' === n ) {
				errors.phone = msg.phone_empty;
			} else if ( 10 !== n.length ) {
				errors.phone = msg.phone;
			}
		}
		if ( consent && ! consent.checked ) {
			errors.consent = msg.consent;
		}
		return errors;
	}

	function setFieldError( form, field, text ) {
		var wrap = form.querySelector( '[data-field="' + field + '"]' );
		var input = form.elements[ field ];
		var note = wrap && wrap.querySelector( '.field__error' );
		if ( ! wrap || ! input || ! note ) {
			return;
		}
		wrap.classList.toggle( 'field--error', !! text );
		note.textContent = text || '';
		note.hidden = ! text;
		if ( text ) {
			input.setAttribute( 'aria-invalid', 'true' );
			input.setAttribute( 'aria-describedby', note.id );
		} else {
			input.removeAttribute( 'aria-invalid' );
			input.removeAttribute( 'aria-describedby' );
		}
	}

	function showErrors( form, errors ) {
		[ 'name', 'phone', 'consent' ].forEach( function ( field ) {
			setFieldError( form, field, errors[ field ] );
		} );
	}

	function setGeneral( form, text ) {
		var box = form.querySelector( '.request__error' );
		if ( box ) {
			box.textContent = text || '';
			box.hidden = ! text;
		}
	}

	/* ── Состояния ── */

	function setSending( form, sending ) {
		var btn = form.querySelector( '.request__submit' );
		form.setAttribute( 'aria-busy', sending ? 'true' : 'false' );
		if ( ! btn ) {
			return;
		}
		if ( sending ) {
			btn.dataset.label = btn.textContent;
			btn.textContent = msg.sending || 'Отправляем…';
		} else if ( btn.dataset.label ) {
			btn.textContent = btn.dataset.label;
		}
		btn.disabled = sending;
	}

	function showSuccess( root, form, text ) {
		var box = root.querySelector( '.request__success' );
		var note = box && box.querySelector( '.request__success-text' );
		if ( note && text ) {
			note.textContent = text;
		}
		form.hidden = true;
		if ( box ) {
			box.hidden = false;
			box.focus();
		}
		if ( window.skyGoal && root.dataset.goal ) {
			window.skyGoal( root.dataset.goal );
		}
	}

	/* ── Отправка ── */

	function init( root ) {
		var form = root.querySelector( '.request__form' );
		if ( ! form || ! window.fetch || ! window.FormData ) {
			return;
		}
		var touched = false;

		form.noValidate = true; // свои сообщения вместо всплывающих подсказок браузера

		var phone = form.querySelector( '[data-phone-mask]' );
		if ( phone ) {
			initMask( phone );
		}

		// После первой попытки отправки ошибки обновляются по ходу ввода.
		form.addEventListener( 'input', function () {
			if ( touched ) {
				showErrors( form, validate( form ) );
			}
		} );
		form.addEventListener( 'change', function () {
			if ( touched ) {
				showErrors( form, validate( form ) );
			}
		} );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			if ( 'true' === form.getAttribute( 'aria-busy' ) ) {
				return;
			}
			touched = true;
			setGeneral( form, '' );

			var errors = validate( form );
			showErrors( form, errors );
			var first = Object.keys( errors )[ 0 ];
			if ( first ) {
				form.elements[ first ].focus();
				return;
			}

			var data = new FormData( form );
			data.append( 'sky_ajax', '1' );
			setSending( form, true );

			// getAttribute: form.action вернул бы скрытое поле name="action", а не адрес.
			window.fetch( form.getAttribute( 'action' ), {
				method: 'POST',
				body: data,
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
			} )
				.then( function ( res ) {
					// Сбой сервера — не вина посетителя: msg.error («проверьте имя и телефон») здесь не подходит.
					if ( ! res.ok ) {
						return { status: 'error', message: msg.server };
					}
					return res.json().catch( function () {
						return { status: 'error', message: msg.server };
					} );
				} )
				.then( function ( res ) {
					setSending( form, false );
					if ( 'ok' === res.status ) {
						showSuccess( root, form, res.message );
						return;
					}
					var errs = res.errors || {};
					showErrors( form, errs );
					setGeneral( form, res.message || msg.error );
					var bad = Object.keys( errs )[ 0 ];
					if ( bad && form.elements[ bad ] ) {
						form.elements[ bad ].focus();
					}
				} )
				.catch( function () {
					setSending( form, false );
					setGeneral( form, msg.network );
				} );
		} );
	}

	document.querySelectorAll( '[data-request]' ).forEach( init );

	// Ссылка «Заказать» / «Выбрать тариф» с data-lead-topic: подставить выбор в поле «Что нужно» формы по якорю.
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a[data-lead-topic]' );
		var hash = link && link.hash;
		var root = hash && document.getElementById( hash.slice( 1 ) );
		var field = root && root.querySelector( '[name="message"]' );
		if ( field ) {
			field.value = link.dataset.leadTopic;
		}
	} );

	// Переход по якорю к форме («Заказать лицензию», «Получить консультацию») — сразу в первое поле.
	// На сенсорном экране — только с клавиатуры (e.detail === 0), чтобы не выдвигать экранную клавиатуру.
	var finePointer = window.matchMedia( '(pointer: fine)' );
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a[href*="#"]' );
		if ( ! finePointer.matches && 0 !== e.detail ) {
			return;
		}
		if ( ! link || link.pathname !== window.location.pathname || ! link.hash ) {
			return;
		}
		var root = document.getElementById( link.hash.slice( 1 ) );
		var form = root && root.matches( '[data-request]' ) && root.querySelector( '.request__form:not([hidden])' );
		var input = form && form.querySelector( 'input:not([type="hidden"]):not([tabindex="-1"])' );
		if ( input ) {
			window.setTimeout( function () {
				input.focus( { preventScroll: true } );
			}, 0 );
		}
	} );
} )();
