<?php

namespace App\Providers;

use App\Models\student;
use App\Models\user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            return User::checkPassword($value);
        }, 'The :attribute must be a strong password.');
        Validator::extend('valid_name', function ($attribute, $value, $parameters, $validator) {
            return User::checkName($value);
        }, 'The :attribute format is invalid.');
        Validator::extend('check_email', function ($attribute, $value, $parameters, $validator) {
            return User::checkEmail($value);
        }, 'The :attribute format is invalid.');
        Validator::extend('check_birthday', function ($attribute, $value, $parameters, $validator) {
            return User::checkBirth_date($value);
        }, 'The :attribute format is invalid.');

        Validator::extend('valid_student_code', function ($attribute, $value, $parameters, $validator) {
            return student::checkStudent_code($value);
        }, 'The :attribute format is invalid.');
    }
}
