(function ($, undefined) {
	$.nette.ext('datepicker', {
		init: function () {
			this.bind($('body'));
		},
		success: function (payload) {
			var snippets;

			if (!payload.snippets || !(snippets = this.ext('snippets'))) {
				return;
			}

			for (var id in payload.snippets) {
				this.bind(snippets.getElement(id));
			}
		}
	}, {
		bind: function (el) {
			el.find('.datepicker').each(function (i, el) {
				var $input = $(el);
				$input.datepicker({
					format: $input.data('datepicker-format')
				});
			});
		}
	});
})(jQuery);