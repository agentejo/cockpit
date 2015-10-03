(function($)
{
	$.Redactor.prototype.counter = function()
	{
		return {
			init: function()
			{
				if (!this.opts.counterCallback) return;

				this.$editor.on('keyup.redactor-limiter', $.proxy(function(e)
				{
					var words = 0, characters = 0, spaces = 0;

					var html = this.code.get();

					var text = html.replace(/<\/(.*?)>/gi, ' ');
					text = text.replace(/<(.*?)>/gi, '');
					text = text.replace(/\t/gi, '');
					text = text.replace(/\n/gi, ' ');
					text = text.replace(/\r/gi, ' ');
					text = $.trim(text);

					if (text !== '')
					{
						var arrWords = text.split(/\s+/);
						var arrSpaces = text.match(/\s/g);

						if (arrWords) words = arrWords.length;
						if (arrSpaces) spaces = arrSpaces.length;

						characters = text.length;

					}

					this.core.setCallback('counter', { words: words, characters: characters, spaces: spaces });


				}, this));
			}
		};
	};
})(jQuery);