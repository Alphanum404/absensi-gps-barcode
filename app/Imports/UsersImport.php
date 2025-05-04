<?php

namespace App\Imports;

use App\Models\Division;
use App\Models\Education;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    protected $failures = [];

    public function __construct(public bool $save = true)
    {
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Process division, education, and job title
            $division_id = null;
            if (isset($row['division']) && !empty($row['division'])) {
                $division = Division::firstOrCreate(['name' => $row['division']]);
                $division_id = $division->id;
            }

            $job_title_id = null;
            if (isset($row['job_title']) && !empty($row['job_title'])) {
                $jobTitle = JobTitle::firstOrCreate(['name' => $row['job_title']]);
                $job_title_id = $jobTitle->id;
            }

            $education_id = null;
            if (isset($row['education']) && !empty($row['education'])) {
                $education = Education::firstOrCreate(['name' => $row['education']]);
                $education_id = $education->id;
            }

            // Create user data array
            $userData = [
                'nim' => $row['nim'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'gender' => $row['gender'],
                'education_id' => $education_id,
                'division_id' => $division_id,
                'job_title_id' => $job_title_id,
                'password' => Hash::make($row['password']),
                'raw_password' => $row['password'],
                'group' => 'user', // Default group
            ];

            // Add ID if present
            if (isset($row['id']) && !empty($row['id'])) {
                $userData['id'] = $row['id'];
            }

            // Add timestamps if present
            if (isset($row['created_at']) && !empty($row['created_at'])) {
                $userData['created_at'] = $row['created_at'];
            }

            if (isset($row['updated_at']) && !empty($row['updated_at'])) {
                $userData['updated_at'] = $row['updated_at'];
            }

            // Create and save user
            $user = new User();
            $user->forceFill($userData);

            if ($this->save) {
                $result = $user->save();
                Log::info('User saved result', ['success' => $result, 'user' => $user->toArray()]);
            }

            return $user;

        } catch (\Exception $e) {
            Log::error('Error importing user', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'row' => $row
            ]);
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string'],
            'name' => ['required', 'string'],
            'email' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);

        foreach ($failures as $failure) {
            Log::warning('Import failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors()
            ]);
        }
    }

    public function getFailures()
    {
        return $this->failures;
    }
}
