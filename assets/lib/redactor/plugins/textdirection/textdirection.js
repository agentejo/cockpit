(function($)
{
	$.Redactor.prototype.textdirection = function()
	{
		return {
			init: function()
			{
				var that = this;
				var dropdown = {};

				dropdown.ltr = { title: 'Left to Right', func: that.textdirection.setLtr };
				dropdown.rtl = { title: 'Right to Left', func: that.textdirection.setRtl};

				var button = this.button.add('textdirection', 'Change Text Direction');
				this.button.addDropdown(button, dropdown);
			},
			setRtl: function()
			{
				this.buffer.set();
				this.block.setAttr('dir', 'rtl');
			},
			setLtr: function()
			{
				this.buffer.set();
				this.block.removeAttr('dir');
			}
		};
	};
})(jQuery);