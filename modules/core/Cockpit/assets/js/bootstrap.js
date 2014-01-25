(function(global, $, $win, $doc){

    
    $doc.on("app-init", function(){
        
        var frmCockpitSearch = $("#frmCockpitSearch"),
            txtSearch        = frmCockpitSearch.find('.uk-search-field');

        $doc.on("keydown", function(e) {

            if (e.target.tagName && e.target.tagName.toLowerCase()=='body') {
                txtSearch.focus();
            }
        });

    });


})(this, jQuery, jQuery(window), jQuery(document));