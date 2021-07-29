<?php

/**
 * Description of contactController
 *
 * @author amots
 * @since 2020-03-17
 */
class volunteersController extends baseController
{
    private $people;
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->people = new people();
    }

    public function index()
    {
        $this->registry->template->pageTitle = Lang::trans('nav.theVolunteers');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('nav.theVolunteers'), 'link' => NULL],
        ]);
        $this->registry->template->content = $this->renderPeopleContent();
        $renderer = new template_renderer();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

    private function renderPeopleContent()
    {
        $founders = $this->people->renderFoundersPage();
        $volunteersPage = $this->people->renderVolunteersPage();
        $header = Lang::trans('nav.activeVolunteers');
        return <<<EOF
            <script>
                
                $(document).ready(function () {
                    var grid = $('.grid').masonry({});
                    grid.imagesLoaded().progress( function() {
                        grid.masonry('layout');
                      });
                });

                
            </script>
            <script src="/resources/js/masonry.min.js"></script> 
            <script src="/resources/js/imagesloaded.js"></script> 
            
            <style>
                .media-object{
                    max-width: 100px;
                }
                .media{
                    display: table; overflow: hidden;
                }
                .media-bottom {
                    display: table-cell; vertical-align: bottom;
                }
                .head4 {
                    color: #A94716 !important;
                    font-size: 1.2rem;
                }
            </style>        
            <h1>{$header}</h1>
            <div class="row">
            {$founders}
            </div>
            <div>
            {$volunteersPage}
            </div>
EOF;
    }
}
