<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function explore(Request $request)
    {
        $query = Article::with('user')->where('status', 1);

        if ($request->filled('category') && $request->category !== '综合') {
            $query->where('category', $request->category);
        }

        $sort = $request->input('sort', 'recommend');
        if ($sort === 'latest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('views_count', 'desc');
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 19);

        $articles = $query->skip($offset)->take($limit)->get();

        $formatted = $articles->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'summary' => $item->summary,
                'author' => $item->user->username ?? '佚名',
                'category' => $item->category,
                'views' => $item->views_count > 1000 ? round($item->views_count / 1000, 1) . 'k' : $item->views_count,
                'likes' => $item->likes_count,
                'cover' => $item->cover,
                'tags' => json_decode($item->tags, true) ?? []
            ];
        });

        return $this->success($formatted);
    }

    public function categories()
    {
        $categories = Category::orderBy("sort", "asc")->get();
        return $this->success($categories);
    }

    public function ranking()
    {
        $ranking = Article::where("status", 1)->orderBy("views_count", "desc")->take(5)->get(["id", "title", "views_count"]);
        return $this->success($ranking);
    }

    public function articleDetail($id)
    {
        $article = Article::with('user')->findOrFail($id);

        $article->increment('views_count');

        $data = $article->toArray();
        $author = $article->user;

        $author->article_count = Article::where('user_id', $author->id)->count();
        $author->total_views = Article::where('user_id', $author->id)->sum('views_count');

        $data['user'] = $author;
        $data['tags'] = json_decode($article->tags, true) ?? [];
        $data['publish_time'] = $article->created_at->format('Y-m-d H:i');

        return $this->success($data);
    }

    public function comments($id)
    {
        $comments = Comment::with([
            'user:id,username,avatar',
            'replies.user:id,username,avatar',
            'replies.replyToUser:id,username'
        ])->where('article_id', $id)->where('parent_id', 0)->orderBy('created_at', 'desc')->get();

        $formatTime = function ($time) {
            $diffInSeconds = now()->diffInSeconds($time);
            $diffInHours = now()->diffInHours($time);
            $diffInDays = now()->diffInDays($time);
            $diffInMonths = now()->diffInMonths($time);

            if ($diffInSeconds < 86400) {
                return $diffInHours . '小时前';
            } elseif ($diffInDays < 30) {
                return $diffInDays . '天前';
            } elseif ($diffInMonths < 12) {
                return $diffInMonths . '月前';
            } else {
                return now()->diffInYears($time) . '年前';
            }
        };

        $formatted = $comments->map(function ($item) use ($formatTime) {
            return [
                'id' => $item->id,
                'content' => $item->content,
                'likes' => $item->likes_count,
                'time' => $formatTime($item->created_at),
                'user' => $item->user,
                'replies' => $item->replies->map(function ($reply) use ($formatTime) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'likes' => $reply->likes_count,
                        'time' => $formatTime($reply->created_at),
                        'user' => $reply->user,
                        'reply_to_user' => $reply->replyToUser
                    ];
                })
            ];
        });

        return $this->success($formatted);
    }
}
