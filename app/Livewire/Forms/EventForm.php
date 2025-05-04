<?php

namespace App\Livewire\Forms;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventForm extends Form
{
    public ?Event $event = null; // Initialize to null explicitly

    public $name = '';
    public $start_time = null;
    public $end_time = null;

    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'start_time' => ['required'],
            'end_time' => ['nullable'],
        ];
        
        // Only add the unique rule with ignore when we have an event
        if (isset($this->event)) {
            $rules['name'][] = Rule::unique('events')->ignore($this->event);
        } else {
            $rules['name'][] = Rule::unique('events');
        }
        
        return $rules;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
        $this->name = $event->name;
        $this->start_time = $event->start_time;
        $this->end_time = $event->end_time;
        return $this;
    }

    public function store()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        Event::create($this->all());
        $this->reset();
    }

    public function update()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        $this->event->update($this->all());
        $this->reset();
    }

    public function delete()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->event->delete();
        $this->reset();
    }
}
