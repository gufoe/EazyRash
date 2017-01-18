<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    public $appends = ['review'];
    public static $rules = [
        'rash' => 'required|string',
    ];

    public function article()
    {
        return $this->belongsTo('App\Article');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function reviews()
    {
        return $this->hasMany('App\Review');
    }

    public function getReviewAttribute()
    {
        return !user() ? null : $this->reviews()
                ->whereUserId(user()->id)->first();
    }
}
