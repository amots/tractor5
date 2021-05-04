<?php

class essay
{

    var $essays;
    private $article = NULL;
    private $errors = [];

    function __construct()
    {
        $this->essays = $this->getEssaysList();
    }

    public function getEssaysList()
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `articles` ORDER BY `position`;";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $essays = $stmt->fetchAll();

        return $essays;
    }

    public function essayList()
    {
        $items = [];
        foreach ($this->essays as $essay) {
            $items[] = '<li class="list-group-item">' . $this->formatEssayList($essay) . '</li>';
        }
        $lists = join('', $items);
        return '<ul class="list-group list-group-flush">' . $lists . '</ul>';
    }

    private function formatEssayList($essay)
    {
        $str = $pre = $post = '';
        if (strlen($essay['article_id']) > 0) {
            $pre = '<a href="/essays/article/' . $essay['article_id'] . '">';
            $post = '</a>';
        }
        $str .= $pre . $essay['title_' . Lang::getLocale()] . $post;
        return $str;
    }

    public function getAtricle($id)
    {
        // Debug::dump($id, 'id in ' . util::getCaller());
        $pdo = db::getInstance();
        $sql = 'SELECT * FROM `articles` where `article_id` = :id LIMIT 1';
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $this->article = $stmt->fetch();
        return $this->article;
    }

    public function getArticleTitle($id)
    {
        $field = 'title_' . Lang::getLocale();
        $pdo = db::getInstance();
        $sql = "SELECT {$field} FROM `articles` where `article_id` = :id LIMIT 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $title = $stmt->fetch();
        return $title[$field];
    }

    public function renderArticle($id)
    {
        $article = $this->getAtricle($id);

        $title = $article['title_' . Lang::getLocale()];
        $author = $article['author_' . Lang::getLocale()];
        $body = $article['body_' . Lang::getLocale()];
        // $updated = $elements = [];
        $elements[] = "<h2>{$title}</h2>";
        $elements[] = "<h3>{$author}</h3>";
        $elements[] = "<article>{$body}</article>";
        $elements[] = util::renderLastUpdated(
            $article['updated'],
            Lang::trans('general.updated', lang::getLocale())
        );
        return join(' ', $elements);
    }

    public function renderArticleEditContent($article_id)
    {
        $articleData = $this->getAtricle($article_id);
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        if (is_numeric($article_id)) {
            if (!isset($articleData['updated'])) $articleData['updated'] = NULL;
            $articleData['article_id'] = $article_id;
        } else {
            $form=new form('articles');
            $articleData = $form->genEmptyRecord();
            $articleData['article_id'] = null;
        }

        $articleData['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/editArticle.html");
        $renderer->viewData = ['item' => $articleData];
        $content = $renderer->render();
        return $content;
    }

    public function get_errors()
    {
        return $this->errors;
    }

    public function renderMngBreadcrumbs()
    {
        return breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('mng.articles'), 'link' => NULL],
        ]);
    }
}
