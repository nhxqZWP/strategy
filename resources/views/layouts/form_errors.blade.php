<div class="with-border">
    @if(!empty(session('error')))
        <div class="alert alert-danger">{!! session('error') !!}</div>
    @endif

    @if(!empty(session('message')))
        <div class="alert alert-success">{!! session('message') !!}</div>
    @endif
</div>