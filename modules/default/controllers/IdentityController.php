<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class IdentityController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        throw new Monkeys_BadUrlException($this->getRequest()->getRequestUri());
    }

    public function idAction()
    {
        $this->view->headLink()->headLink(array(
                    'rel'   => 'openid.server',
                    'href'  => $this->_helper->ProviderUrl($this->_config)
        ));
        $this->view->headLink()->headLink(array(
                    'rel'   => 'openid2.provider',
                    'href'  => $this->_helper->ProviderUrl($this->_config)
        ));

        $this->view->idUrl = urldecode(Zend_OpenId::selfURL());
    }
}
