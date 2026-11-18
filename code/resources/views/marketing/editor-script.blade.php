@script
<script>
const init = () => {
    if (!window.tinymce) return;
    $wire.$el.querySelectorAll('[data-marketing-editor]').forEach(element => {
        if (tinymce.get(element.id)) return;
        tinymce.init({target:element,license_key:'gpl',promotion:false,height:320,menubar:true,
            plugins:'code link image lists table preview wordcount',toolbar:'undo redo | blocks | bold italic | bullist numlist | link image table | code preview',
            relative_urls:false,remove_script_host:false,convert_urls:true,
            invalid_elements:'script,iframe,object,embed,form,input,button,meta,style',
            setup(editor){editor.on('change input undo redo focusout',()=>{ editor.save(); $wire.set(element.dataset.statePath,editor.getContent(),false); element.dispatchEvent(new Event('input', {bubbles:true})); });}
        });
    });
};
if (window.tinymce) init(); else { const script=document.createElement('script');script.src='/js/tinymce/tinymce.min.js';script.onload=init;document.head.appendChild(script); }
</script>
@endscript
