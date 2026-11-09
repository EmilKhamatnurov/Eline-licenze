/** A single, understated entrance for section headings below the initial viewport. */
( function () {
	'use strict';

	if ( ! ( 'IntersectionObserver' in window ) || ! Element.prototype.animate ||
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	var observer = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( ! entry.isIntersecting ) {
				return;
			}
			observer.unobserve( entry.target );
			// A preference can change while the page is open.
			if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
				return;
			}
			entry.target.animate( [
				{ opacity: 0.35, transform: 'translateY(12px)' },
				{ opacity: 1, transform: 'translateY(0)' },
			], { duration: 600, easing: 'cubic-bezier(0.23, 1, 0.32, 1)' } );
		} );
	}, { threshold: 0, rootMargin: '0px 0px -40px 0px' } );

	document.querySelectorAll( '#main section h2' ).forEach( function ( heading ) {
		// Linked titles and headings already on screen stay completely still.
		if ( heading.querySelector( 'a, button' ) || heading.getBoundingClientRect().top < window.innerHeight ) {
			return;
		}
		observer.observe( heading );
	} );
} )();
