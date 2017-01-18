<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Article;
use App\Commit;
use App\Comment;
use App\File;
use App\Review;

class CommitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'stream']);
    }

    public function create(Request $request)
    {
        $article = Article::findOrFail($request->input('article_id'));
        if ($article->user_id != user()->id) {
            return error('Only the article owner can upload a commit.');
        }

        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return error('Error loading the file.');
        }

        $data = [
            'article_id' => $article->id,
            'rash'    => @file_get_contents($request->file('file')->path()),
        ];
        validate(Commit::$rules, $data);
        $commit = Commit::create($data);
        $article->update(['status' => Article::REVIEWING]);
        return $commit;
    }

    public function review(Request $request, $id)
    {
        $commit = Commit::findOrFail($id);
        $this->authorize('review-article', $commit->article);
        if (!$commit->article->lock(user())) {
            return error('The file is not in your possess in this moment.');
        }

        \DB::beginTransaction();

        if (!$request->input('rash')) {
            return error('Invalid rash file');
        }

        $commit->update(['rash' => $request->input('rash')]);

        if ($request->input('edits.review')) {
            $accepted = (int) $request->input('edits.review.accepted');
            $content = (string) $request->input('edits.review.content');
            if (!\Gate::allows('manage-article', $commit->article)) {
                $review = Review::firstOrCreate([
                    'user_id' => user()->id,
                    'commit_id' => $commit->id
                ]);

                $review->update([
                    'content'  => $content,
                    'accepted' => $accepted,
                ]);
            } else {
                $commit->article->update([
                    'status' => $accepted ? Article::ACCEPTED : Article::REJECTED
                ]);
                $commit->update([
                    'content'  => $content,
                    'accepted' => $accepted,
                ]);
            }


        }

        foreach ($request->input('edits.comments.added') as $data) {
            $data['user_id'] = user()->id;
            $data['commit_id'] = $commit->id;
            validate(Comment::$rules, $data);
            Comment::create($data);
        }

        foreach ($request->input('edits.comments.removed') as $id) {
            $commit->comments()
                ->whereId($id)
                ->whereUserId(user()->id)
                ->delete();
        }

        \DB::commit();

        return success();
    }
}
