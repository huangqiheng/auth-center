<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* This helper can only be used from IdentityController and OpenidController
*/
class CommunityID_Controller_Action_Helper_ProviderUrl
        extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($config)
    {
        $currentUrl = urldecode(Zend_OpenId::selfURL());

        if ($config->subdomain->enabled) {
            $protocol = Monkeys_Controller_Action::getProtocol();
            preg_match('#(.*)\.'.$config->subdomain->hostname.'#', $currentUrl, $matches);

            return "$protocol://"
                       . ($config->subdomain->use_www? 'www.' : '')
                       . $config->subdomain->hostname
                       . '/openid/provider';
        } else {
            preg_match('#(.*)/(identity|openid)?/#', $currentUrl, $matches);

            return $matches[1] . '/openid/provider';
        }
    }
}
