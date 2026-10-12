<?php
/**
 * Модуль analytics: Яндекс Метрика и цели.
 *
 * @package Sky\Baseline
 */

namespace Sky\Baseline\Modules;

use Sky\Baseline\Config;
use Sky\Baseline\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Счётчик выводится, только если задан analytics.metrika_id.
 * Цели: успешная заявка (?lead=ok), клики по tel:, mailto: и ссылкам мессенджеров.
 */
final class Analytics implements Module {

	/**
	 * Хуки.
	 */
	public function register(): void {
		add_action( 'wp_head', [ $this, 'counter' ], 20 );
		add_action( 'wp_body_open', [ $this, 'noscript' ] );
		add_action( 'wp_footer', [ $this, 'goals' ], 20 );
	}

	/**
	 * ID счётчика или 0, если счётчик не нужен на этой странице.
	 */
	public static function counter_id(): int {
		$id = absint( Config::get( 'analytics.metrika_id', 0 ) );
		if ( ! $id || is_preview() || is_customize_preview() ) {
			return 0;
		}
		// Визиты администраторов портят статистику и вебвизор.
		if ( Config::get( 'analytics.exclude_admins', true ) && current_user_can( 'manage_options' ) ) {
			return 0;
		}
		return $id;
	}

	/**
	 * Код счётчика.
	 */
	public function counter(): void {
		$id = self::counter_id();
		if ( ! $id ) {
			return;
		}
		$options = [
			'clickmap'            => true,
			'trackLinks'          => true,
			'accurateTrackBounce' => true,
			'webvisor'            => (bool) Config::get( 'analytics.webvisor', true ),
		];
		$js      = '(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};'
			. 'm[i].l=1*new Date();for(var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return;}}'
			. 'k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})'
			. '(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");'
			. sprintf( 'ym(%d,"init",%s);', $id, wp_json_encode( $options ) );
		wp_print_inline_script_tag( $js );
	}

	/**
	 * Пиксель для посетителей без JS.
	 */
	public function noscript(): void {
		$id = self::counter_id();
		if ( $id ) {
			printf(
				'<noscript><div><img src="%s" style="position:absolute;left:-9999px" alt=""></div></noscript>' . "\n",
				esc_url( 'https://mc.yandex.ru/watch/' . $id )
			);
		}
	}

	/**
	 * Цели. Функция window.skyGoal( 'имя' ) доступна теме.
	 */
	public function goals(): void {
		$id = self::counter_id();
		if ( ! $id ) {
			return;
		}
		$goals = array_map( 'strval', (array) Config::get( 'analytics.goals', [] ) );
		$hosts = array_values( array_map( 'strval', (array) Config::get( 'analytics.messenger_hosts', [] ) ) );

		$js = sprintf(
			'(function(id,g,hosts){'
			. 'function goal(n,p){if(n&&typeof window.ym==="function"){window.ym(id,"reachGoal",n,p||{});}}'
			. 'window.skyGoal=goal;'
			// Цель заявки срабатывает один раз: параметр убирается из адреса, обновление страницы не задвоит конверсию.
			. 'try{var u=new URL(location.href);if(u.searchParams.get("lead")==="ok"){goal(g.lead);u.searchParams.delete("lead");history.replaceState(null,"",u.pathname+u.search+u.hash);}}catch(e){}'
			. 'document.addEventListener("click",function(e){'
			. 'var a=e.target&&e.target.closest?e.target.closest("a[href]"):null;if(!a){return;}'
			. 'var h=a.getAttribute("href")||"";'
			. 'if(/^tel:/i.test(h)){goal(g.tel);}'
			. 'else if(/^mailto:/i.test(h)){goal(g.email);}'
			. 'else if(/^(tg|whatsapp|viber):/i.test(h)||(a.hostname&&hosts.indexOf(a.hostname.replace(/^www\./,""))>-1)){goal(g.messenger,{messenger:a.hostname||h.split(":")[0]});}'
			. '},true);'
			. '})(%d,%s,%s);',
			$id,
			wp_json_encode( (object) $goals, JSON_HEX_TAG ),
			wp_json_encode( $hosts, JSON_HEX_TAG )
		);
		wp_print_inline_script_tag( $js );
	}
}
