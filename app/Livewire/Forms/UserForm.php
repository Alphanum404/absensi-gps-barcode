<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Form
{
    public ?User $user = null;

    public $name = '';
    public $nim = ''; // Diubah dari nip
    public $email = '';
    public $phone = '';
    public $password = '';
    public $gender = '';
    public $group = 'user';
    public $division_id = null;
    public $education_id = null;
    public $job_title_id = null;
    public $photo = null;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:255', Rule::unique('users', 'nim')->ignore($this->user?->id)], // Diubah dari nip
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'phone' => ['required', 'string', 'max:255'],
            'password' => $this->user ? ['nullable', 'string'] : ['required', 'string'],
            'gender' => ['required', 'string', 'in:male,female'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'education_id' => ['nullable', 'exists:educations,id'],
            'job_title_id' => ['nullable', 'exists:job_titles,id'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->nim = $user->nim; // Diubah dari nip
        $this->email = $user->email;
        $this->phone = $user->phone;
        if ($this->isAllowed()) {
            $this->password = $user->raw_password;
        }
        $this->gender = $user->gender;
        $this->group = $user->group;
        $this->division_id = $user->division_id;
        $this->education_id = $user->education_id;
        $this->job_title_id = $user->job_title_id;
        return $this;
    }

    public function store()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        $this->validate();
        /** @var User $user */
        $user = User::create([
            ...$this->all(),
            'password' => Hash::make($this->password ?? 'password'),
            'raw_password' => $this->password ?? 'password',
        ]);
        if (isset($this->photo))
            $user->updateProfilePhoto($this->photo);
        $this->reset();
    }

    public function update()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        $this->validate();
        $this->user->update([
            ...$this->all(),
            'password' => $this->password ? Hash::make($this->password) : $this->user?->password,
            'raw_password' => $this->password ?? $this->user?->raw_password,
        ]);
        if (isset($this->photo))
            $this->user->updateProfilePhoto($this->photo);
        $this->reset();
    }

    public function deleteProfilePhoto()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        return $this->user->deleteProfilePhoto();
    }

    public function delete()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        $this->user->delete();
        $this->deleteProfilePhoto();
        $this->reset();
    }

    private function isAllowed()
    {
        if ($this->group === 'user') {
            return Auth::user()?->isAdmin;
        }
        return Auth::user()?->isSuperadmin || (Auth::user()?->isAdmin && Auth::user()?->id === $this->user?->id);
    }
}
