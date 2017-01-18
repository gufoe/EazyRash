<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = ['id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
        'content'   => 'string',
        'accepted'  => 'boolean',
        'user_id'   => 'required|exists:users,id',
        'commit_id' => 'required|exists:commits,id',
    ];


    public function article()
    {
        return $this->commit->belongsTo('App\Article');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function commit()
    {
        return $this->belongsTo('App\Commit');
    }
}
