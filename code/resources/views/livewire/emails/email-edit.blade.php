<div>
    @if($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title">MODIFICA EMAIL</div>
            </div>
            <div class="box-body">
                <form wire:submit.prevent="update">
                    <input type="hidden" wire:model="emailId" />

                    <div class="mb-3">
                        <label for="subject" class="block mb-1">Oggetto</label>
                        <input wire:model="subject" type="text" id="subject" class="form-control">
                        @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div wire:ignore class="mb-3">
                        <label for="body" class="block mb-1">Testo</label>
                        <textarea rows="15" wire:model="body" id="body" class="form-control"></textarea>
                        @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="block mb-1">URL</label>
                        <input wire:model="url" type="text" id="url" class="form-control">
                        @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="button_label" class="block mb-1">Button Label</label>
                        <input wire:model="button_label" type="text" id="button_label" class="form-control">
                        @error('button_label') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="ti-btn ti-btn-primary-full">Salva</button>
                    <a href="/emails" class="ti-btn ti-btn-light">Annulla</a>
                </form>
            </div>
        </div>


  


    @endif


    <script>
        tinymce.init({
            entity_encoding : "raw",
            selector: '#body',
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: 'p',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            height: 500,
            menubar: true,
            //valid_elements: '*[*]',
            valid_elements: "ins[*],div[class|data-embed|id|itemscope|itemtype|itemprop|content|meta],a[href|target|rel|class|id|itemprop],p[itemprop],br,b,i,u,strong,em,li,ul,ol,h2[class],h3[itemprop],h4,img[src|alt|class|width|height|itemprop|loading],table[border|cellspacing|cellpadding|class],thead[class],tbody[class],tr[class],th[scope|class|style|width],td[class|style],span[itemprop|class],meta[itemprop|content],iframe[width|height|loading|src|allow|allowfullscreen]",
            invalid_elements: 'class,pre,id,dir,lang,script',
            allow_unsafe_link_target: true,
            plugins: [
                'advlist image imagetools lists link charmap print preview anchor',
                'searchreplace visualblocks codesample fullscreen',
                'insertdatetime table paste wordcount code'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | codesample | link | code | imagetools',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('init change', function() {
                    editor.save();
                });
                editor.on('focusout', function(e) {
                    @this.set('body', editor.getContent());
                });
                
            },
        });
    </script>

</div>
