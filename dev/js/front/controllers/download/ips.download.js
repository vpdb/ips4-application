/**
 * This submits the form, causing the browser to download the file from VPDB,
 * and redirects to the given URL.
 */
;(function($, _, undefined) {
	'use strict';
	ips.controller.register('vpdb.front.download', {
		initialize: function() {
			var scope = this.scope;
			scope.submit();
			setTimeout(function() {
				window.location = scope.attr('data-redirect-to');
			}, 3000);
		}
	});
}(jQuery, _));