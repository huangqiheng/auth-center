<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_AntiSpam extends Zend_Service_Akismet
{
    const TYPE_AKISMET = 1;
    const TYPE_TYPEPAD = 2;

    const TYPEPAD_API_URL = 'api.antispam.typepad.com';

    private $_type;

    public function __construct($type)
    {
        $this->_type = $type;

        switch ($type) {
            case self::TYPE_AKISMET:
                $apiKey = Zend_Registry::get('config')->akismet->key;
                break;
            case self::TYPE_TYPEPAD:
                $apiKey = Zend_Registry::get('config')->typePadAntiSpam->key;
                break;
            default:
                throw new Exception('Wrong spam service type');
        }

        parent::__construct($apiKey, 'http://www.kb-m.com');
    }

    protected function _post($host, $path, array $params)
    {
        if ($this->_type == self::TYPE_TYPEPAD) {
            $caller = $this->_getCallerMethod();
            if (strtolower($caller) == 'verifykey') {
                $host = self::TYPEPAD_API_URL;
            } else {
                $host = $this->getApiKey() . '.' . self::TYPEPAD_API_URL;
            }
        }

        return parent::_post($host, $path, $params);
    }

    /**
    * @return string
    */
    private function _getCallerMethod()
    {
        $backTrace = debug_backtrace();

        return $backTrace[2]['function'];
    }
}
