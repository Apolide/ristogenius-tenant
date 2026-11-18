@props(['path', 'value' => '', 'validationLanguage' => null])
<div wire:ignore style="position:relative">
    <textarea id="editor-{{ md5($path) }}" data-marketing-editor data-state-path="{{ $path }}" class="form-control" rows="8">{{ $value }}</textarea>
    @if($validationLanguage)
        {{-- TinyMCE hides its textarea: this focusable control anchors the native validation popup. --}}
        <input type="text" data-campaign-editor-validity data-language="{{ $validationLanguage }}" data-editor-id="editor-{{ md5($path) }}"
               aria-label="{{ __('marketing.email_body') }} {{ strtoupper($validationLanguage) }}" aria-describedby="editor-error-{{ $validationLanguage }}" tabindex="-1"
               style="position:absolute;bottom:0;left:0;width:100%;height:1px;opacity:0;pointer-events:none">
        <p id="editor-error-{{ $validationLanguage }}" data-editor-error class="text-danger mt-2" hidden>{{ __('marketing.content_required') }}</p>
    @endif
</div>
