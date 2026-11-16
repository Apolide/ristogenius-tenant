@if($errors->any())<div class="o-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>@endif
@if(session('order_notice'))<div role="status" class="o-panel mb-4">{{ session('order_notice') }}</div>@endif
