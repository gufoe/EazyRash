<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Conference extends Model
{
    protected $guarded = ['id'];
    protected $hidden = [];
    protected $appends = ['auth'];
    public $timestamps = false;

    public static $rules = [
        'name' => 'required',
    ];

    public function chairs()
    {
        return $this->belongsToMany('App\User', 'chairs');
    }

    public function articles()
    {
        return $this->hasMany('App\Article');
    }

    public function getAuthAttribute()
    {
        return [
            'manage' => Gate::allows('manage-conference', $this),
        ];
    }
}
