<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste complète des notifications de l'utilisateur connecté.
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(20);

        return view('pages.notifications.index', compact('notifications'));
    }

    /**
     * Marque une notification comme lue puis redirige vers sa cible (si fournie).
     */
    public function read(string $notification)
    {
        $notif = Auth::user()->notifications()->findOrFail($notification);

        if (is_null($notif->read_at)) {
            $notif->markAsRead();
        }

        $url = $notif->data['url'] ?? null;

        return $url ? redirect()->to($url) : back();
    }

    /**
     * Marque toutes les notifications comme lues.
     */
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprime une notification.
     */
    public function destroy(string $notification)
    {
        Auth::user()->notifications()->findOrFail($notification)->delete();

        return back()->with('success', 'Notification supprimée.');
    }
}
