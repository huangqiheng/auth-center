<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_SigninImage extends Zend_Db_Table_Row_Abstract
{
    const MAX_WIDTH = 165;
    const MAX_HEIGHT = 195;

    private $_width;
    private $_height;

    public function getHeight()
    {
        list (,$height) = $this->_getDimensions();
        return $height;
    }

    public function getWidth()
    {
        list ($width,) = $this->_getDimensions();
        return $width;
    }

    private function _getDimensions()
    {
        if (!isset($this->_width) || !isset($this->_height)) {
            $image = imagecreatefromstring($this->image);
            $this->_width = imagesx($image);
            $this->_height = imagesy($image);

            if ($this->_height >= $this->_width * self::MAX_HEIGHT / self::MAX_WIDTH
                    && $this->_height > self::MAX_HEIGHT) {
                $newHeight = self::MAX_HEIGHT;
                $newWidth = floor($width * $newHeight / $height);

                $this->_height = $newHeight;
                $this->_width = $newWidth;
            } elseif ($this->_height < $this->_width * self::MAX_HEIGHT / self::MAX_WIDTH
                    && $this->_width > self::MAX_WIDTH) {
                $newWidth = self::MAX_WIDTH;
                $newHeight = floor($newWidth * $this->_height / $this->_width);
                $this->_height = $newHeight;
                $this->_width = $newWidth;
            }
        }

        return array($this->_width, $this->_height);
    }
}

