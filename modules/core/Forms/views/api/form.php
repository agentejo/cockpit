<script>
    
    (function($){

        if(!$) return;

        var formid = "{{ $options["id"] }}";

        $(function($){

            var form       = $("#"+formid), 
                msgsuccess = form.find(".form-message-success").hide(), 
                msgfail    = form.find(".form-message-fail").hide();

            form.on("submit", function(){
                
                msgsuccess.hide();
                msgfail.hide();

                var data   = form.serialize(),
                    inputs = form.find(":input").attr("disabled", true);

                form.trigger("form-submit", [form]);

                $.post("@route('/api/forms/submit/'.$name)", data, function(response){
                    
                    form.trigger("form-after-post", [form, response]);

                    if(response=='false') {
                        
                        if(msgfail.length) {
                            msgfail.show();
                        } else {
                            alert("@lang('Form submission failed.')");
                        }
                    } else {
                        
                        if(msgsuccess.length) {
                            msgsuccess.show();
                        } else {
                            alert("@lang('Form submission was successfull.')");
                            form[0].reset();
                        }
                    }

                    inputs.attr("disabled", false);

                }).fail(function(){

                    form.trigger("form-fail", [form]);

                    if(msgfail.length) {
                        msgfail.show();
                    } else {
                        alert("@lang('Form submission failed.')");
                    }

                    inputs.attr("disabled", false); 
                });

                return false;
            })

        });

    })(window.jQuery || undefined);

</script>

<form id="{{ $options["id"] }}" name="{{ $name }}" class="{{ $options["class"] }}" method="post" onsubmit="return false;">
<input type="hidden" name="__csrf" value="{{ $options["csrf"] }}">
@if(isset($options["mailsubject"])):
<input type="hidden" name="__mailsubject" value="{{ $options["mailsubject"] }}">
@endif