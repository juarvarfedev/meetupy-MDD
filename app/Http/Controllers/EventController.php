<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\MeetupGroups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->with(['meetupGroup.user'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $user = auth()->user();

        return Inertia::render('events/index', [
            'events' => $events,
            'canCreateEvent' => $user ? $user->meetupGroups()->exists() : false,
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load(['meetupGroup.user']);

        $user = auth()->user();

        return Inertia::render('events/show', [
            'event' => $event,
            'canEdit' => $user?->can('update', $event) ?? false,
            'isRsvped' => $user ? $event->users()->where('user_id', $user->id)->exists() : false,
            'attendeesCount' => $event->users()->count(),
        ]);
    }

    public function create(): Response
    {

        $meetupGroups = auth()->user()->meetupGroups()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Without Authorization, you can fetch all meetup groups instead of just the user's groups
        // $meetupGroups = MeetupGroups::query()
        //     ->orderBy('name')
        //     ->get(['id', 'name']);

        return Inertia::render('events/create', [
            'meetupGroups' => $meetupGroups,
        ]);
    }

    public function edit(Event $event): Response
    {
        Gate::authorize('update', $event);

        return Inertia::render('events/edit', [
            'event' => $event
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        $validated = $request->validated();

        $event->update($validated);

        return redirect()->route('events.show', $event);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['slug'] = Str::slug($validated['title']);

        $event = Event::create($validated);

        return redirect()->route('events.show', $event);
    }
}
