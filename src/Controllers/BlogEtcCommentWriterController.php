<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Events\CommentAdded;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\AddNewCommentRequest;

/**
 * Class BlogEtcCommentWriterController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCommentWriterController extends Controller
{

    /**
     * Let a guest (or logged in user) submit a new comment for a blog post
     *
     * @param AddNewCommentRequest $request
     * @param $blog_post_slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function addNewComment(AddNewCommentRequest $request, $blog_post_slug)
    {

        if (config("blogetc.comments.type_of_comments_to_show","built_in") !== 'built_in') {
            throw new \Exception("Built in comments are disabled");
        }

        $blog_post = BlogEtcPost::where("slug", $blog_post_slug)
            ->firstOrFail();

        $new_comment = new BlogEtcComment($request->all());

        if (config("blogetc.comments.save_ip_address")) {
            $new_comment->ip = $request->ip();
        }

        if (config("blogetc.comments.save_user_id_if_logged_in", true)) {
            if (\Auth::check()) {
                $new_comment->user_id = \Auth::user()->id;
            }
        }

        $new_comment->approved = config("blogetc.comments.auto_approve_comments", true) ? true : false;

        $blog_post->comments()->save($new_comment);

        event(new CommentAdded($blog_post, $new_comment));

        return view("blogetc::saved_comment", ['blog_post' => $blog_post, 'new_comment' => $new_comment]);

    }

}