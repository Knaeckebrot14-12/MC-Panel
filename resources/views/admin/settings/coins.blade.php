@extends('layouts.admin')

@section('title')
    @lang('admin/settings_coins.title')
@endsection

@section('content-header')
    <h1>@lang('admin/settings_coins.heading')<small>@lang('admin/settings_coins.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/settings_coins.heading')</li>
    </ol>
@endsection

@section('content')
    <form>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.linkvertise.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="form-group col-md-6">
                            <label class="control-label">@lang('admin/settings_coins.linkvertise.user_id_label') <span class="field-optional"></span></label>
                            <div>
                                <input type="text" class="form-control" name="coins:linkvertise:user_id" value="{{ old('coins:linkvertise:user_id', config('coins.linkvertise.user_id')) }}"/>
                                <p class="text-muted small">@lang('admin/settings_coins.linkvertise.user_id_description')</p>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.linkvertise.reward_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:linkvertise:reward" value="{{ old('coins:linkvertise:reward', config('coins.linkvertise.reward')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.linkvertise.reward_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.linkvertise.daily_limit_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="coins:linkvertise:daily_limit" value="{{ old('coins:linkvertise:daily_limit', config('coins.linkvertise.daily_limit')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.linkvertise.daily_limit_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.linkvertise.daily_limit_description')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.afk.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.afk.reward_per_minute_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:afk:reward_per_minute" value="{{ old('coins:afk:reward_per_minute', config('coins.afk.reward_per_minute')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.afk.reward_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-xs-12">
                            <label class="control-label">@lang('admin/settings_coins.afk.ad_slot_label') <span class="field-optional"></span></label>
                            <div>
                                <textarea name="coins:afk:ad_slot_html" rows="6" class="form-control" style="font-family: monospace;">{{ old('coins:afk:ad_slot_html', config('coins.afk.ad_slot_html')) }}</textarea>
                                <p class="text-muted small">
                                    @lang('admin/settings_coins.afk.ad_slot_description')
                                    <strong>@lang('admin/settings_coins.afk.ad_slot_warning_bold')</strong>@lang('admin/settings_coins.afk.ad_slot_warning')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.shop_resources.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.memory_unit_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="coins:shop:memory_unit_mib" value="{{ old('coins:shop:memory_unit_mib', config('coins.shop.memory_unit_mib')) }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.memory_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:shop:memory_price" value="{{ old('coins:shop:memory_price', config('coins.shop.memory_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.disk_unit_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="coins:shop:disk_unit_mib" value="{{ old('coins:shop:disk_unit_mib', config('coins.shop.disk_unit_mib')) }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.disk_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:shop:disk_price" value="{{ old('coins:shop:disk_price', config('coins.shop.disk_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.cpu_unit_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="coins:shop:cpu_unit_percent" value="{{ old('coins:shop:cpu_unit_percent', config('coins.shop.cpu_unit_percent')) }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.cpu_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:shop:cpu_price" value="{{ old('coins:shop:cpu_price', config('coins.shop.cpu_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.backup_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:shop:backup_price" value="{{ old('coins:shop:backup_price', config('coins.shop.backup_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_resources.slot_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:shop:slot_price" value="{{ old('coins:shop:slot_price', config('coins.shop.slot_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <p class="text-muted small">@lang('admin/settings_coins.shop_resources.pool_notice')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.daily.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.daily.reward_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:daily:reward" value="{{ old('coins:daily:reward', config('coins.daily.reward')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.daily.reward_description')</p>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.daily.streak_bonus_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:daily:streak_bonus" value="{{ old('coins:daily:streak_bonus', config('coins.daily.streak_bonus')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.daily.streak_bonus_description')</p>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.daily.streak_max_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" max="365" class="form-control" name="coins:daily:streak_max" value="{{ old('coins:daily:streak_max', config('coins.daily.streak_max')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.daily.days_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.daily.streak_max_description')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.referral.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="form-group col-md-6">
                            <label class="control-label">@lang('admin/settings_coins.referral.referrer_reward_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:referral:referrer_reward" value="{{ old('coins:referral:referrer_reward', config('coins.referral.referrer_reward')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.referral.referrer_reward_description')</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">@lang('admin/settings_coins.referral.referred_bonus_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:referral:referred_bonus" value="{{ old('coins:referral:referred_bonus', config('coins.referral.referred_bonus')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_resources.coins_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.referral.referred_bonus_description')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_coins.shop_server.heading')</h3>
                    </div>
                    <div class="box-body row">
                        <div class="col-xs-12"><div class="alert alert-info">{!! trans('admin/settings_coins.shop_server.plans_notice', ['link' => '<a href="' . route('admin.plans') . '">' . trans('admin/layout.nav.plans') . '</a>']) !!}</div></div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.monthly_price_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="coins:server:monthly_price" value="{{ old('coins:server:monthly_price', config('coins.server.monthly_price')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_server.monthly_price_unit')</span>
                            </div>
                        </div>
                        <div class="col-xs-12"><p class="text-muted small">@lang('admin/settings_coins.shop_server.spec_notice')</p></div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.memory_label')</label>
                            <div class="input-group">
                                <input type="number" min="128" class="form-control" name="coins:server:memory" value="{{ old('coins:server:memory', config('coins.server.memory')) }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.disk_label')</label>
                            <div class="input-group">
                                <input type="number" min="128" class="form-control" name="coins:server:disk" value="{{ old('coins:server:disk', config('coins.server.disk')) }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.cpu_label')</label>
                            <div class="input-group">
                                <input type="number" min="25" class="form-control" name="coins:server:cpu" value="{{ old('coins:server:cpu', config('coins.server.cpu')) }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.backups_label')</label>
                            <div>
                                <input type="number" min="0" class="form-control" name="coins:server:backups" value="{{ old('coins:server:backups', config('coins.server.backups')) }}"/>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.grace_period_label')</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="coins:server:suspension_grace_days" value="{{ old('coins:server:suspension_grace_days', config('coins.server.suspension_grace_days')) }}"/>
                                <span class="input-group-addon">@lang('admin/settings_coins.shop_server.grace_period_unit')</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.shop_server.grace_period_description')</p>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.refund_label')</label>
                            <div class="input-group">
                                <input type="number" min="0" max="100" class="form-control" name="coins:server:cancellation_refund_percent" value="{{ old('coins:server:cancellation_refund_percent', config('coins.server.cancellation_refund_percent')) }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.shop_server.refund_description')</p>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="control-label">@lang('admin/settings_coins.shop_server.pool_toggle_label')</label>
                            <div>
                                @php $countsTowardsPool = old('coins:server:count_towards_pool', config('coins.server.count_towards_pool') ? 'true' : 'false'); @endphp
                                <select name="coins:server:count_towards_pool" class="form-control">
                                    <option value="false" @if($countsTowardsPool === 'false') selected @endif>@lang('admin/settings_coins.shop_server.pool_toggle_no')</option>
                                    <option value="true" @if($countsTowardsPool === 'true') selected @endif>@lang('admin/settings_coins.shop_server.pool_toggle_yes')</option>
                                </select>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_coins.shop_server.pool_toggle_description')</p>
                        </div>
                    </div>
                    <div class="box-footer">
                        {{ csrf_field() }}
                        <button type="button" id="saveButton" class="btn btn-sm btn-primary pull-right">@lang('strings.save')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        function saveSettings() {
            var data = {};
            $('form input[name], form textarea[name], form select[name]').each(function () {
                data[$(this).attr('name')] = $(this).val();
            });

            return $.ajax({
                method: 'PATCH',
                url: '/admin/settings/coins',
                contentType: 'application/json',
                data: JSON.stringify(data),
                headers: { 'X-CSRF-Token': $('input[name="_token"]').val() }
            }).fail(function (jqXHR) {
                var errorText = '';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    $.each(jqXHR.responseJSON.errors, function (i, v) {
                        if (v.detail) errorText += v.detail + ' ';
                    });
                } else {
                    errorText = jqXHR.responseText;
                }

                swal({ title: '{{ trans('admin/settings_coins.js.error_title') }}', text: '{{ trans('admin/settings_coins.js.error_text', ['error' => '']) }}' + errorText, type: 'error' });
            });
        }

        $(document).ready(function () {
            $('#saveButton').on('click', function () {
                saveSettings().done(function () {
                    swal({ title: '{{ trans('admin/settings_coins.js.success_title') }}', text: '{{ trans('admin/settings_coins.js.success_text') }}', type: 'success' });
                });
            });
        });
    </script>
@endsection
