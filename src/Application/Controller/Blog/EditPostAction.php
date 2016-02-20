<?php
/**
 * Created by PhpStorm.
 * User: Julien
 * Date: 20/02/2016
 * Time: 14:56
 */

namespace Application\Controller\Blog;

use Application\Html\HtmlBuilder;
use Framework\AbstractAction;
use Framework\Http\Request;
use Michelf\Markdown;

class EditPostAction extends AbstractAction
{
    public function __invoke(Request $request)
    {
        $session = $this->getService('session_blog_post');
        $html = new HtmlBuilder();
        $id = $request->getAttribute('id');
        $repository = $this->getService('repository.blog_post');
        $blog_post = $repository->find($id);
        if($request->getMethod() == "POST"){
            $args['html'] = $html;
            $args['blog_post'] = $blog_post;
            $title = $request->getRequestParameter('title',false);
            $content = $request->getRequestParameter('content',false);
            $args['session'] = $session;
            if(!$title && !$content){
                $session->store('error','Aucun champ n\'a été rempli');
                return $this->render('blog/edit.twig',$args);
            }
            if(!$content && $title){
                $session->store('error','Le contenu du blog post ne doit pas être vide');
                $args['title'] = $title;
                return $this->render('blog/edit.twig',$args);
            }
            if($content && !$title){
                $session->store('error','Le titre du blog post ne doit pas être vide');
                $args['content'] = $content;
                return $this->render('blog/edit.twig',$args);
            }
            $html = addslashes(Markdown::defaultTransform($content));
            $repository = $this->getService('repository.blog_post');
            if($repository->edit($id,[
                'title' => $title,
                'content' => $html,
                'content_markdown' => $content
            ])){
                return $this->redirect(
                    "/index.php/blog/article-{$id}.html"
                    ,301
                );
            }
        }
        return $this->render(
            'blog/edit.twig',
            compact('blog_post','session','html')
        );
    }

}