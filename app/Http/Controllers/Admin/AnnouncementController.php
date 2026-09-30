<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Pterodactyl\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AnnouncementFormRequest;

class AnnouncementController extends Controller
{
    public function __construct(protected AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::query()->orderByDesc('created_at')->get(),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function create(AnnouncementFormRequest $request): RedirectResponse
    {
        Announcement::query()->create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'is_active' => true,
        ]);

        $this->alert->success('Announcement was created successfully.')->flash();

        return redirect()->route('admin.announcements');
    }

    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        return redirect()->route('admin.announcements');
    }

    /**
     * @throws \Exception
     */
    public function delete(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        $this->alert->success('Announcement was deleted successfully.')->flash();

        return redirect()->route('admin.announcements');
    }
}
