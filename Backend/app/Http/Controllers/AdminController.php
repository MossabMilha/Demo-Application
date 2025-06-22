<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Level;
use App\Models\Subject;
use App\Models\user;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            case 'professor':
                $rules = array_merge($rules,[
                    'department' => ['required', 'string'],
                    'grade' => ['required', 'string'],
                    'phone' =>['required', 'string', 'unique:professors,phone', 'check_phone'],
                ]);

                break;
            case 'parent':
                $rules = array_merge($rules,[
                    'occupation' => ['required', 'string'],
                    'phone' =>['required', 'string', 'unique:professors,phone', 'check_phone'],
                ]);
                break;
            case 'staff':
                $rules = array_merge($rules,[
                    'position' => ['required', 'string'],
                    'department' =>['required', 'string', 'unique:professors,phone', ],
                ]);
                break;
            default:
                return response()->json(['message' => 'Invalid role'], 400);
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $password = user::generateStrongPassword();
        try {
            DB::beginTransaction();
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
                case  'professor':
                    $user->professor()->create([
                        'department' => $request->department,
                        'grade' => $request->grade,
                        'phone' => $request->phone,
                    ]);
                    break;
                case 'parent':
                    $user->parent()->create([
                        'occupation' => $request->occupation,
                        'phone' => $request->phone,
                    ]);
                    break;
                case 'staff':
                    $user->staff()->create([
                        'position' => $request->position,
                        'department' => $request->department,
                    ]);
                    break;
            }
            DB::commit();
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'password' => $password, // Return the generated password
            ], 201);
        }catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create user',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function updateUser(Request $request){
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $rules = [
            'first_name' => ['sometimes', 'string', 'valid_name'],
            'last_name' => ['sometimes', 'string', 'valid_name'],
            'email' => ['sometimes', 'email', 'check_email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|in:student,professor,parent,staff',
        ];
        switch ($user->role) {
            case 'student':
                $rules = array_merge($rules,[
                    'student_code' => ['sometimes', 'string', 'valid_student_code', Rule::unique('students')->ignore($user->student->id)],
                    'birthday' => ['sometimes', 'date'],
                    'class_level' => ['sometimes', 'string'],
                    'section' => ['sometimes', 'string'],
                ]);
                break;
            case 'professor':
                $rules = array_merge($rules,[
                    'department' => ['sometimes', 'string'],
                    'grade' => ['sometimes', 'string'],
                    'phone' =>['sometimes', 'string', Rule::unique('professors')->ignore($user->professor->id), 'check_phone'],
                ]);
                break;
            case 'parent':
                $rules = array_merge($rules,[
                    'occupation' => ['sometimes', 'string'],
                    'phone' =>['sometimes', 'string', Rule::unique('parents')->ignore($user->parent->id), 'check_phone'],
                ]);
                break;
            case 'staff':
                $rules = array_merge($rules,[
                    'position' => ['sometimes', 'string'],
                    'department' =>['sometimes', 'string'],
                ]);
                break;
            default:
                return response()->json(['message' => 'Invalid role'], 400);
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            DB::beginTransaction();
            foreach (['first_name', 'last_name', 'email', 'role'] as $field) {
                if ($request->has($field)) {
                    $user->$field = $request->$field;
                }
            }
            $user->save();
            switch ($user->role) {
                case 'student':
                    $student = $user->student;
                    if ($student) {
                        $student->update($request->only(['student_code', 'birthday', 'class_level', 'section']));
                    }
                    break;

                case 'professor':
                    $prof = $user->professor;
                    if ($prof) {
                        $prof->update($request->only(['department', 'grade', 'phone']));
                    }
                    break;

                case 'parent':
                    $parent = $user->parentData;
                    if ($parent) {
                        $parent->update($request->only(['occupation', 'phone']));
                    }
                    break;

                case 'staff':
                    $staff = $user->staff;
                    if ($staff) {
                        $staff->update($request->only(['position', 'department']));
                    }
                    break;
            }
            DB::commit();
            return response()->json(['message' => 'User updated successfully', 'user' => $user]);
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Update failed', 'details' => $e->getMessage()], 500);
        }
    }
    public function ShowUsers(Request  $request){
        $role = $request->input('role');

        if (!$role) {
            return response()->json(['error' => 'You should define a role'], 400);
        }
        $validRoles = ['professor', 'parent', 'staff'];

        if (!in_array($role, $validRoles)) {
            return response()->json(['error' => 'Invalid role'], 400);
        }
        $relation = match ($role) {
            'student' => 'student',
            'professor' => 'professor',
            'parent' => 'parentData',
            'staff' => 'staff',
        };
        $users = User::where('role', $role)->with($relation)->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found for this role'], 404);
        }

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users,
        ]);

    }
    public function activeUserAccount(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->is_active = 1;
        $user->save();

        return response()->json(['message' => 'User account activated successfully', 'user' => $user]);
    }
    public function  deactivateUserAccount(Request $request){
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->is_active = 0;
        $user->save();

        return response()->json(['message' => 'User account deactivated successfully', 'user' => $user]);
    }

    public function addLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:levels,name',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $level = Level::create($request->only('name', 'description'));
            return response()->json(['message' => 'Level added successfully', 'level' => $level], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add level', 'details' => $e->getMessage()], 500);
        }
    }
    public function deleteLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:levels,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $level = Level::findOrFail($request->id);
            $level->delete();
            return response()->json(['message' => 'Level deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete level', 'details' => $e->getMessage()], 500);
        }
    }
    public function editLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:levels,id',
            'name' => 'sometimes|string|max:255|unique:levels,name,' . $request->id,
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $level = Level::findOrFail($request->id);
            $level->update($request->only('name', 'description'));
            return response()->json(['message' => 'Level updated successfully', 'level' => $level], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update level', 'details' => $e->getMessage()], 500);
        }
    }
    public function showLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:levels,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $level = Level::with('choices')->findOrFail($request->id);
            return response()->json(['message' => 'Level retrieved successfully', 'level' => $level], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve level', 'details' => $e->getMessage()], 500);
        }
    }

    public function addChoices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $level = Level::findOrFail($request->level_id);
            $choice = $level->choices()->create($request->only('name', 'description'));
            return response()->json(['message' => 'Choice added successfully', 'choice' => $choice], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add choice', 'details' => $e->getMessage()], 500);
        }
    }
    public function deleteChoices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:choices,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $choice = Choice::findOrFail($request->id);
            $choice->delete();
            return response()->json(['message' => 'Choice deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete choice', 'details' => $e->getMessage()], 500);
        }
    }
    public function editChoices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:choices,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $choice = Choice::findOrFail($request->id);
            $choice->update($request->only('name', 'description'));
            return response()->json(['message' => 'Choice updated successfully', 'choice' => $choice], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update choice', 'details' => $e->getMessage()], 500);
        }
    }
    public function showChoices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:choices,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $choice = Choice::with(['level', 'subject'])->findOrFail($request->id);
            return response()->json(['message' => 'Choice retrieved successfully', 'choice' => $choice], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve choice', 'details' => $e->getMessage()], 500);
        }
    }

    public function addSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'choice_id' => 'required|exists:choices,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'coefficient' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $choice = Choice::findOrFail($request->choice_id);
            $subject = $choice->subjects()->create($request->only('name', 'description', 'coefficient'));
            return response()->json(['message' => 'Subject added successfully', 'subject' => $subject], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add subject', 'details' => $e->getMessage()], 500);
        }
    }
    public function deleteSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subject = Subject::findOrFail($request->id);
            $subject->delete();
            return response()->json(['message' => 'Subject deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete subject', 'details' => $e->getMessage()], 500);
        }
    }
    public function editSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:subjects,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'coefficient' => 'sometimes|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subject = Subject::findOrFail($request->id);
            $subject->update($request->only('name', 'description', 'coefficient'));
            return response()->json(['message' => 'Subject updated successfully', 'subject' => $subject], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update subject', 'details' => $e->getMessage()], 500);
        }
    }
    public function showSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subject = Subject::with('choice')->findOrFail($request->id);
            return response()->json(['message' => 'Subject retrieved successfully', 'subject' => $subject], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve subject', 'details' => $e->getMessage()], 500);
        }
    }


}
