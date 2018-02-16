/**
 * This submits the form, causing the browser to download the file from VPDB,
 * and redirects to the given URL.
 */
;(function($, _, undefined) {
	'use strict';
	ips.controller.register('vpdb.front.register', {

		initialize: function() {
			this.on('click', '[data-action="register"]', this.register);
			this.registerUrl = this.scope.attr('data-register');
		},

		register: function() {
			ips.getAjax()(this.registerUrl, { type: 'post', data: { confirmed: true } })
				.done(function(response) {
					console.log('done!', response);
				})
				.fail(function(err) {
					console.log('failed!', err);
				})
				.always(function() {

				});
		}
	});

}(jQuery, _));