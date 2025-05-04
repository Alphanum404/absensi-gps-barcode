<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Get active event for today
        $event = Event::where(function ($query) {
            $query->whereDate('event_date', now()->toDateString());
        })->orderBy('start_time')->first();

        return view('home', compact('event'));
    }

    // Tambahkan method ini untuk memeriksa apakah ada event yang akan datang
    public function hasUpcomingEvents()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        return Event::where(function ($query) use ($today, $tomorrow) {
            $query->whereDate('event_date', $today)
                ->orWhereDate('event_date', $tomorrow);
        })->exists();
    }
}
