document.body.addEventListener('htmx:afterSwap', (evt) => {
    if (evt.target.id === 'form-panel') {
        const form = evt.target.querySelector('form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
