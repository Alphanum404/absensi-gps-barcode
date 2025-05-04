<x-modal wire:model="showDetail" onclose="removeMap()">
  <div class="px-6 py-4">
    @if ($currentAttendance)
      @php
        $isExcused = $currentAttendance['status'] == 'excused' || $currentAttendance['status'] == 'sick';
        $showMap = $currentAttendance['latitude'] && $currentAttendance['longitude'] && !$isExcused;
      @endphp
      <h3 class="mb-3 text-xl font-semibold dark:text-white">{{ $currentAttendance['name'] }}</h3>
      <div class="mb-3 w-full">
        <x-label for="nim" value="{{ __('NIM') }}"></x-label>
        <x-input type="text" class="w-full" id="nim" disabled value="{{ $currentAttendance['nim'] }}"></x-input>
      </div>
      <div class="mb-3 flex w-full gap-3">
        <div class="w-full">
        <x-label for="date" value="{{ __('Date') }}"></x-label>
        <x-input type="text" class="w-full" id="date" disabled value="{{ $currentAttendance['date'] }}"></x-input>
        </div>
        <div class="w-full">
        <x-label for="status" value="{{ __('Status') }}"></x-label>
        <x-input type="text" class="w-full" id="status" disabled
          value="{{ __($currentAttendance['status']) }}"></x-input>
        </div>
      </div>
      @if ($isExcused)
      <div class="mb-3 w-full">
        <x-label for="address" value="{{ __('Address') }}" />
        <x-input type="text" class="w-full" id="address" disabled value="{{ $currentAttendance['address'] }}" />
      </div>
      @endif
      <div class="flex flex-col gap-3">
        @if ($currentAttendance['attachment'])
        <x-label for="attachment" value="{{ __('Attachment') }}"></x-label>
        <img src="{{ $currentAttendance['attachment'] }}" alt="Attachment"
        class="max-h-48 object-contain sm:max-h-64 md:max-h-72">
      @endif
        @if ($currentAttendance['note'])
        <x-label for="note" value="Keterangan" />
        <x-textarea type="text" id="note" disabled value="{{ $currentAttendance['note'] }}" />
      @endif
        @if ($showMap)
        <x-label for="map" value="Koordinat Lokasi Absen"></x-label>
        <p class="dark:text-gray-300">
        {{ $currentAttendance['latitude'] }}, {{ $currentAttendance['longitude'] }}
        </p>
        <div class="my-2 h-52 w-full md:h-64" id="map"></div>
      @endif
        @if ($currentAttendance['time_in'] || $currentAttendance['time_out'])
        <div class="grid grid-cols-2 gap-3">
        <x-label for="time_in" value="Waktu Masuk"></x-label>
        <x-label for="time_out" value="Waktu Keluar"></x-label>
        <x-input type="text" id="time_in" disabled value="{{ $currentAttendance['time_in'] ?? '-' }}"></x-input>
        <x-input type="text" id="time_out" disabled value="{{ $currentAttendance['time_out'] ?? '-' }}"></x-input>
        </div>
      @endif

        <div class="flex gap-3">
        @if ($currentAttendance['event'] ?? false)
        <div class="w-full">
        <x-label for="event" value="Event"></x-label>
        <x-input class="w-full" type="text" id="event" disabled
        value="{{ $currentAttendance['event']['name'] }}"></x-input>
        </div>
      @endif
        @if ($currentAttendance['barcode'] ?? false)
        <div class="w-full">
        <x-label for="barcode" value="Barcode"></x-label>
        <x-input class="w-full" type="text" id="barcode" disabled
        value="{{ $currentAttendance['barcode']['name'] }}"></x-input>
        </div>
      @endif
        </div>

        <!-- Add this section to display event details in the modal -->
        @if (isset($currentAttendance['event_name']))
        <div class="mb-3">
        <x-label for="event" value="{{ __('Event') }}" />
        <x-input type="text" class="w-full" id="event" disabled value="{{ $currentAttendance['event_name'] }}" />
        </div>
        <div class="mb-3 grid grid-cols-2 gap-3">
        <div>
        <x-label for="event_time" value="{{ __('Event Time') }}" />
        <x-input type="text" class="w-full" id="event_time" disabled
        value="{{ $currentAttendance['event_time'] ?? 'N/A' }}" />
        </div>
        <div>
        <x-label for="event_location" value="{{ __('Event Location') }}" />
        <x-input type="text" class="w-full" id="event_location" disabled
        value="{{ $currentAttendance['event_location'] ?? 'N/A' }}" />
        </div>
        </div>
      @endif

      </div>
  @endif
  </div>
</x-modal>

@push('attendance-detail-scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script>
    let map = null;

    function setLocation(lat, lng) {
    removeMap();

    // Check if the map container exists before initializing
    const mapContainer = document.getElementById('map');

    if (!mapContainer) {
      console.warn('Map container not found');
      return;
    }

    setTimeout(() => {
      try {
      map = L.map('map').setView([Number(lat), Number(lng)], 19);
      L.marker([Number(lat), Number(lng)]).addTo(map);
      L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 21,
      }).addTo(map);
      } catch (error) {
      console.error('Error initializing map:', error);
      }
    }, 500);
    }

    function removeMap() {
    if (map !== null) {
      try {
      map.remove();
      } catch (error) {
      console.warn('Error removing map:', error);
      }
    }
    map = null;
    }
  </script>
@endpush