<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Article;
use App\Review;
use App\Comment;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'download']);
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


    public function download($id)
    {
        $article = Article::findOrFail($id);
        $commit = $article->commit;
        $rash = $commit->rash;

        // Append reviews/comments for each reviewer
        foreach ($article->reviewers as $reviewer) {
            $data = [];

            // Get (or create) review
            $review = Review::firstOrNew([
                'user_id' => $reviewer->id,
                'commit_id' => $commit->id
            ]);

            // Get comments
            $comments = Comment::where([
                'user_id' => $reviewer->id,
                'commit_id' => $commit->id
            ])->get();

            // Generate review #id
            $rid = "#review{$review->id}";

            // Add review
            $data[] = [
          		"@context"       => "http://vitali.web.cs.unibo.it/twiki/pub/TechWeb16/context.json",
          		"@type"          => "review",
          		"@id"            => $rid,
          		"article"        => [
          			"@id"        => "",
          			"eval"       => [
          				"@id"    => "$rid-eval",
          				"@type"  => "score",
          				"status" => "pso:".($review->accepted ? 'accepted' : 'rejected')."-for-publication",
          				"author" => "mailto:{$reviewer->email}",
          				"date"   => str_replace(' ', 'T', $review->updated_at),
          			]
          		],
          		"comments"       => $comments->pluck('target')->toArray()
            ];

            // Add comments
            foreach ($comments as $c) {
                $data[] = [
               		"@context" => "http://vitali.web.cs.unibo.it/twiki/pub/TechWeb16/context.json",
               		"@type"    => "comment",
               		"@id"      => "{$rid}-c{$c->id}",
               		"text"     => $c->content,
               		"ref"      => $c->target,
               		"author"   => "mailto:{$c->user->email}",
               		"date"     => str_replace(' ', 'T', $c->updated_at),
               	];
            }

            // Add person
            $data[] = [
          		"@context"      => "http://vitali.web.cs.unibo.it/twiki/pub/TechWeb16/context.json",
          		"@type"         => "person",
          		"@id"           => "mailto:{$reviewer->email}",
          		"name"          => $reviewer->name,
          		"as"            => [
          			"@id"       => "#role2",
          			"@type"     => "role",
          			"role_type" => "pro:reviewer",
          			"in"        => ""
          		],
            ];

            // Append the data block to the rash <head>
            $json = json_encode($data, JSON_PRETTY_PRINT);
            $block = "\n<script type=\"application/ld+json\">\n{$json}\n</script>\n";
            $rash = str_replace('</head>', "{$block}\n</head>", $rash);
        }


        // Append the chair comment
        $chair = $article->conference->chairs()->first();
        $data = [

            [
          		"@context" => "http://vitali.web.cs.unibo.it/twiki/pub/TechWeb16/context.json",
          		"@type"    => "decision",
          		"@id"      => "#decision1",
          		"article"  => [
          			"@id"  => "",
          			"eval" => [
          				"@context" => "easyrash.json",
          				"@id"      => "#decision1-eval",
          				"@type"    => "score",
          				"status"   => "pso:{$article->status_txt}-for-publication",
          				"author"   => "mailto:{$chair->email}",
          				"date"     => str_replace(' ', 'T', $commit->updated_at),
          			]
          		]
          	],
           	[
          		"@context" => "http://vitali.web.cs.unibo.it/twiki/pub/TechWeb16/context.json",
          		"@type"    => "person",
          		"@id"      => "mailto:{$chair->email}",
          		"name"     => $chair->name,
          		"as"       => [
          			"@id"       => "#role3",
          			"@type"     => "role",
          			"role_type" => "pro:chair",
          			"in"        => ""
          		]
          	]
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $block = "<script type=\"application/ld+json\">{$json}</script>";
        $rash = str_replace('</head>', "{$block}\n</head>", $rash);

        return $rash;
    }
}
