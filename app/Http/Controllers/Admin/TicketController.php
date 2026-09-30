<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\TicketMessage;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Notifications\TicketStaffReplied;
use Pterodactyl\Services\StaffAudit;

class TicketController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(Request $request): View
    {
        $query = Ticket::query()->with(['user', 'assignee']);

        $status = $request->query('status', 'active');
        if ($status === 'active') {
            $query->where('status', '!=', Ticket::STATUS_CLOSED);
        } elseif (in_array($status, Ticket::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($request->query('assigned') === 'me') {
            $query->where('assigned_to', $request->user()->id);
        } elseif ($request->query('assigned') === 'none') {
            $query->whereNull('assigned_to');
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $tickets = $query
            ->orderByRaw("status = 'closed'")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('last_reply_at')
            ->paginate(30)
            ->appends($request->query());

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'ratingStats' => [
                'total' => $rated = Ticket::query()->whereNotNull('rating')->count(),
                'percent' => $rated > 0 ? (int) round(Ticket::query()->where('rating', '>', 0)->count() / $rated * 100) : 0,
            ],
            'filters' => ['status' => $status, 'assigned' => $request->query('assigned'), 'search' => $search],
        ]);
    }

    public function view(Ticket $ticket): View
    {
        $ticket->load(['user', 'assignee', 'server', 'messages.author']);

        $staff = User::query()->where('root_admin', true)->orWhere('role', '!=', 'user')->orderBy('username')->get();

        return view('admin.tickets.view', ['ticket' => $ticket, 'staff' => $staff]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => 'required|string|min:1|max:5000',
            'internal' => 'nullable|boolean',
            'after' => 'nullable|string|in:answered,closed,keep',
        ]);

        $internal = (bool) ($data['internal'] ?? false);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['message'],
            'is_staff' => true,
            'is_internal' => $internal,
        ]);

        $updates = [];
        if (!$internal) {
            $updates['last_reply_at'] = now();
            $after = $data['after'] ?? 'answered';
            if ($after === 'closed') {
                $updates += ['status' => Ticket::STATUS_CLOSED, 'closed_at' => now()];
            } elseif ($after === 'answered') {
                $updates += ['status' => Ticket::STATUS_ANSWERED, 'closed_at' => null];
            }
            if (is_null($ticket->assigned_to)) {
                $updates['assigned_to'] = $request->user()->id;
            }
        }
        $ticket->update($updates);
        StaffAudit::record($internal ? 'ticket.note' : 'ticket.replied', '#' . $ticket->id . ' ' . $ticket->subject, [], $ticket->user, $ticket->server);

        if (!$internal) {
            try {
                $ticket->user->notify((new TicketStaffReplied($ticket))->locale($ticket->user->language ?: config('app.default_locale', config('app.locale'))));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $this->alert->success(trans('admin/tickets.notices.replied'))->flash();

        return redirect()->route('admin.tickets.view', $ticket->id);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'sometimes|string|in:' . implode(',', Ticket::STATUSES),
            'priority' => 'sometimes|string|in:' . implode(',', Ticket::PRIORITIES),
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        if (array_key_exists('assigned_to', $data) && $data['assigned_to']) {
            $assignee = User::query()->find($data['assigned_to']);
            if (!$assignee || !$assignee->hasStaffPermission('tickets')) {
                abort(422);
            }
        }

        if (isset($data['status'])) {
            $data['closed_at'] = $data['status'] === Ticket::STATUS_CLOSED ? now() : null;
        }

        $ticket->update($data);
        StaffAudit::record('ticket.updated', '#' . $ticket->id . ' ' . $ticket->subject, [], $ticket->user, $ticket->server);

        $this->alert->success(trans('admin/tickets.notices.updated'))->flash();

        return redirect()->route('admin.tickets.view', $ticket->id);
    }
}
