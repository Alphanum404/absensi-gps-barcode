<?php

namespace App\Livewire;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Support\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $event_id = null;
    public $events = null;
    public ?array $currentLiveCoords = null;
    public string $successMsg = '';
    public bool $isAbsence = false;
    public $hasUpcomingEvents = false;

    public function scan(string $barcode)
    {
        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        } else if (is_null($this->event_id)) {
            return __('Invalid event');
        }

        /** @var Barcode */
        $barcode = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcode) {
            return 'Invalid barcode';
        }

        $barcodeLocation = new LatLong($barcode->latLng['lat'], $barcode->latLng['lng']);
        $userLocation = new LatLong($this->currentLiveCoords[0], $this->currentLiveCoords[1]);

        if (($distance = $this->calculateDistance($userLocation, $barcodeLocation)) > $barcode->radius) {
            return __('Location out of range') . ": $distance" . "m. Max: $barcode->radius" . "m";
        }

        /** @var Attendance */
        $existingAttendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->where('barcode_id', $barcode->id)
            ->first();

        if (!$existingAttendance) {
            $attendance = $this->createAttendance($barcode);
            $this->successMsg = __('Attendance In Successful');
        } else {
            $attendance = $existingAttendance;
            $attendance->update([
                'time_out' => date('H:i:s'),
            ]);
            $this->successMsg = __('Attendance Out Successful');
        }

        if ($attendance) {
            $this->setAttendance($attendance->fresh());
            Attendance::clearUserAttendanceCache(Auth::user(), Carbon::parse($attendance->date));
            return true;
        }
    }

    public function calculateDistance(LatLong $a, LatLong $b)
    {
        $distanceCalculator = new DistanceCalculator($a, $b);
        $distanceInMeter = floor($distanceCalculator->get()->asKilometres() * 1000); // convert to meters
        return $distanceInMeter;
    }

    /** @return Attendance */
    public function createAttendance(Barcode $barcode)
    {
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $timeIn = $now->format('H:i:s');
        /** @var Event */
        $event = Event::find($this->event_id);
        $status = Carbon::now()->setTimeFromTimeString($event->start_time)->lt($now) ? 'late' : 'present';
        return Attendance::create([
            'user_id' => Auth::user()->id,
            'barcode_id' => $barcode->id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => null,
            'event_id' => $event->id,
            'latitude' => doubleval($this->currentLiveCoords[0]),
            'longitude' => doubleval($this->currentLiveCoords[1]),
            'status' => $status,
            'note' => null,
            'attachment' => null,
        ]);
    }

    protected function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->event_id = $attendance->event_id;
        $this->isAbsence = $attendance->status !== 'present' && $attendance->status !== 'late';
    }

    public function getAttendance()
    {
        if (is_null($this->attendance)) {
            return null;
        }
        return [
            'time_in' => $this->attendance?->time_in,
            'time_out' => $this->attendance?->time_out,
        ];
    }

    // Update the mount method to get events
    public function mount()
    {
        // Get events for today or recurring events
        $this->events = Event::where(function ($query) {
            $query->whereDate('event_date', now()->toDateString())
                ->orWhere('is_recurring', true);
        })->get();

        // Check if user has already attended today
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))->first();
        if ($attendance) {
            $this->setAttendance($attendance);
        } else {
            // Set default event if available
            if ($this->events->isNotEmpty()) {
                $closest = ExtendedCarbon::now()
                    ->closestFromDateArray($this->events->pluck('start_time')->toArray());

                $this->event_id = $this->events
                    ->where(fn(Event $event) => $event->start_time == $closest->format('H:i:s'))
                    ->first()->id ?? null;
            }
        }

        // Set nilai $hasUpcomingEvents
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $this->hasUpcomingEvents = Event::where(function ($query) use ($today, $tomorrow) {
            $query->whereDate('event_date', $today)
                ->orWhereDate('event_date', $tomorrow);
        })->exists();
    }

    public function render()
    {
        return view('livewire.scan', [
            'hasUpcomingEvents' => $this->hasUpcomingEvents,
        ]);
    }
}
