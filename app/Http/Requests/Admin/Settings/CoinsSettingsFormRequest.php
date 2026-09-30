<?php

namespace Pterodactyl\Http\Requests\Admin\Settings;

use Illuminate\Validation\Rule;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class CoinsSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return rules to validate coin system settings POST data against.
     */
    public function rules(): array
    {
        return [
            'coins:linkvertise:user_id' => 'nullable|string|max:191',
            'coins:linkvertise:reward' => 'required|integer|min:0',
            'coins:linkvertise:daily_limit' => 'required|integer|min:1',
            'coins:afk:reward_per_minute' => 'required|integer|min:0',
            'coins:afk:ad_slot_html' => 'nullable|string',
            'coins:shop:memory_unit_mib' => 'required|integer|min:1',
            'coins:shop:memory_price' => 'required|integer|min:0',
            'coins:shop:disk_unit_mib' => 'required|integer|min:1',
            'coins:shop:disk_price' => 'required|integer|min:0',
            'coins:shop:cpu_unit_percent' => 'required|integer|min:1',
            'coins:shop:cpu_price' => 'required|integer|min:0',
            'coins:shop:backup_price' => 'required|integer|min:0',
            'coins:shop:slot_price' => 'required|integer|min:0',
            'coins:server:monthly_price' => 'required|integer|min:0',
            'coins:server:memory' => 'required|integer|min:128',
            'coins:server:disk' => 'required|integer|min:128',
            'coins:server:cpu' => 'required|integer|min:25',
            'coins:server:backups' => 'required|integer|min:0',
            'coins:server:suspension_grace_days' => 'required|integer|min:1',
            'coins:server:count_towards_pool' => ['required', Rule::in(['true', 'false'])],
            'coins:server:cancellation_refund_percent' => 'required|integer|min:0|max:100',
            'coins:daily:reward' => 'required|integer|min:0',
            'coins:daily:streak_bonus' => 'required|integer|min:0',
            'coins:daily:streak_max' => 'required|integer|min:1|max:365',
            'coins:referral:referrer_reward' => 'required|integer|min:0',
            'coins:referral:referred_bonus' => 'required|integer|min:0',
        ];
    }

    public function normalize(?array $only = null): array
    {
        return $this->only(array_keys($this->rules()));
    }
}
