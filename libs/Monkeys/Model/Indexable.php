<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

interface Monkeys_Model_Indexable
{
    public function getProjectId();
    public function getExcerpt();
    public function getContentWithoutTags();
    public function isPublished();
    public function isDraft();
    public function getTitle();
    public function getType();
}
