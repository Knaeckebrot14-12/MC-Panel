@section('settings::notice')
    @if(config('pterodactyl.load_environment_only', false))
        <div class="row">
            <div class="col-xs-12">
                <div class="alert alert-danger">
                    {!! trans('admin/settings.notice.env_only', ['env_var' => '<code>APP_ENVIRONMENT_ONLY=false</code>']) !!}
                </div>
            </div>
        </div>
    @endif
@endsection
