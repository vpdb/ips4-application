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
			this.errMessage = $('#registerError').hide();
		},

		register: function() {
			var that = this;
			var continueBtn = $('[data-action="register"]');
			continueBtn.prop('disabled', true);
			ips.getAjax()(this.registerUrl, { type: 'post', data: { confirmed: true } })
				.done(function(response) {
					if (response.error) {
						console.log(response);
						that.errMessage.text(response.error).show();
						return;
					}
					that.trigger('closeDialog');
					ips.ui.flashMsg.show('Registration at VPDB successful!');
					$(document).trigger('vpdbRegistrationSuccessful');
				})
				.fail(function(err) {
					that.errMessage.text(err).show();
				})
				.always(function() {
					continueBtn.prop('disabled', false);
				})
		}
	});

}(jQuery, _));