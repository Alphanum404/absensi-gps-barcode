<div>
  @pushOnce('styles')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  @endpushOnce
  <h3 class="col-span-2 mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
    Data Absensi
  </h3>
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
    <x-label for="month_filter" value="Bulan"></x-label>
    <x-input type="month" name="month_filter" id="month_filter" wire:model.live="month" />
  </div>
  <h5 class="mt-3 text-sm dark:text-gray-200">Klik pada tanggal untuk melihat detail</h5>
  <div class="mt-4 flex w-full flex-col gap-3 lg:flex-row">
    <!-- Responsive calendar container -->
    <div class="w-full max-w-full overflow-hidden lg:max-w-[36rem]">
      <div class="grid grid-cols-7 dark:text-white">
        @foreach (['M', 'S', 'S', 'R', 'K', 'J', 'S'] as $day)
      <div
        class="{{ $day === 'M' ? 'text-red-500' : '' }} {{ $day === 'J' ? 'text-green-600 dark:text-green-500' : '' }} flex h-10 items-center justify-center border border-gray-300 text-center dark:border-gray-600">
        {{ $day }}
      </div>
    @endforeach
        @if ($start->dayOfWeek !== 0)
        @foreach (range(1, $start->dayOfWeek) as $i)
        <div
        class="aspect-square h-14 border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-700 sm:h-16">
        </div>
      @endforeach
    @endif
        @php
      $presentCount = 0;
      $lateCount = 0;
      $excusedCount = 0;
      $sickCount = 0;
      $absentCount = 0;
    @endphp
        @foreach ($dates as $date)
        @php
        // Find attendance for the current date
        $dateStr = $date->format('Y-m-d');
        $attendance = collect($attendances)->where('date', $dateStr)->first();
        $hasEvent = in_array($dateStr, $events);

        // By default, use neutral styling with no highlights
        $shortStatus = '';
        $bgColor = 'border border-gray-300 dark:border-gray-600';

        // Only highlight if there's an attendance record
        if ($attendance) {
        switch ($attendance['status'] ?? null) {
          case 'present':
          $shortStatus = 'H';
          $bgColor = 'bg-green-200 dark:bg-green-950 hover:bg-green-100 dark:hover:bg-green-700 border border-green-600';
          $presentCount++;
          break;
          case 'late':
          $shortStatus = 'T';
          $bgColor = 'bg-amber-200 dark:bg-amber-800 hover:bg-amber-300 dark:hover:bg-amber-700 border border-amber-600';
          $lateCount++;
          break;
          case 'excused':
          $shortStatus = 'I';
          $bgColor = 'bg-blue-200 dark:bg-blue-800 hover:bg-blue-300 dark:hover:bg-blue-700 border border-blue-600';
          $excusedCount++;
          break;
          case 'sick':
          $shortStatus = 'S';
          $bgColor = 'bg-purple-200 dark:bg-purple-950 hover:bg-purple-100 dark:hover:bg-purple-700 border border-purple-600';
          $sickCount++;
          break;
          case 'absent':
          $shortStatus = 'A';
          $bgColor = 'bg-red-200 dark:bg-red-950 text-red-500 dark:text-red-200 border border-red-300 dark:border-red-700';
          $absentCount++;
          break;
        }
        } else if ($hasEvent) {
        // There's an event but no attendance - this is an absence
        $shortStatus = 'A';
        $bgColor = 'bg-red-200 dark:bg-red-950 text-red-500 dark:text-red-200 border border-red-300 dark:border-red-700';
        $absentCount++;
        }
      @endphp
        @if ($attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
        <button class="{{ $bgColor }} aspect-square h-14 min-w-full py-1 text-center sm:h-16"
          wire:click="show({{ $attendance['id'] }})"
          onclick="setLocation({{ $attendance['lat'] ?? 0 }}, {{ $attendance['lng'] ?? 0 }})">
          <span
          class="text-base font-medium {{ $date->isSunday() ? 'text-red-500' : '' }} {{ $date->isFriday() ? 'text-green-600 dark:text-green-500' : '' }}">
          {{ $date->format('d') }}
          </span>
          <br>
          @if ($shortStatus)
        <span class="text-sm font-bold">{{ $shortStatus }}</span>
        @endif
          @if(isset($attendance['event_name']))
        <span class="block text-xs truncate">{{ $attendance['event_name'] }}</span>
        @endif
        </button>
      @else
        <div class="{{ $bgColor }} aspect-square h-14 min-w-full py-1 text-center sm:h-16">
          <span
          class="text-base font-medium {{ $date->isSunday() ? 'text-red-500' : '' }} {{ $date->isFriday() ? 'text-green-600 dark:text-green-500' : '' }}">
          {{ $date->format('d') }}
          </span>
          <br>
          @if ($shortStatus)
        <span class="text-sm font-bold">{{ $shortStatus }}</span>
        @endif
        </div>
      @endif
    @endforeach
        @if ($end->dayOfWeek !== 6)
        @foreach (range(5, $end->dayOfWeek) as $i)
        <div
        class="aspect-square h-14 border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-700 sm:h-16">
        </div>
      @endforeach
    @endif
      </div>
    </div>

    <!-- Summary cards -->
    <div class="mt-4 grid w-full grid-cols-2 gap-3 md:grid-cols-4 lg:mt-0">
      <div
        class="flex items-center justify-between rounded-md bg-green-200 px-4 py-2 text-gray-800 dark:bg-green-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-lg font-semibold md:text-xl">Hadir: {{ $presentCount + $lateCount }}</h4>
          Terlambat: {{ $lateCount }}
        </div>
      </div>
      <div
        class="flex items-center justify-between rounded-md bg-blue-200 px-4 py-2 text-gray-800 dark:bg-blue-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-lg font-semibold md:text-xl">Izin: {{ $excusedCount }}</h4>
        </div>
      </div>
      <div
        class="flex items-center justify-between rounded-md bg-purple-200 px-4 py-2 text-gray-800 dark:bg-purple-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-lg font-semibold md:text-xl">Sakit: {{ $sickCount }}</h4>
        </div>
      </div>
      <div
        class="flex items-center justify-between rounded-md bg-red-200 px-4 py-2 text-gray-800 dark:bg-red-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-lg font-semibold md:text-xl">Absen: {{ $absentCount }}</h4>
        </div>
      </div>
    </div>
  </div>

  <x-attendance-detail-modal :current-attendance="$currentAttendance" />
  @stack('attendance-detail-scripts')
</div>