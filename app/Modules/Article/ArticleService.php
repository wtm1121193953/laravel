<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 18:16
 */

namespace App\Modules\Article;


use App\BaseService;

class ArticleService extends BaseService
{

    /**
     * 根据code获取文章
     * @param $code
     * @return Article
     */
    public static function getByCode($code)
    {
        return Article::where('code', $code)->first();
    }

    /**
     * 根据code编辑文章
     * @param $code
     * @param $title
     * @param $content
     * @return Article
     */
    public static function editByCode($code, $title, $content)
    {
        $article = Article::where('code', $code)->first();
        if (empty($article)){
            $article = new Article();
            $article->code = $code;
        }
        $article->title = $title;
        $article->content = $content;
        $article->save();
        return $article;
    }
}