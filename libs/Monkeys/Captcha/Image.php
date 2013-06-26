<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

/**
* Same as Zend_Captcha_Image, but also readable by humans ;)
*/
class Monkeys_Captcha_Image extends Zend_Captcha_Image
{
    /**#@+
    * All uppercase, and removed letters/numbers than can be mistaken for one another
    */
    static $V  = array("A", "E", "I", "U", "Y");
    static $VN = array("A", "E", "I", "U", "Y", "3","4","5","6","7","8","9");
    static $C  = array("B","C","D","F","G","H","J","K","M","N","P","Q","R","S","T","W","X");
    static $CN = array("B","C","D","F","G","H","J","K","M","N","P","Q","R","S","T","W","X","3","4","5","6","7","8","9");
    /**#@-*/

    /**#@+
    * Reduced these levels
    */
    protected $_dotNoiseLevel = 25;
    protected $_lineNoiseLevel = 1;
    /**#@-*/

    /**
    * Gotta reproduce this function here 'cause PHP won't have late static binding till 5.3
    */
    protected function _generateWord()
    {
        $word       = '';
        $wordLen    = $this->getWordLen();
        $vowels     = $this->_useNumbers ? self::$VN : self::$V;
        $consonants = $this->_useNumbers ? self::$CN : self::$C;

        for ($i=0; $i < $wordLen; $i = $i + 2) {
            // generate word with mix of vowels and consonants
            $consonant = $consonants[array_rand($consonants)];
            $vowel     = $vowels[array_rand($vowels)];
            $word     .= $consonant . $vowel;
        }

        if (strlen($word) > $wordLen) {
            $word = substr($word, 0, $wordLen);
        }

        return $word;
    }

    /**
     * Set captcha word
     *
     * @param  string $word
     * @return Zend_Captcha_Word
     */
    protected function _setWord($word)
    {
        $word = strtoupper($word);
        $session       = $this->getSession();
        $session->word = $word;
        $this->_word   = $word;
        return $this;
    }

    /**
     * Validate the word
     *
     * Overriden to handle on in uppercase for better readability
     *
     * @see    Zend_Validate_Interface::isValid()
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $name = $this->getName();
        if (!isset($context[$name]['input'])) {
            $this->_error(self::MISSING_VALUE);
            return false;
        }
        $value = strtoupper($context[$name]['input']);
        $this->_setValue($value);

        if (!isset($context[$name]['id'])) {
            $this->_error(self::MISSING_ID);
            return false;
        }

        $this->_id = $context[$name]['id'];
        if ($value !== $this->getWord()) {
            $this->_error(self::BAD_CAPTCHA);
            return false;
        }

        return true;
    }
}
