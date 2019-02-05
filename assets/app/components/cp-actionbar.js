
customElements.define('cp-actionbar', class extends HTMLElement {

    constructor() {
        super();
    }

    connectedCallback() {
        document.body.style.paddingBottom = `calc(2rem + ${this.offsetHeight}px)`;
    }

    disconnectedCallback() {
        document.body.style.paddingBottom = '';
    }
});
