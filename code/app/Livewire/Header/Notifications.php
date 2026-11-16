<?php

namespace App\Livewire\Header;

use Livewire\Component;

class Notifications extends Component
{
    public function open(int $id)
    {
        $notification = auth()->user()->notifications()->find($id);
        abort_unless($notification, 404);
        auth()->user()->unreadNotifications()->updateExistingPivot($id, ['read_at' => now()]);

        return $this->redirect($notification->url);
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        $user->unreadNotifications()->updateExistingPivot(
            $user->unreadNotifications()->pluck('notifications.id')->all(),
            ['read_at' => now()]
        );
    }

    public function render()
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();

        return view('livewire.header.notifications', [
            'notifications' => $user->notifications()->latest('notifications.id')->limit(30)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
