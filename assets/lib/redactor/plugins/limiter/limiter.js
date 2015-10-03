(function($)
{
	$.Redactor.prototype.limiter = function()
	{
		return {
			init: function()
			{
				if (!this.opts.limiter) return;

				this.$editor.on('keydown.redactor-limiter', $.proxy(function(e)
				{
					var key = e.which;
					var ctrl = e.ctrlKey || e.metaKey;

					if (key == this.keyCode.BACKSPACE
					   	|| key == this.keyCode.DELETE
					    || key == this.keyCode.ESC
					    || key == this.keyCode.SHIFT
					    || (ctrl && key == 65)
					    || (ctrl && key == 82)
					    || (ctrl && key == 116)
					)
					{
						return;
					}

					var count = this.$editor.text().length;
					if (count >= this.opts.limiter)
					{
						return false;
					}


				}, this));

			}
		};
	};
})(jQuery);