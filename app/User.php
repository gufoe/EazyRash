<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    protected $guarded = ['id'];
    protected $visible = ['id', 'name', 'email', 'full_name'];
    protected $appends = ['full_name'];
    public $timestamps = false;
    public static $rules = [
        'name'    => 'required',
        'email'    => 'required|unique:users',
        'password' => 'required|min:7',
    ];

    public function articles()
    {
        return $this->hasMany('App\Article');
    }

    public function chairs()
    {
        return $this->belongsToMany('App\Conference', 'chairs');
    }

    public function reviews()
    {
        return $this->belongsToMany('App\Article', 'reviews');
    }

    public function generateToken()
    {
        $this->update(['api_token' => str_random(10)]);
        return $this->api_token;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function login($password)
    {
        return Hash::check($password, $this->password);
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} <{$this->email}>";
    }
}
