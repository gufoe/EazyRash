<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class Article extends Model
{
    protected $guarded = ['id'];
    protected $hidden = [];
    protected $appends = [
        'commits_count',
        'status_txt',
        'auth',
        'commit',
    ];

    public $timestamps = false;

    public static $rules = [
        'name'          => 'required',
        'conference_id' => 'required|exists:conferences,id',
        'user_id'       => 'required|exists:users,id',
    ];

    const UPDATING = 0;
    const REVIEWING = 1;
    const REVIEWED = 2;
    const ACCEPTED = 3;
    const REJECTED = 4;

    public static $statuses = [
        'updating',  // Waiting a new commit from the author
        'reviewing', // Waiting reviews from peers
        'reviewed',  // Waiting chair judgment
        'accepted',  // Article accepted
        'rejected',  // Article rejected
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function conference()
    {
        return $this->belongsTo('App\Conference');
    }

    public function reviewers()
    {
        return $this->belongsToMany('App\User', 'reviewers');
    }

    public function commits()
    {
        return $this->hasMany('App\Commit');
    }

    public function hasStatus($status)
    {
        return @self::$statuses[$this->status] == $status;
    }

    public function getCommitsCountAttribute()
    {
        return $this->commits()->count();
    }

    public function getStatusTxtAttribute()
    {
        return @self::$statuses[$this->status];
    }

    public function getCommitAttribute()
    {
        return $this->commits()
            ->with('comments')
            ->with('comments.user')
            ->with('reviews')
            ->with('reviews.user')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getAuthAttribute()
    {
        return [
            'review' => Gate::allows('review-article', $this),
            'update' => Gate::allows('update-article', $this),
            'manage' => Gate::allows('manage-article', $this),
        ];
    }

    public function lock(User $user)
    {
        if (!\Gate::check('review-article', $this, $user)) {
            return false;
        }
        // Acquire lock if
        // - the article is not locked
        // - the user already has the lock
        // - the lock is expired (older than 3600 seconds)
        $seconds = $this->locked_at ? (new Carbon())->diffInSeconds(new Carbon($this->locked_at)) : null;
        if (!$this->lock_user_id || $this->lock_user_id == $user->id || $seconds > 30) {
            // die('wtf');
            // var_dump($this->locked_at);
            // var_dump((new Carbon())->diffInSeconds(new Carbon($this->locked_at)));
            // die();
            $this->update([
                'lock_user_id' => $user->id,
                'locked_at' => Carbon::now()->toDateTimeString(),
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function unlock(User $user)
    {
        if (!\Gate::check('review-article', $this, $user)) {
            return false;
        }
        if ($this->lock_user_id == $user->id) {
            $this->update([
                'lock_user_id' => null,
                'locked_at' => null,
            ]);
            return true;
        } else {
            return false;
        }
    }
}
