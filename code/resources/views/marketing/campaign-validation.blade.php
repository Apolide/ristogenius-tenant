@script
<script>
const root = $wire.$el;
const enabledLanguages = form => [...form.querySelectorAll('[data-marketing-language]:checked')].map(input => input.value);
const syncEditorValidity = (form, showError = false) => {
    const enabled = enabledLanguages(form);
    form.querySelectorAll('[data-campaign-subject]').forEach(input => {
        input.required = enabled.includes(input.dataset.language);
        input.setCustomValidity(input.required && !input.value.trim() ? @js(__('marketing.subject_required')) : '');
    });
    form.querySelectorAll('[data-campaign-editor-validity]').forEach(input => {
        const textarea = document.getElementById(input.dataset.editorId);
        const editor = window.tinymce?.get(input.dataset.editorId);
        const html = document.createElement('div');
        const content = editor ? editor.getContent() : textarea.value;
        $wire.set(textarea.dataset.statePath, content, false);
        html.innerHTML = content;
        html.querySelectorAll('script, style').forEach(node => node.remove());
        input.value = html.textContent.replace(/[\s\u00a0\u200b]/g, '');
        input.required = enabled.includes(input.dataset.language);
        input.setCustomValidity(input.required && !input.value ? @js(__('marketing.content_required')) : '');
        const error = input.parentElement.querySelector('[data-editor-error]');
        if (!input.validationMessage) error.hidden = true;
        else if (showError) error.hidden = false;
    });
};
root.addEventListener('submit', event => {
    const form = event.target;
    if (!form.matches('[data-campaign-form]')) return;
    syncEditorValidity(form, true);
    const invalid = [...form.elements].find(input => input.willValidate && !input.checkValidity());
    if (!invalid) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (invalid.dataset.language) {
        form.dispatchEvent(new CustomEvent('campaign-invalid-language', {bubbles:true, detail:{language:invalid.dataset.language}}));
    }
    requestAnimationFrame(() => {
        invalid.scrollIntoView({block:'center', behavior:'auto'});
        invalid.focus({preventScroll:true});
        invalid.reportValidity();
    });
}, true);
root.addEventListener('input', event => {
    const form = event.target.closest('[data-campaign-form]');
    if (form) syncEditorValidity(form);
});
</script>
@endscript
