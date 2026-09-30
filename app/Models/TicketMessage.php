<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    public const RESOURCE_NAME = 'ticket_message';

    protected $table = 'ticket_messages';

    protected $fillable = ['ticket_id', 'user_id', 'body', 'is_staff', 'is_internal'];

    protected $casts = [
        'ticket_id' => 'integer',
        'user_id' => 'integer',
        'is_staff' => 'boolean',
        'is_internal' => 'boolean',
    ];

    public static array $validationRules = [
        'ticket_id' => 'required|integer|exists:tickets,id',
        'user_id' => 'nullable|integer|exists:users,id',
        'body' => 'required|string|max:5000',
        'is_staff' => 'boolean',
        'is_internal' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
