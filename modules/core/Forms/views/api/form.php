<script>

    setTimeout(function(){

        var form       = document.getElementById("{{ $options['id'] }}"),
            msgsuccess = form.getElementsByClassName("form-message-success").item(0),
            msgfail    = form.getElementsByClassName("form-message-fail").item(0),
            bind       = function(ele, evt, fn) {
                if (!ele.addEventListener) {
                    ele.attachEvent("on"+evt, fn);
                } else {
                    ele.addEventListener(evt, fn, false);
                }
            },
            success = function(){
                if(msgsuccess) {
                    msgsuccess.style.display = 'block';
                } else {
                    alert("@lang('Form submission was successfull.')");
                }
            },
            fail = function(){
                if(msgfail) {
                    msgfail.style.display = 'block';
                } else {
                    alert("@lang('Form submission failed.')");
                }
            };

        if(msgsuccess) msgsuccess.style.display = "none";
        if(msgfail)    msgfail.style.display = "none";

        bind(form, "submit", function(e){

            e.preventDefault();

            if(msgsuccess) msgsuccess.style.display = "none";
            if(msgfail)    msgfail.style.display = "none";

            var xhr = new XMLHttpRequest(), data;

            if(window.FormData) {
                data = new FormData(form);
            } else {
                // deprecated: remove after dropping ie 9 support
                data = serialize(form);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            }

            xhr.onload = function(){

                this.responseText;

                if (this.status == 200) {

                    if(this.responseText=='false') {
                        fail();
                    } else {
                        success();
                        form.reset();
                    }

                } else {
                    fail();
                }
            };

            xhr.open('POST', "@route('/api/forms/submit/'.$name)", true);
            xhr.send(data);
        });

        function serialize(form) {
            if (!form || form.nodeName !== "FORM") {
                return;
            }
            var i, j, q = [];
            for (i = form.elements.length - 1; i >= 0; i = i - 1) {
                if (form.elements[i].name === "") {
                    continue;
                }
                switch (form.elements[i].nodeName) {
                case 'INPUT':
                    switch (form.elements[i].type) {
                    case 'color':
                    case 'date':
                    case 'datetime':
                    case 'datetime-local':
                    case 'email':
                    case 'month':
                    case 'number':
                    case 'range':
                    case 'search':
                    case 'tel':
                    case 'text':
                    case 'time':
                    case 'url':
                    case 'week':
                    case 'hidden':
                    case 'password':
                    case 'button':
                    case 'reset':
                    case 'submit':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                    case 'checkbox':
                    case 'radio':
                        if (form.elements[i].checked) {
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        }
                        break;
                    case 'file':
                        break;
                    }
                    break;
                case 'TEXTAREA':
                    q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                    break;
                case 'SELECT':
                    switch (form.elements[i].type) {
                    case 'select-one':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                    case 'select-multiple':
                        for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                            if (form.elements[i].options[j].selected) {
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
                            }
                        }
                        break;
                    }
                    break;
                case 'BUTTON':
                    switch (form.elements[i].type) {
                    case 'reset':
                    case 'submit':
                    case 'button':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                    }
                    break;
                }
            }
            return q.join("&");
        }

    }, 100);

</script>

<form id="{{ $options["id"] }}" name="{{ $name }}" class="{{ $options["class"] }}" method="post" onsubmit="return false;">
<input type="hidden" name="__csrf" value="{{ $options["csrf"] }}">
@if(isset($options["mailsubject"])):
<input type="hidden" name="__mailsubject" value="{{ $options["mailsubject"] }}">
@endif