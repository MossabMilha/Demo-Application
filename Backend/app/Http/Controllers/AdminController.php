<?php

namespace App\Http\Controllers;

use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function createUser(Request $request){
        $rules = [
            'first_name' => ['required', 'string', 'valid_name'],
            'last_name' => ['required', 'string', 'valid_name'],
            'email' => ['required', 'email', 'unique:users,email', 'check_email'],
            'role' => 'required|in:student,professor,parent,staff',
        ];
        switch ($request->input('role')) {
            case 'student':
                $rules = array_merge($rules,[
                    'student_code' => ['required', 'string', 'unique:students,student_code', 'valid_student_code'],
                    'birthday' => ['required', 'date'],
                    'class_level' => ['required', 'string'],
                    'section' => ['required', 'string'],
                ]);

                break;
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $password = user::generateStrongPassword();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'is_active' => 0,
        ]);
        switch ($user->role) {
            case 'student':
                $user->student()->create([
                    'student_code' => $request->student_code,
                    'birth_date' => $request->birthday,
                    'class_level' => $request->class_level,
                    'section' => $request->section,
                ]);
                break;
        }
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'password' => $password, // Return the generated password
        ], 201);
    }
}
