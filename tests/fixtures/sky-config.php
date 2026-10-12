<?php
// Конфиг для tests/run.php.
return [
	'redirects' => [
		'map'      => [
			'/услуги/ремонт/'            => '/uslugi/remont/',
			'/%D1%86%D0%B5%D0%BD%D1%8B/' => '/ceny/',
			'/loop/'                     => '/loop/',
			'/old-page.html'             => '/kontakty/',
		],
		'prefixes' => [ '/services/' => '/uslugi/' ],
	],
	'city'      => [
		'nominative'    => 'Уфа',
		'prepositional' => 'Уфе',
	],
	'robots_txt' => [
		'disallow'    => [ '/*?s=' ],
		'clean_param' => [ 'utm_source&utm_medium' ],
	],
];
