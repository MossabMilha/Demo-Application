<?php

namespace App\Models;

use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class user extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public static  $validRoles = ['admin', 'professor', 'student', 'parent', 'staff'];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public static function checkPassword(string $password): bool
    {
        if (strlen($password) < 6) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/\d/', $password)) {
            return false;
        }

        if (!preg_match('/[\W_]/', $password)) {
            return false;
        }
        return true;
    }

    public static function checkEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function checkName(string $name): bool{
        return preg_match('/^[a-zA-Z_-]+$/', $name) === 1;
    }
    public static function checkBirth_date($birth_date){
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        return $date && $date->format('Y-m-d') === $birth_date;
    }
    public static function checkPhone($phone){
        return preg_match('/^\+\d{1,3} \d{9}$/', $phone) === 1;
    }
    public static function generateStrongPassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()_+-={}[]|:;<>,.?/~';

        // Ensure at least one character from each category
        $password = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        // Fill the rest with random characters from all sets
        $all = $upper . $lower . $digits . $special;
        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle to make it unpredictable
        shuffle($password);

        return implode('', $password);
    }


    // Relationships
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function professor()
    {
        return $this->hasOne(Professor::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function parentData()
    {
        return $this->hasOne(ParentModel::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }
}
