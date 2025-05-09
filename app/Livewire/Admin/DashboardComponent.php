<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;

    public function render()
    {
        /** @var Collection<Attendance>  */
        $attendances = Attendance::where('date', date('Y-m-d'))->get();

        // Check if there are any events today
        $hasEvents = Event::whereDate('event_date', date('Y-m-d'))->exists();

        /** @var Collection<User>  */
        $employees = User::where('group', 'user')
            ->paginate(20)
            ->through(function (User $user) use ($attendances, $hasEvents) {
                $attendance = $attendances
                    ->where(fn(Attendance $attendance) => $attendance->user_id === $user->id)
                    ->first();

                // If no attendance but there are events today, mark as absent
                // Otherwise, don't mark anything (null)
                if (!$attendance && $hasEvents) {
                    $attendance = (object) [
                        'status' => 'absent'
                    ];
                }

                return $user->setAttribute('attendance', $attendance);
            });

        $employeesCount = User::where('group', 'user')->count();
        $presentCount = $attendances->where(fn($attendance) => $attendance->status === 'present')->count();
        $lateCount = $attendances->where(fn($attendance) => $attendance->status === 'late')->count();
        $excusedCount = $attendances->where(fn($attendance) => $attendance->status === 'excused')->count();
        $sickCount = $attendances->where(fn($attendance) => $attendance->status === 'sick')->count();

        // Only count absent if there are events today
        $absentCount = $hasEvents ? $employeesCount - ($presentCount + $lateCount + $excusedCount + $sickCount) : 0;

        return view('livewire.admin.dashboard', [
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            'hasEvents' => $hasEvents,
        ]);
    }
}
