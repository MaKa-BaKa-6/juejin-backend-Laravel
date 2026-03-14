<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
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
                'views' => $item->views_count > 1000 ? round($item->views_count/1000, 1).'k' : $item->views_count,
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
        $ranking = Article::where("status", 1)
            ->orderBy("views_count", "desc")
            ->take(5)
            ->get(["id", "title", "views_count"]);
        return $this->success($ranking);
    }
}
