<?php

namespace App\Livewire;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AttendanceHistoryComponent extends Component
{
    use AttendanceDetailTrait;

    public ?string $month;

    public function mount()
    {
        $this->month = now()->format('Y-m');

        // Force cache refresh for the current month
        $this->clearAttendanceCache();
    }

    protected function clearAttendanceCache()
    {
        $user = auth()->user();
        $date = Carbon::parse($this->month);

        // Clear user-specific attendance cache for the current month
        $cacheKey = "attendance-$user->id-$date->month-$date->year";
        Cache::forget($cacheKey);
    }

    // Also add this method to force refresh
    public function refreshData()
    {
        $this->clearAttendanceCache();
    }

    public function render()
    {
        $user = auth()->user();
        $date = Carbon::parse($this->month);

        // Get all events for the current month
        $events = \App\Models\Event::whereMonth('event_date', $date->month)
            ->whereYear('event_date', $date->year)
            ->orWhere('is_recurring', true)
            ->get()
            ->pluck('event_date')
            ->map(function ($eventDate) {
                return $eventDate ? $eventDate->format('Y-m-d') : null;
            })
            ->filter()
            ->toArray();

        $start = Carbon::parse($this->month)->startOfMonth();
        $end = Carbon::parse($this->month)->endOfMonth();
        $dates = $start->range($end)->toArray();

        $attendances = new Collection(Cache::remember(
            "attendance-$user->id-$date->month-$date->year",
            now()->addDay(),
            function () use ($user) {
                /** @var Collection<Attendance>  */
                $attendances = Attendance::filter(
                    month: $this->month,
                    userId: $user->id,
                )->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note', 'event_id']);

                return $attendances->map(
                    function (Attendance $v) {
                        $v->setAttribute('coordinates', $v->lat_lng);
                        $v->setAttribute('lat', $v->latitude);
                        $v->setAttribute('lng', $v->longitude);
                        if ($v->attachment) {
                            $v->setAttribute('attachment', $v->attachment_url);
                        }

                        // Add event details
                        if ($v->event) {
                            $v->setAttribute('event_name', $v->event->name);
                            $v->setAttribute('event_time', $v->event->start_time->format('H:i') . '-' . $v->event->end_time->format('H:i'));
                            $v->setAttribute('event_location', $v->event->location);
                        }

                        return $v->getAttributes();
                    }
                )->toArray();
            }
        ) ?? []);
        $attendanceToday = $attendances->firstWhere(fn($v, $_) => $v['date'] === Carbon::now()->format('Y-m-d'));
        return view('livewire.attendance-history', [
            'attendances' => $attendances,
            'attendanceToday' => $attendanceToday,
            'dates' => $dates,
            'start' => $start,
            'end' => $end,
            'events' => $events,
        ]);
    }
}
