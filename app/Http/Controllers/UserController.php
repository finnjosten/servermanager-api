<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group User management
 * @authenticated
 *
 * APIs for managing users
 */
class UserController extends Controller {

    private $request_fields = array(
        'email',
        'password',

        'role_slug',
        'department_slug',
        'subdepartment_slug',
        'supervisor_id',

        'blocked',
        'verified',

        'first_name',
        'sure_name',
        'bsn',
        'date_of_service',

        'sick_days',
        'vac_days',
        'personal_days',
        'max_vac_days'
    );
    private $validator_fields = array(
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|max:64',

        'role_slug' => 'nullable|string|exists:roles,slug',
        'department_slug' => 'nullable|string|exists:departments,slug',
        'subdepartment_slug' => 'nullable|string|exists:subdepartments,slug',
        'supervisor_id' => 'nullable|integer|exists:users,id',

        'blocked' => 'nullable|boolean',
        'verified' => 'nullable|boolean',

        'first_name' => 'required|string|max:64',
        'sure_name' => 'required|string|max:64',
        'bsn' => 'required|string|max:9|unique:users,bsn',
        'date_of_service' => 'required|date',

        'sick_days' => 'nullable|integer',
        'vac_days' => 'nullable|integer',
        'personal_days' => 'nullable|integer',
        'max_vac_days' => 'nullable|integer',
    );

    /**
     * Display all the users.
     */
    public function index() {
        $users = User::all();
        return response()->json(["data" => $users]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request) {

        $data = $request->only(...$this->request_fields);

        // Define validation rules
        $validator = Validator::make($data, $this->validator_fields);

        // Check if the validation fails
        if ($validator->fails()) {
            // Return a JSON response with validation errors
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the user with the validated data
        $user = User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),

            'role_slug' => $data['role_slug'] ?? 1, // Default role ID to 1 if not provided
            'department_slug' => $data['department_slug'] ?? null,
            'subdepartment_slug' => $data['subdepartment_slug'] ?? null,
            'supervisor_id' => $data['supervisor_id'] ?? null,

            'blocked' => $data['blocked'] ?? false,
            'verified' => $data['verified'] ?? false,

            'first_name' => $data['first_name'],
            'sure_name' => $data['sure_name'],
            'bsn' => $data['bsn'],
            'date_of_service' => $data['date_of_service'],

            'sick_days' => $data['sick_days'] ?? 0,
            'vac_days' => $data['vac_days'] ?? 0,
            'personal_days' => $data['personal_days'] ?? 0,
            'max_vac_days' => $data['max_vac_days'] ?? 366,
        ]);


        // Return a success response
        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show($user_id) {
        return response()->json(["data" => User::whereId($user_id)->first()]);
    }

    /**
     * Display the current user.
     */
    public function showCurrent(Request $request) {
        return response()->json(["data" => $request->user()]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $user_id) {

        $data = $request->only(...$this->request_fields);

        // Define validation rules
        $validator = Validator::make($data, $this->validator_fields);

        // Check if the validation fails
        if ($validator->fails()) {
            // Return a JSON response with validation errors
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the user with the validated data
        $user = User::find()->whereId($user_id)->first();

        $user->fill([
            'email' => $data['email'] ?? $user->email,

            'role_slug' => $data['role_slug'] ?? $user->role_slug,
            'department_slug' => $data['department_slug'] ?? $user->department_slug,
            'subdepartment_slug' => $data['subdepartment_slug'] ?? $user->subdepartment_slug,
            'supervisor_id' => $data['supervisor_id'] ?? $user->supervisor_id,

            'blocked' => $data['blocked'] ?? $user->blocked,
            'verified' => $data['verified'] ?? $user->verified,

            'first_name' => $data['first_name'] ?? $user->first_name,
            'sure_name' => $data['sure_name'] ?? $user->sure_name,
            'bsn' => $data['bsn'] ?? $user->bsn,
            'date_of_service' => $data['date_of_service'] ?? $user->date_of_service,

            'sick_days' => $data['sick_days'] ?? $user->sick_days,
            'vac_days' => $data['vac_days'] ?? $user->vac_days,
            'personal_days' => $data['personal_days'] ?? $user->personal_days,
            'max_vac_days' => $data['max_vac_days'] ?? $user->max_vac_days,
        ]);

        $user->save();

        // Return a success response
        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy($user_id) {

        $user = User::whereId($user_id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully!',
        ]);

    }
}
