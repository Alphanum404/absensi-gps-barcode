<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAttendanceController extends Controller
{
    public function applyLeave()
    {
        // Cek event yang akan datang (hari ini atau besok)
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $upcomingEvents = Event::where(function ($query) use ($today, $tomorrow) {
            $query->whereDate('event_date', $today)
                ->orWhereDate('event_date', $tomorrow);
        })->get();

        // Jika tidak ada event yang akan datang, redirect dengan pesan
        if ($upcomingEvents->isEmpty()) {
            return redirect()->route('home')
                ->with('flash.banner', __('Tidak ada event yang tersedia untuk pengajuan izin.'))
                ->with('flash.bannerStyle', 'danger');
        }

        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->first();

        return view('attendances.apply-leave', [
            'attendance' => $attendance,
            'upcomingEvents' => $upcomingEvents
        ]);
    }

    public function storeLeaveRequest(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:excused,sick'],
            'note' => ['required', 'string', 'max:255'],
            'from' => ['required', 'date'],
            'event_id' => ['required', 'exists:events,id'], // Tambahkan validasi event_id
            'attachment' => ['nullable', 'file', 'max:3072'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        try {
            // Periksa apakah event benar-benar untuk hari ini atau besok
            $event = Event::findOrFail($request->event_id);
            $eventDate = Carbon::parse($event->event_date);
            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            if (!($eventDate->isSameDay($today) || $eventDate->isSameDay($tomorrow))) {
                return redirect()->back()
                    ->with('flash.banner', __('Event yang dipilih tidak valid untuk pengajuan izin.'))
                    ->with('flash.bannerStyle', 'danger');
            }

            // Save new attachment file
            $newAttachment = null;
            if ($request->file('attachment')) {
                $newAttachment = $request->file('attachment')->storePublicly(
                    'attachments',
                    ['disk' => config('jetstream.attachment_disk')]
                );
            }

            // Gunakan tanggal event untuk tanggal absensi
            $date = Carbon::parse($event->event_date);

            $existing = Attendance::where('user_id', Auth::user()->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => $request->status,
                    'note' => $request->note,
                    'attachment' => $newAttachment ?? $existing->attachment,
                    'latitude' => doubleval($request->lat) ?? $existing->latitude,
                    'longitude' => doubleval($request->lng) ?? $existing->longitude,
                    'event_id' => $request->event_id, // Hubungkan dengan event
                ]);
            } else {
                Attendance::create([
                    'user_id' => Auth::user()->id,
                    'status' => $request->status,
                    'date' => $date->format('Y-m-d'),
                    'note' => $request->note,
                    'attachment' => $newAttachment ?? null,
                    'latitude' => $request->lat ? doubleval($request->lat) : null,
                    'longitude' => $request->lng ? doubleval($request->lng) : null,
                    'event_id' => $request->event_id, // Hubungkan dengan event
                ]);
            }

            Attendance::clearUserAttendanceCache(Auth::user(), $date);

            return redirect(route('home'))
                ->with('flash.banner', __('Created successfully.'));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('flash.banner', $th->getMessage())
                ->with('flash.bannerStyle', 'danger');
        }
    }

    public function history()
    {
        return view('attendances.history');
    }

    public function getAttendancesWithEvent()
    {
        $attendances = Attendance::with('event')
            ->filter(/* ... */)
            ->get()
            ->map(function ($attendance) {
                // Tambahkan informasi event ke data
                $eventData = null;
                if ($attendance->event) {
                    $eventData = [
                        'id' => $attendance->event->id,
                        'name' => $attendance->event->name,
                        'event_date' => $attendance->event->event_date,
                        'start_time' => $attendance->event->start_time->format('H:i'),
                        'end_time' => $attendance->event->end_time->format('H:i'),
                        'location' => $attendance->event->location,
                    ];
                }

                return [
                    'id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'status' => $attendance->status,
                    'date' => $attendance->date,
                    // ... fields lainnya
                    'event' => $eventData,
                ];
            });

        return $attendances;
    }
}
