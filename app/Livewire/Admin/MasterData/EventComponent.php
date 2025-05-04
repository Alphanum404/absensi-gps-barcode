<?php

namespace App\Livewire\Admin\MasterData;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Laravel\Jetstream\InteractsWithBanner;

class EventComponent extends Component
{
    use WithPagination;
    use InteractsWithBanner;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|date')]
    public $event_date;

    #[Rule('required')]
    public $start_time = '';

    #[Rule('required|after:start_time')]
    public $end_time = '';

    #[Rule('nullable|string|max:255')]
    public $location = '';

    #[Rule('nullable|string')]
    public $description = '';

    #[Rule('nullable|boolean')]
    public $is_recurring = false;

    #[Rule('nullable|string')]
    public $recurrence_pattern;

    public $event_id;
    public $isEdit = false;
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $events = Event::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('location', 'like', '%' . $this->search . '%')
            ->orderBy('event_date', 'desc')
            ->paginate(10);

        return view('livewire.admin.master-data.event-component', ['events' => $events]);
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            $event = Event::find($this->event_id);
            $event->update([
                'name' => $this->name,
                'event_date' => $this->event_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'location' => $this->location,
                'description' => $this->description,
                'is_recurring' => $this->is_recurring,
                'recurrence_pattern' => $this->recurrence_pattern,
            ]);
            $this->banner(__('Event updated successfully'));
        } else {
            Event::create([
                'name' => $this->name,
                'event_date' => $this->event_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'location' => $this->location,
                'description' => $this->description,
                'is_recurring' => $this->is_recurring,
                'recurrence_pattern' => $this->recurrence_pattern,
            ]);
            $this->banner(__('Event created successfully'));
        }

        $this->resetFields();
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $event = Event::find($id);
        $this->event_id = $event->id;
        $this->name = $event->name;
        $this->event_date = $event->event_date;
        $this->start_time = $event->start_time->format('H:i:s');
        $this->end_time = $event->end_time->format('H:i:s');
        $this->location = $event->location;
        $this->description = $event->description;
        $this->is_recurring = $event->is_recurring;
        $this->recurrence_pattern = $event->recurrence_pattern;
    }

    public function delete($id)
    {
        $event = Event::find($id);
        $name = $event->name;
        $event->delete();

        $this->banner(__('Event ":name" deleted successfully', ['name' => $name]));
    }

    public function resetFields()
    {
        $this->reset([
            'name',
            'event_date',
            'start_time',
            'end_time',
            'location',
            'description',
            'is_recurring',
            'recurrence_pattern',
            'isEdit',
            'event_id'
        ]);
    }

    public function updating($property)
    {
        if ($property === 'search') {
            $this->resetPage();
        }
    }
}
