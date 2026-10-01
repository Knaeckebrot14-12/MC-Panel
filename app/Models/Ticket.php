<?php

namespace Pterodactyl\Models;

use Pterodactyl\Notifications\TicketNeedsAttention;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $assigned_to
 * @property int|null $server_id
 * @property string $subject
 * @property string $category
 * @property string $priority
 * @property string $status
 */
class Ticket extends Model
{
    public const RESOURCE_NAME = 'ticket';

    public const STATUS_OPEN = 'open';
    public const STATUS_CUSTOMER_REPLY = 'customer_reply';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [self::STATUS_OPEN, self::STATUS_CUSTOMER_REPLY, self::STATUS_ANSWERED, self::STATUS_CLOSED];
    public const CATEGORIES = ['general', 'technical', 'billing', 'other'];
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    protected $table = 'tickets';

    protected $fillable = ['user_id', 'assigned_to', 'server_id', 'subject', 'category', 'priority', 'status', 'last_reply_at', 'closed_at', 'rating', 'rated_at'];

    protected $casts = [
        'user_id' => 'integer',
        'assigned_to' => 'integer',
        'server_id' => 'integer',
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
        'rated_at' => 'datetime',
        'rating' => 'integer',
    ];

    public static array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'assigned_to' => 'nullable|integer|exists:users,id',
        'server_id' => 'nullable|integer|exists:servers,id',
        'subject' => 'required|string|max:191',
        'category' => 'required|string|in:general,technical,billing,other',
        'priority' => 'required|string|in:low,normal,high,urgent',
        'status' => 'required|string|in:open,customer_reply,answered,closed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('id');
    }

    /**
     * Mails the person responsible for this ticket (or the whole support team if nobody is assigned yet).
     * A broken mail setup must never prevent the ticket itself from being saved.
     */
    public function notifyStaff(bool $isNew): void
    {
        if ($isNew) {
            \Pterodactyl\Services\Notifications\TeamAlerts::newTicket($this);
        }

        $assignee = $this->assignee;

        $recipients = $assignee && $assignee->hasStaffPermission('tickets')
            ? collect([$assignee])
            : User::query()->where('root_admin', true)->orWhere('role', '!=', User::ROLE_USER)->get()
                ->filter(fn (User $u) => $u->hasStaffPermission('tickets'));

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(
                    (new TicketNeedsAttention($this, $isNew))->locale($recipient->language ?: config('app.default_locale', config('app.locale')))
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
