<raw>

    <span></span>

    <script>

        var cache = null;

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function(){

            if (cache==opts.content) return;

            this.root.innerHTML = opts.content;
            cache = opts.content;
        });

    </script>

</raw>
