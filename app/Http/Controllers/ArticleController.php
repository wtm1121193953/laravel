<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/15
 * Time: 19:41
 */

namespace App\Http\Controllers;


use App\Modules\Article\Article;

class ArticleController extends Controller
{
    public function index($code = '')
    {
        $article = Article::where('code', $code)->first();
        if (empty($article)){
            return view('error');
        }
        return view('article', ['article' => $article]);
    }
}