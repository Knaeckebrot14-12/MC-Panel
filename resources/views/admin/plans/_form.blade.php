@php $plan = $plan ?? null; @endphp
<div class="box-body">
    <div class="form-group">
        <label class="control-label">@lang('admin/plans.form.name')</label>
        <input type="text" name="name" maxlength="191" value="{{ old('name', optional($plan)->name) }}" class="form-control" required>
    </div>
    <div class="form-group">
        <label class="control-label">@lang('admin/plans.form.description') <span class="field-optional"></span></label>
        <input type="text" name="description" maxlength="191" value="{{ old('description', optional($plan)->description) }}" class="form-control">
    </div>
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.memory')</label>
            <div class="input-group"><input type="number" name="memory" min="128" value="{{ old('memory', optional($plan)->memory ?? 2048) }}" class="form-control" required><span class="input-group-addon">MiB</span></div>
        </div>
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.disk')</label>
            <div class="input-group"><input type="number" name="disk" min="128" value="{{ old('disk', optional($plan)->disk ?? 5120) }}" class="form-control" required><span class="input-group-addon">MiB</span></div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.cpu')</label>
            <div class="input-group"><input type="number" name="cpu" min="0" value="{{ old('cpu', optional($plan)->cpu ?? 100) }}" class="form-control" required><span class="input-group-addon">%</span></div>
        </div>
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.backups')</label>
            <input type="number" name="backups" min="0" value="{{ old('backups', optional($plan)->backups ?? 1) }}" class="form-control" required>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.price')</label>
            <div class="input-group"><input type="number" name="monthly_price" min="0" value="{{ old('monthly_price', optional($plan)->monthly_price ?? 300) }}" class="form-control" required><span class="input-group-addon">@lang('admin/plans.form.price_unit')</span></div>
        </div>
        <div class="form-group col-xs-6">
            <label class="control-label">@lang('admin/plans.form.sort_order')</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', optional($plan)->sort_order ?? 0) }}" class="form-control">
        </div>
    </div>
    <div class="checkbox checkbox-primary">
        <input id="planActive{{ optional($plan)->id }}" type="checkbox" name="active" value="1" @if(old('active', $plan ? $plan->active : true)) checked @endif>
        <label for="planActive{{ optional($plan)->id }}">@lang('admin/plans.form.active')</label>
    </div>
</div>
