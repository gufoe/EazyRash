<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Article;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function lists()
    {
        return Article::with('user')
            ->with('reviewers')
            ->get();
    }

    public function detail($id)
    {
        return Article::with('user')
            ->with('reviewers')
            ->with('conference')
            ->findOrFail($id);
    }

    public function create(Request $request)
    {
        $data = [
            'name'          => $request->input('name'),
            'conference_id' => $request->input('conference_id'),
            'user_id'       => user()->id,
            'status'        => Article::UPDATING,
        ];
        validate(Article::$rules, $data);
        $article = Article::create($data);
        return $article;
    }

    public function setReviewers(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('manage-article', $article);
        validate([
            'users' => 'array',
            'users.*' => 'exists:users,id', ], $request->all());
        $article->reviewers()->sync($request->input('users'));
        return $this->detail($id);
    }

    public function lock(Request $request, $id)
    {
        $success = Article::findOrFail($id)->lock($request->user());
        return success('locked', $success);
    }

    public function unlock(Request $request, $id)
    {
        $success = Article::findOrFail($id)->unlock($request->user());
        return success('unlocked', $success);
    }
}
