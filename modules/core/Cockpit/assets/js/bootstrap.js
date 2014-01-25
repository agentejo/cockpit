(function(global, $, $win, $doc){

    
    $doc.on("app-init", function(){
        

        //auto-focus in-app search
        var txtSearch = $("#frmCockpitSearch").find('.uk-search-field');

        $doc.on("keydown", function(e) {

            //ctrl-c, ctrl-v etc.
            if(e.ctrlKey || e.altKey || e.metaKey) {
                return;
            }

            if (e.target.tagName && e.target.tagName.toLowerCase()=='body' && (e.keyCode>=65 && e.keyCode<=90)) {
                txtSearch.focus();
            }
        });

    });


})(this, jQuery, jQuery(window), jQuery(document));