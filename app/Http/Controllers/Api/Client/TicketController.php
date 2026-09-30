<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\TicketMessage;
use Pterodactyl\Exceptions\DisplayException;

class TicketController extends ClientApiController
{
    private const MAX_OPEN_TICKETS = 5;

    /**
     * Lists the requesting user's tickets, most recently active first.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->orderByRaw("status = 'closed'")
            ->orderByDesc('last_reply_at')
            ->paginate(25);

        return new JsonResponse([
            'data' => collect($tickets->items())->map(fn (Ticket $t) => $this->summary($t)),
            'meta' => [
                'currentPage' => $tickets->currentPage(),
                'lastPage' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Opens a new ticket together with its first message.
     *
     * @throws DisplayException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|min:3|max:191',
            'category' => 'required|string|in:' . implode(',', Ticket::CATEGORIES),
            'priority' => 'sometimes|string|in:low,normal,high',
            'serverId' => 'nullable|integer',
            'message' => 'required|string|min:5|max:5000',
        ]);

        $user = $request->user();

        $open = Ticket::query()->where('user_id', $user->id)->where('status', '!=', Ticket::STATUS_CLOSED)->count();
        if ($open >= self::MAX_OPEN_TICKETS) {
            throw new DisplayException(trans('tickets.errors.too_many_open', ['max' => self::MAX_OPEN_TICKETS]));
        }

        $serverId = null;
        if (!empty($data['serverId'])) {
            $serverId = Server::query()->where('id', $data['serverId'])->where('owner_id', $user->id)->value('id');
        }

        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'server_id' => $serverId,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'normal',
            'status' => Ticket::STATUS_OPEN,
            'last_reply_at' => now(),
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $data['message'],
            'is_staff' => false,
        ]);

        $ticket->notifyStaff(true);

        return new JsonResponse($this->detail($ticket), 201);
    }

    public function view(Request $request, int $ticket): JsonResponse
    {
        return new JsonResponse($this->detail($this->findOwned($request, $ticket)));
    }

    /**
     * Adds a reply from the ticket's owner.
     *
     * @throws DisplayException
     */
    public function reply(Request $request, int $ticket): JsonResponse
    {
        $ticket = $this->findOwned($request, $ticket);
        $data = $request->validate(['message' => 'required|string|min:2|max:5000']);

        if ($ticket->isClosed()) {
            throw new DisplayException(trans('tickets.errors.closed'));
        }

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['message'],
            'is_staff' => false,
        ]);

        $ticket->update(['status' => Ticket::STATUS_CUSTOMER_REPLY, 'last_reply_at' => now()]);
        $ticket->notifyStaff(false);

        return new JsonResponse($this->detail($ticket->refresh()));
    }

    public function close(Request $request, int $ticket): JsonResponse
    {
        $ticket = $this->findOwned($request, $ticket);
        $ticket->update(['status' => Ticket::STATUS_CLOSED, 'closed_at' => now()]);

        return new JsonResponse($this->detail($ticket->refresh()));
    }

    /**
     * Lets the ticket's owner say whether the help they got was useful (only once it's closed).
     *
     * @throws DisplayException
     */
    public function rate(Request $request, int $ticket): JsonResponse
    {
        $ticket = $this->findOwned($request, $ticket);
        $data = $request->validate(['rating' => 'required|string|in:up,down']);

        if (!$ticket->isClosed()) {
            throw new DisplayException(trans('tickets.errors.rate_open'));
        }

        $ticket->update(['rating' => $data['rating'] === 'up' ? 1 : -1, 'rated_at' => now()]);

        return new JsonResponse($this->detail($ticket->refresh()));
    }

    public function reopen(Request $request, int $ticket): JsonResponse
    {
        $ticket = $this->findOwned($request, $ticket);
        $ticket->update(['status' => Ticket::STATUS_OPEN, 'closed_at' => null, 'last_reply_at' => now(), 'rating' => null, 'rated_at' => null]);

        return new JsonResponse($this->detail($ticket->refresh()));
    }

    /**
     * Servers the user may attach to a new ticket.
     */
    public function servers(Request $request): JsonResponse
    {
        return new JsonResponse(
            Server::query()->where('owner_id', $request->user()->id)->orderBy('name')->get(['id', 'name'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
        );
    }

    private function findOwned(Request $request, int $id): Ticket
    {
        return Ticket::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    private function summary(Ticket $t): array
    {
        return [
            'id' => $t->id,
            'subject' => $t->subject,
            'category' => $t->category,
            'priority' => $t->priority,
            'status' => $t->status,
            'lastReplyAt' => optional($t->last_reply_at)->toIso8601String(),
            'createdAt' => $t->created_at->toIso8601String(),
            'rating' => $t->rating,
        ];
    }

    private function detail(Ticket $t): array
    {
        $t->loadMissing(['messages.author', 'server']);

        return $this->summary($t) + [
            'serverName' => optional($t->server)->name,
            'messages' => $t->messages->where('is_internal', false)->values()->map(fn (TicketMessage $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'isStaff' => $m->is_staff,
                'author' => optional($m->author)->username ?? '—',
                'createdAt' => $m->created_at->toIso8601String(),
            ]),
        ];
    }
}
