<raw>

    <span></span>

    <script>

        var cache = null;

        this.on('update', function(){

            if (cache==opts.content) return;

            this.root.innerHTML = opts.content;
            cache = opts.content;
        });

    </script>

</raw>
