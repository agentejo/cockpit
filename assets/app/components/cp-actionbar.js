
(function() {

    var oldTarget = null;

    const tabKeydown = e => {
        if (e.keyCode != 9) return;
        oldTarget = e.target;
    };

    const tabKeyup = e => {

        if (e.keyCode != 9 || e.target == oldTarget	) return;

        let viewHeight   = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        let footerHeight = document.querySelector('cp-actionbar').offsetHeight;
        let elemTop      = e.target.getBoundingClientRect().top;
        let elemHeight   = e.target.offsetHeight;
        let maxHeight    = viewHeight - footerHeight - elemHeight;

        if (elemTop > maxHeight) {

            let container = e.target.closest('cp-fieldcontainer');
            let label     = container ? container.querySelector('label') : null;
            let bodyTop   = document.body.getBoundingClientRect().top;
            let labelTop  = label ? label.getBoundingClientRect().top : 0;

            let newTop    = elemHeight < footerHeight
              ? window.scrollY + footerHeight + 10 :
                ( viewHeight - footerHeight > elemHeight
                  ? window.scrollY + elemHeight 
                    : (labelTop || elemTop) - bodyTop );

            window.scrollTo({
                top: newTop,
                behavior: 'smooth'
            });
        }

    };

    customElements.define('cp-actionbar', class extends HTMLElement {

        constructor() {
            super();
        }

        connectedCallback() {
            document.body.style.paddingBottom = `calc(2rem + ${this.offsetHeight}px)`;

            document.addEventListener('keydown', tabKeydown);
            document.addEventListener('keyup', tabKeyup);
        }

        disconnectedCallback() {
            document.body.style.paddingBottom = '';
        }
    });

})();
