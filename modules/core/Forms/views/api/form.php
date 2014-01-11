<script>
    
    (function($){

        if(!$) return;

        var formid = "<?php echo $options["id"];?>";

        $(function($){

            var form = $("#"+formid), successmessage = form.find(".success-message").hide(), failmessage = form.find(".fail-message").hide();

            form.on("submit", function(){
                
                successmessage.hide();
                failmessage.hide();

                var data   = form.serialize(),
                    inputs = form.find(":input").attr("disabled", true);

                form.trigger("form-submit", [form]);

                $.post("<?php $this->route('/api/forms/submit/'.$name);?>", data, function(response){
                    
                    form.trigger("form-after-post", [form, response]);

                    if(response=='false') {
                        
                        if(failmessage.length) {
                            failmessage.show();
                        } else {
                            alert('Form submission failed.');
                        }
                    } else {
                        
                        if(successmessage.length) {
                            successmessage.show();
                        } else {
                            alert('Form submission was successfull.');
                            form[0].reset();
                        }
                    }

                    inputs.attr("disabled", false);

                }).fail(function(){

                    form.trigger("form-fail", [form]);

                    if(failmessage.length) {
                        failmessage.show();
                    } else {
                        alert('Form submission failed.');
                    }

                    inputs.attr("disabled", false); 
                });

                return false;
            })

        });

    })(window.jQuery || undefined);

</script>

<form id="<?php echo $options["id"];?>" name="<?php echo $name;?>" class="<?php echo $options["class"];?>" method="post" onsubmit="return false;">
<input type="hidden" name="__csrf" value="<?php echo $options["csrf"];?>">
<?php if(isset($options["mailsubject"])): ?>
<input type="hidden" name="__mailsubject" value="<?php echo $options["mailsubject"];?>">
<?php endif; ?>