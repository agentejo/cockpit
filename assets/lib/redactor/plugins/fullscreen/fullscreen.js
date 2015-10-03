(function($)
{
	$.Redactor.prototype.fullscreen = function()
	{
		return {
			init: function()
			{
				this.fullscreen.isOpen = false;

				var button = this.button.add('fullscreen', 'Fullscreen');
				this.button.addCallback(button, this.fullscreen.toggle);

				if (this.opts.fullscreen) this.fullscreen.toggle();
			},
			enable: function()
			{
				this.button.changeIcon('fullscreen', 'normalscreen');
				this.button.setActive('fullscreen');
				this.fullscreen.isOpen = true;

				if (this.opts.toolbarExternal)
				{
					this.fullscreen.toolcss = {};
					this.fullscreen.boxcss = {};
					this.fullscreen.toolcss.width = this.$toolbar.css('width');
					this.fullscreen.toolcss.top = this.$toolbar.css('top');
					this.fullscreen.toolcss.position = this.$toolbar.css('position');
					this.fullscreen.boxcss.top = this.$box.css('top');
				}

				this.fullscreen.height = this.$editor.height();

				if (this.opts.maxHeight) this.$editor.css('max-height', '');
				if (this.opts.minHeight) this.$editor.css('min-height', '');

				if (!this.$fullscreenPlaceholder) this.$fullscreenPlaceholder = $('<div/>');
				this.$fullscreenPlaceholder.insertAfter(this.$box);

				this.$box.appendTo(document.body);

				this.$box.addClass('redactor-box-fullscreen');
				$('body, html').css('overflow', 'hidden');

				this.fullscreen.resize();
				$(window).on('resize.redactor.fullscreen', $.proxy(this.fullscreen.resize, this));
				$(document).scrollTop(0, 0);

				$('.redactor-toolbar-tooltip').hide();
				this.$editor.focus();
				this.observe.load();
			},
			disable: function()
			{
				this.button.removeIcon('fullscreen', 'normalscreen');
				this.button.setInactive('fullscreen');
				this.fullscreen.isOpen = false;

				$(window).off('resize.redactor.fullscreen');
				$('body, html').css('overflow', '');

				this.$box.insertBefore(this.$fullscreenPlaceholder);
				this.$fullscreenPlaceholder.remove();

				this.$box.removeClass('redactor-box-fullscreen').css({ width: 'auto', height: 'auto' });

				this.code.sync();

				if (this.opts.toolbarExternal)
				{
					this.$box.css('top', this.fullscreen.boxcss.top);
					this.$toolbar.css({
						'width': this.fullscreen.toolcss.width,
						'top': this.fullscreen.toolcss.top,
						'position': this.fullscreen.toolcss.position
					});
				}

				if (this.opts.minHeight) this.$editor.css('minHeight', this.opts.minHeight);
				if (this.opts.maxHeight) this.$editor.css('maxHeight', this.opts.maxHeight);

				$('.redactor-toolbar-tooltip').hide();
				this.$editor.css('height', 'auto');
				this.$editor.focus();
				this.observe.load();
			},
			toggle: function()
			{
				if (this.fullscreen.isOpen)
				{
					this.fullscreen.disable();
				}
				else
				{
					this.fullscreen.enable();
				}
			},
			resize: function()
			{
				if (!this.fullscreen.isOpen) return;

				var toolbarHeight = this.$toolbar.height();

				var height = $(window).height() - toolbarHeight - this.utils.normalize(this.$editor.css('padding-top')) - this.utils.normalize(this.$editor.css('padding-bottom'));
				this.$box.width($(window).width()).height(height);

				if (this.opts.toolbarExternal)
				{
					this.$toolbar.css({
						'top': '0px',
						'position': 'absolute',
						'width': '100%'
					});

					this.$box.css('top', toolbarHeight + 'px');
				}

				this.$editor.height(height);
			}
		};
	};
})(jQuery);