/**
 * Квиз «Подбор лицензии» (template-parts/components/quiz.php, данные — inc/quiz.php).
 * Шаг 1 — вид деятельности, шаг 2 — форма бизнеса, шаг 3 — результат и форма заявки.
 * Ответы записываются в поле заявки «Что нужно» (message), само поле на шаге 3 скрыто.
 * Без JS этот файл не нужен: видна обычная форма с полем «Что нужно».
 */
( function () {
	'use strict';

	function init( root ) {
		var ui = root.querySelector( '[data-quiz-ui]' );
		var fallback = root.querySelector( '[data-quiz-fallback]' );
		var formWrap = root.querySelector( '[data-quiz-form-wrap]' );
		var slot = root.querySelector( '[data-quiz-slot]' );
		var steps = Array.prototype.slice.call( root.querySelectorAll( '[data-quiz-step]' ) );
		var results = Array.prototype.slice.call( root.querySelectorAll( '[data-quiz-result]' ) );
		var label = root.querySelector( '[data-quiz-label]' );
		var percent = root.querySelector( '[data-quiz-percent]' );
		var bar = root.querySelector( '.quiz__bar' );
		var fill = root.querySelector( '.quiz__bar-fill' );
		var topic = formWrap && formWrap.querySelector( 'input[name="message"]' );
		var topicField = topic && topic.closest( '[data-field="message"]' );
		if ( ! ui || ! steps.length || ! formWrap || ! slot ) {
			return;
		}

		var state = { step: 1, activity: null, form: '' };
		// Три шага, как обещает вводный текст: два вопроса и «куда прислать расчёт». Процент — положение на шкале.
		var progress = {
			1: [ 'Вопрос 1 из 3', 33 ],
			2: [ 'Вопрос 2 из 3', 67 ],
			3: [ 'Вопрос 3 из 3: куда прислать расчёт', 100 ],
		};

		// Выбранный ответ помечен (aria-pressed) — после «Назад» видно, что было выбрано.
		function mark( btn ) {
			btn.closest( '.quiz__options' ).querySelectorAll( '.quiz__option' ).forEach( function ( b ) {
				b.setAttribute( 'aria-pressed', b === btn ? 'true' : 'false' );
			} );
		}

		function show( step, focus ) {
			state.step = step;
			steps.forEach( function ( el ) {
				el.hidden = String( step ) !== el.dataset.quizStep;
			} );
			var p = progress[ step ];
			label.textContent = p[ 0 ];
			percent.textContent = p[ 1 ] + '%';
			fill.style.transform = 'scaleX(' + p[ 1 ] / 100 + ')';
			bar.setAttribute( 'aria-valuenow', String( p[ 1 ] ) );
			bar.setAttribute( 'aria-valuetext', p[ 0 ] );
			if ( focus ) {
				// Фокус на вопрос нового шага — клавиатура и экранный диктор не теряют место.
				var h = root.querySelector( '[data-quiz-step="' + step + '"] .quiz__question' );
				if ( h ) {
					h.focus();
				}
			}
		}

		function summary() {
			var names = state.activity
				? Array.prototype.map.call( state.activity.result.querySelectorAll( '.quiz__license' ), function ( a ) {
					return a.textContent;
				} )
				: [];
			return 'Подбор лицензии: ' + state.activity.label + '; форма бизнеса: ' + state.form + ( names.length ? '; подходит: ' + names.join( ', ' ) : '' );
		}

		root.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( 'button' );
			if ( ! btn || ! root.contains( btn ) ) {
				return;
			}
			if ( btn.dataset.quizActivity ) {
				var id = btn.dataset.quizActivity;
				mark( btn );
				state.activity = {
					label: btn.textContent.trim(),
					result: results.filter( function ( r ) {
						return r.dataset.quizResult === id;
					} )[ 0 ],
				};
				show( 2, true );
			} else if ( btn.dataset.quizForm ) {
				state.form = btn.dataset.quizForm;
				mark( btn );
				results.forEach( function ( r ) {
					r.hidden = r !== state.activity.result;
				} );
				if ( topic ) {
					topic.value = summary();
				}
				show( 3, true );
			} else if ( btn.hasAttribute( 'data-quiz-back' ) ) {
				show( 1, true );
			} else if ( btn.hasAttribute( 'data-quiz-restart' ) ) {
				state = { step: 1, activity: null, form: '' };
				root.querySelectorAll( '.quiz__option[aria-pressed]' ).forEach( function ( b ) {
					b.removeAttribute( 'aria-pressed' );
				} );
				if ( topic ) {
					topic.value = '';
				}
				show( 1, true );
			}
		} );

		// Режим шагов: форма переезжает в шаг 3, поле «Что нужно» заполняет квиз.
		slot.appendChild( formWrap );
		if ( topicField ) {
			topicField.hidden = true;
		}
		fallback.hidden = true;
		ui.hidden = false;
		show( 1, false );
	}

	document.querySelectorAll( '[data-quiz]' ).forEach( init );
} )();
