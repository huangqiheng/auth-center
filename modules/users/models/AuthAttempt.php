<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_AuthAttempt extends Zend_Db_Table_Row_Abstract
{
    const MAX_ATTEMPTS_ALLOWED = 3;
    const MIN_MINUTES_BETWEEN_ATTEMPTS = 30;

    public function addFailure()
    {
        $this->failed_attempts++;
        $this->last_attempt = date('Y-m-d H:i:s');
    }

    public function surpassedMaxAllowed()
    {
        return ($this->failed_attempts >= self::MAX_ATTEMPTS_ALLOWED)
            && $this->last_attempt > date('Y-m-d H:i:s', time() - self::MIN_MINUTES_BETWEEN_ATTEMPTS * 60);
    }
}
