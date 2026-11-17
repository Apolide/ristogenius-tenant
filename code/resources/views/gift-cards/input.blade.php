<label class="block">
    <span class="form-label">{{ __("gift_cards.fields.{$field}") }} @if($required) * @endif</span>
    <input class="form-control" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $max }}"
           autocomplete="{{ $autocomplete }}" value="{{ is_scalar(old($field)) ? old($field) : '' }}" @required($required)>
</label>
