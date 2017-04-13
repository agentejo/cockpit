(function($, UI){


    // UIkit overrides

    UI.modal.alert = function(content, options) {

        options = UI.$.extend(true, {modal:false, title: false, labels:UI.modal.labels}, options);

        var modal = UI.modal.dialog(([
            options.title ? '<div class="uk-modal-header"><h2>'+options.title+'</h2></div>':'',
            '<div class="uk-margin uk-modal-content">'+(options.title ? content : '<h2>'+content+'</h2>')+'</div>',
            '<div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-link uk-button-large uk-modal-close">'+options.labels.Ok+'</button></div>'
        ]).join(""), options);

        modal.on('show.uk.modal', function(){
            setTimeout(function(){
                modal.element.find('button:first').focus();
            }, 50);
        });

        modal.show();
    };

    UI.modal.confirm = function(content, onconfirm, options) {

        onconfirm = UI.$.isFunction(onconfirm) ? onconfirm : function(){};
        options   = UI.$.extend(true, {modal:false, title: false, labels:UI.modal.labels}, options);

        var modal = UI.modal.dialog(([
            options.title ? '<div class="uk-modal-header"><h2>'+options.title+'</h2></div>':'',
            '<div class="uk-margin uk-modal-content">'+(options.title ? content : '<h2>'+content+'</h2>')+'</div>',
            '<div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-link uk-button-large uk-modal-close">'+options.labels.Cancel+'</button> <button class="uk-button uk-button-link uk-button-large js-modal-confirm">'+options.labels.Ok+'</button></div>'
        ]).join(""), options);

        modal.element.find(".js-modal-confirm").on("click", function(){
            onconfirm();
            modal.hide();
        });

        modal.on('show.uk.modal', function(){
            setTimeout(function(){
                modal.element.find('button:first').focus();
            }, 50);
        });

        modal.show();
    };

    UI.modal.prompt = function(text, value, onsubmit, options) {

        onsubmit = UI.$.isFunction(onsubmit) ? onsubmit : function(value){};
        options  = UI.$.extend(true, {modal:false, title: false, labels:UI.modal.labels}, options);

        var modal = UI.modal.dialog(([
            options.title ? '<div class="uk-modal-header"><h2>'+options.title+'</h2></div>':'',
            text ? '<div class="uk-modal-content uk-form">'+(options.title ? text : '<h2>'+text+'</h2>')+'</div>':'',
            '<div class="uk-margin-small-top uk-modal-content uk-form"><p><input type="text" class="uk-width-1-1"></p></div>',
            '<div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-link uk-button-large uk-modal-close">'+options.labels.Cancel+'</button> <button class="uk-button uk-button-link js-modal-ok">'+options.labels.Ok+'</button></div>'
        ]).join(""), options),

        input = modal.element.find("input[type='text']").val(value || '').on('keyup', function(e){
            if (e.keyCode == 13) {
                modal.element.find(".js-modal-ok").trigger('click');
            }
        });

        modal.element.find(".js-modal-ok").on("click", function(){
            if (onsubmit(input.val())!==false){
                modal.hide();
            }
        });

        modal.on('show.uk.modal', function(){
            setTimeout(function(){
                input.focus();
            }, 50);
        });

        modal.show();
    };

    $(document).on('stop.uk.sortable', '[data-modules-menu]', function(){

        var order = {};

        $(this).children().each(function(idx){
            order[this.getAttribute('data-route')] = idx+1;
        });

        App.request('/cockpit/savemenu',{order:order}).then(function(){

        });
    });

    // Material Ripple effect
    $('html').on('click', 'a, button, input, .ripple', function(e) {

        var trigger = $(this), w = trigger.outerWidth(), h = trigger.outerHeight(), 
            d = Math.min(w, h), isInput = trigger.is('input'), surfaceCSS, surface;

        surfaceCSS = {
            top      : trigger.offset().top,
            left     : trigger.offset().left,
            width    : w,
            height   : h,
            overflow : d > 100 || trigger.is('button,.uk-button') ? 'hidden' : ''
        };

        surface = $('<div class="material-ripple"><div class="material-ink"></div></div>').css(surfaceCSS).appendTo('body');

        surface.find('.material-ink').css({
            height     : d,
            width      : d,
            top: Math.floor(h/2 - d/2),
            left: isInput ? e.clientX - surfaceCSS.left : Math.floor(w/2 - d/2),
            background : trigger.attr('ripple-color') || ''
        }).on('animationend', function() {
            surface.remove();
        }).addClass('animate-ink').width();

        setTimeout(function(){
            surface.remove();
        }, 500);
    });

})(jQuery, UIkit);
