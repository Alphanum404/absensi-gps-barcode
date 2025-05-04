<div>
    <!-- Event Form Section -->
    <div class="mb-10 overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ $isEdit ? __('Edit Event') : __('Add New Event') }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Create or modify events for your organization.') }}
            </p>
        </div>

        <form wire:submit.prevent="save" class="px-6 py-5">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Event Name -->
                <div class="col-span-2">
                    <x-label for="name" value="{{ __('Event Name') }}" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name"
                        placeholder="{{ __('Enter event name') }}" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <!-- Event Date -->
                <div>
                    <x-label for="event_date" value="{{ __('Event Date') }}" />
                    <x-input id="event_date" type="date" class="mt-1 block w-full" wire:model="event_date" />
                    <x-input-error for="event_date" class="mt-2" />
                </div>

                <!-- Location -->
                <div>
                    <x-label for="location" value="{{ __('Location') }}" />
                    <x-input id="location" type="text" class="mt-1 block w-full" wire:model="location"
                        placeholder="{{ __('e.g. Laboratory, Meeting Room') }}" />
                    <x-input-error for="location" class="mt-2" />
                </div>

                <!-- Event Times -->
                <div>
                    <x-label for="start_time" value="{{ __('Start Time') }}" />
                    <x-input id="start_time" type="time" class="mt-1 block w-full" wire:model="start_time" />
                    <x-input-error for="start_time" class="mt-2" />
                </div>

                <div>
                    <x-label for="end_time" value="{{ __('End Time') }}" />
                    <x-input id="end_time" type="time" class="mt-1 block w-full" wire:model="end_time" />
                    <x-input-error for="end_time" class="mt-2" />
                </div>

                <!-- Recurring Option -->
                <div>
                    <div class="flex items-center">
                        <x-checkbox id="is_recurring" wire:model="is_recurring" />
                        <x-label for="is_recurring" class="ml-2" value="{{ __('Is Recurring') }}" />
                    </div>
                    <x-input-error for="is_recurring" class="mt-2" />
                </div>

                <!-- Recurrence Pattern (shown only if recurring) -->
                <div x-data="{}" x-show="$wire.is_recurring">
                    <x-label for="recurrence_pattern" value="{{ __('Recurrence Pattern') }}" />
                    <x-select id="recurrence_pattern" class="mt-1 block w-full" wire:model="recurrence_pattern">
                        <option value="">{{ __('Select Pattern') }}</option>
                        <option value="daily">{{ __('Daily') }}</option>
                        <option value="weekly">{{ __('Weekly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                    </x-select>
                    <x-input-error for="recurrence_pattern" class="mt-2" />
                </div>

                <!-- Description -->
                <div class="col-span-2">
                    <x-label for="description" value="{{ __('Description') }}" />
                    <x-textarea id="description" class="mt-1 block w-full" wire:model="description" rows="3"
                        placeholder="{{ __('Add event details here...') }}"></x-textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>
            </div>

            <div
                class="mt-6 flex items-center justify-end space-x-3 border-t border-gray-200 pt-5 dark:border-gray-700">
                @if($isEdit)
                    <x-secondary-button type="button" wire:click="resetFields" class="px-4 py-2">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700">
                        {{ __('Update') }}
                    </x-button>
                @else
                    <x-button type="submit" class="px-4 py-2">
                        {{ __('Save') }}
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Events List Section -->
    <div class="rounded-lg bg-white shadow-lg dark:bg-gray-800 w-full">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col items-start justify-between space-y-3 sm:flex-row sm:items-center sm:space-y-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('Events List') }}
                </h3>
                <div class="relative w-full sm:w-auto">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 z-10 pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                    </span>
                    <x-input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search events...') }}" class="pl-10 w-full" />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="min-w-full w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Event Name') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Date') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Time') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Location') }}
                        </th>
                        <th scope="col"
                            class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300 md:table-cell">
                            {{ __('Recurring') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($events as $event)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $event->name }}
                                @if ($event->description)
                                    <span x-data="{tooltip: false}" x-on:mouseenter="tooltip = true"
                                        x-on:mouseleave="tooltip = false" class="relative cursor-pointer">
                                        <x-heroicon-s-information-circle class="ml-1 inline h-4 w-4 text-blue-500" />
                                        <div x-show="tooltip"
                                            class="absolute left-0 top-full z-50 w-48 rounded-md bg-gray-800 p-2 text-xs text-white shadow-lg"
                                            x-cloak>
                                            {{ \Illuminate\Support\Str::limit($event->description, 100) }}
                                        </div>
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->event_date ? $event->event_date->format('d M Y') : ($event->is_recurring ? __('Recurring') : 'N/A') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->location ?: 'N/A' }}
                            </td>
                            <td
                                class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 md:table-cell">
                                @if($event->is_recurring)
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ __($event->recurrence_pattern) }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ __('No') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                <div class="flex justify-center space-x-2">
                                    <button wire:click="edit({{ $event->id }})"
                                        class="rounded-md bg-blue-100 p-1.5 text-blue-600 transition duration-200 hover:bg-blue-200 dark:bg-blue-700 dark:text-blue-100 dark:hover:bg-blue-600">
                                        <x-heroicon-o-pencil-square class="h-5 w-5" />
                                    </button>
                                    <button wire:click="delete({{ $event->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this event?') }}"
                                        class="rounded-md bg-red-100 p-1.5 text-red-600 transition duration-200 hover:bg-red-300 dark:bg-red-700 dark:text-red-500 dark:hover:bg-red-700">
                                        <x-heroicon-o-trash class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No events found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700 sm:px-6">
            {{ $events->links() }}
        </div>
    </div>
</div>