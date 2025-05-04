<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      Pengajuan Izin Baru
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 lg:p-8">
          <div class="mb-4">
            <x-secondary-button href="{{ url()->previous() }}">
              <x-heroicon-o-chevron-left class="mr-2 h-5 w-5" />
              Kembali
            </x-secondary-button>
          </div>
          <form action="{{ route('store-leave-request') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <div>
                  <x-label for="status" value="{{ __('Status') }}" />
                  <x-select id="status" class="mt-1 block w-full" name="status" required>
                    <option value="excused" {{ old('status') === 'excused' ? 'selected' : '' }}>
                      Izin
                    </option>
                    <option value="sick" {{ old('status') === 'sick' ? 'selected' : '' }}>
                      Sakit
                    </option>
                  </x-select>
                  @error('status')
            <x-input-error for="status" class="mt-2" message="{{ $message }}" />
          @enderror
                </div>

                <div class="mt-4">
                  <x-label for="event_id" value="Pilih Event" />
                  <x-select id="event_id" class="mt-1 block w-full" name="event_id" required>
                    <option value="">{{ __('Select Event') }}</option>
                    @foreach ($upcomingEvents as $event)
                <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                  {{ $event->name }}
                  ({{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }})
                  {{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}
                  @if($event->location)
              | {{ $event->location }}
            @endif
                </option>
          @endforeach
                  </x-select>
                  @error('event_id')
            <x-input-error for="event_id" class="mt-2" message="{{ $message }}" />
          @enderror
                </div>

                <div class="mt-4">
                  <x-label for="note" value="Keterangan" />
                  <x-textarea id="note" type="text" class="mt-1 block w-full" name="note"
                    required>{{ old('note') }}</x-textarea>
                  <x-input-error for="note" class="mt-2" />
                </div>
              </div>

              <div x-data="{ filename: null, preview: null }">
                <input type="file" value="{{ old('attachment') }}" class="hidden" id="attachment" name="attachment"
                  x-ref="attachment" x-on:change="
                                filename = $refs.attachment.files[0].name;
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    preview = e.target.result;
                                };
                                reader.readAsDataURL($refs.attachment.files[0]);
                            " />

                <x-label for="attachment" value="{{ __('Attachment') }}" />

                <div class="mb-2 mt-2" x-show="preview" style="display: none;">
                  <span class="block h-48 max-h-72 w-full bg-contain bg-left bg-no-repeat"
                    x-bind:style="'background-image: url(\'' + preview + '\');'">
                  </span>
                </div>

                @if ($attendance?->attachment)
          <div class="mb-2 mt-2" x-show="!preview">
            <img class="block h-48 max-h-72 w-full object-contain object-left"
            src="{{ $attendance?->attachment_url }}" />
          </div>
        @endif

                <x-secondary-button class="me-2 mt-2" type="button" x-on:click.prevent="$refs.attachment.click()">
                  {{ __('Select Attachment') }}
                </x-secondary-button>

                <x-secondary-button type="button" class="mt-2" x-show="preview"
                  x-on:click="filename = null; preview = null">
                  {{ __('Remove Attachment') }}
                </x-secondary-button>

                <x-input-error for="attachment" class="mt-2" />
              </div>
            </div>

            <!-- Field from sudah tidak perlu karena kita menggunakan tanggal dari event -->
            <input type="hidden" name="from" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">

            <input type="hidden" id="lat" name="lat" value="{{ $attendance?->latitude }}">
            <input type="hidden" id="lng" name="lng" value="{{ $attendance?->longitude }}">

            <div class="mb-3 mt-4 flex items-center justify-end">
              <x-button class="ms-4">
                {{ __('Save') }}
              </x-button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  @pushOnce('scripts')
    <script>
    getLocation();

    async function getLocation() {
      if (navigator.geolocation) {
      navigator.geolocation.watchPosition((position) => {
        console.log(position);
        document.getElementById('lat').value = position.coords.latitude;
        document.getElementById('lng').value = position.coords.longitude;
      }, (err) => {
        console.error(`ERROR(${err.code}): ${err.message}`);
        alert('{{ __('Please enable your location') }}');
      });
      }
    }
    </script>
  @endPushOnce
</x-app-layout>