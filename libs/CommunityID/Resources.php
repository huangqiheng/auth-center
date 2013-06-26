<?php

class CommunityID_Resources
{
    public static function getResourcePath($fileName)
    {
        $locale = Zend_Registry::get('Zend_Locale');
        $localeElements = explode('_', $locale);

        $template = Zend_Registry::get('config')->environment->template;
        if ($template == 'default') {
            $template = '';
        } else {
            $template = "_$template";
        }

        if (file_exists(APP_DIR . "/resources$template/$locale/$fileName")) {
            $file = APP_DIR . "/resources$template/$locale/$fileName";
        } else if (count($localeElements == 2)
                && file_exists(APP_DIR . "/resources$template/{$localeElements[0]}/$fileName")) {
            $file = APP_DIR . "/resources$template/{$localeElements[0]}/$fileName";
        } else if (file_exists(APP_DIR . "/resources$template/en/$fileName")){
            $file = APP_DIR . "/resources$template/en/$fileName";
        } else if (file_exists(APP_DIR . "/resources/$locale/$fileName")) {
            $file = APP_DIR . "/resources/$locale/$fileName";
        } else if (count($localeElements == 2)
                && file_exists(APP_DIR . "/resources/{$localeElements[0]}/$fileName")) {
            $file = APP_DIR . "/resources/{$localeElements[0]}/$fileName";
        } else if (file_exists(APP_DIR . "/resources/en/$fileName")){
            $file = APP_DIR . "/resources/en/$fileName";
        } else {
            throw new Exception("Resource $fileName could not be found");
        }

        return $file;
    }
}
