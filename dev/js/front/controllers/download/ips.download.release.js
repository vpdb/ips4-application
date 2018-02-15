/**
 * This submits the form, causing the browser to download the file from VPDB,
 * and redirects to the given URL.
 */
;(function($, _, undefined) {
	'use strict';
	ips.controller.register('vpdb.front.download.release', {

		initialize: function() {
			this.on('click', '[data-action="download"]', this.download);
		},

		download: function() {

			var that = this;
			var form = $('#releaseDownloadForm');
			var errMessage = $('#releaseDownloadError');
			var downloadBtn = $('[data-action="download"]');

			downloadBtn.prop('disabled', true);
			ips.getAjax()(form.attr('action') + '&' + form.serialize()).done(function(response) {
				if (response.url) {
					download(response.url, function(err) {
						downloadBtn.prop('disabled', false);
						if (err) {
							errMessage.text(err.error).show();
						} else {
							errMessage.hide();
							that.trigger('closeDialog');
						}

					});

				} else if (response.error) {
					downloadBtn.prop('disabled', false);
					errMessage.text(response.error).show();

				} else {
					downloadBtn.prop('disabled', false);
					errMessage.text('Something went wrong, please contact an admin: ' + JSON.stringify(response)).show();
				}
			});
		}

	});

	/**
	 * Downloads a "save as" attachment via Ajax
	 *
	 * @see https://stackoverflow.com/questions/16086162/handle-file-download-from-ajax-post#answer-23797348
	 * @param url URL of the download
	 * @param callback Called after, with first argument as parsed error response on error
	 */
	function download(url, callback) {
		var xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.responseType = 'arraybuffer';
		xhr.onload = function () {
			if (this.status === 200) {
				var filename = "";
				var disposition = xhr.getResponseHeader('Content-Disposition');
				if (disposition && disposition.indexOf('attachment') !== -1) {
					var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
					var matches = filenameRegex.exec(disposition);
					if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
				}
				var type = xhr.getResponseHeader('Content-Type');

				var blob = typeof File === 'function'
					? new File([this.response], filename, { type: type })
					: new Blob([this.response], { type: type });
				if (typeof window.navigator.msSaveBlob !== 'undefined') {
					// IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
					window.navigator.msSaveBlob(blob, filename);
				} else {
					var URL = window.URL || window.webkitURL;
					var downloadUrl = URL.createObjectURL(blob);

					if (filename) {
						// use HTML5 a[download] attribute to specify filename
						var a = document.createElement("a");
						// safari doesn't support this yet
						if (typeof a.download === 'undefined') {
							window.location = downloadUrl;
						} else {
							a.href = downloadUrl;
							a.download = filename;
							document.body.appendChild(a);
							a.click();
						}
					} else {
						window.location = downloadUrl;
					}

					setTimeout(function () {
						URL.revokeObjectURL(downloadUrl);
						callback();
					}, 100); // cleanup
				}
			} else {
				// need to convert from arraybuffer to string to object
				callback(JSON.parse(String.fromCharCode.apply(null, new Uint8Array(xhr.response))));
			}
		};
		xhr.send();
	}
}(jQuery, _));