<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class News_EditController extends CommunityID_Controller_Action
{
    protected $_numCols = 2;

    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->articleForm)) {
            $this->view->articleForm = $appSession->articleForm;
            unset($appSession->articleForm);
        } else {
            $this->view->articleForm = new News_Form_Article();
            $news = new News_Model_News();
            if ($this->_getParam('id') && ($article = $news->getRowInstance($this->_getParam('id')))) {
                $this->view->articleForm->populate(array(
                    'title'     => $article->title,
                    'date'      => $article->date,
                    'excerpt'   => $article->excerpt,
                    'content'   => $article->content,
                ));
                $this->view->articleId = $article->id;
            }
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function addAction()
    {
        $this->_forward('index');
    }

    public function saveAction()
    {
        $form = new News_Form_Article();
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->articleForm = $form;
            $this->_forward('index');
            return;
        }

        $news = new News_Model_News();
        if ($this->_getParam('id')) {
            if (!$article = $news->getRowInstance($this->_getParam('id'))) {
                $this->_helper->FlashMessenger->addMessage($this->view->translate('The article doesn\'t exist.'));
                $this->_redirect('/news');
                return;
            }
        } else {
            $article = $news->createRow();
        }


        require_once 'htmlpurifier/library/HTMLPurifier.auto.php';

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $cleanHtml = $purifier->purify($form->getValue('content'));

        $article->title = $form->getValue('title');
        $article->date = $form->getValue('date');
        $article->excerpt = $form->getValue('excerpt');
        $article->content = $cleanHtml;
        $article->save();

        $this->_helper->FlashMessenger->addMessage($this->view->translate('The article has been saved.'));

        $this->_redirect('/news');
    }

    public function deleteAction()
    {
        $news = new News_Model_News();
        if (!$article = $news->getRowInstance($this->_getParam('id'))) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('The article doesn\'t exist.'));
        } else {
            $article->delete();
            $this->_helper->FlashMessenger->addMessage($this->view->translate('The article has been deleted.'));
        }

        $this->_redirect('/news');
    }
}
