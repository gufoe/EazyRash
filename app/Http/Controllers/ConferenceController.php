<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Conference;

class ConferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        return Conference::with('chairs')
            ->with('articles')
            ->get();
    }

    public function detail($id)
    {
        return Conference
            ::with('chairs')
            ->with('articles')
            ->with('articles.user')
            ->with('articles.reviewers')
            ->findOrFail($id);
    }

    public function create(Request $request)
    {
        \DB::beginTransaction();
        $data = [
            'name' => $request->input('name'),
        ];

        validate(Conference::$rules, $data);
        $conf = Conference::create($data);

        $conf->chairs()->attach(user());

        \DB::commit();
        return success();
    }

    public function setChairs(Request $request, $id)
    {
        $conf = Conference::findOrFail($id);
        $this->authorize('manage-conference', $conf);
        validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ], $request->all());
        $conf->chairs()->sync($request->input('users'));
        return $this->detail($id);
    }
}
