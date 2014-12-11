<?php

class FlexiFormTextHandlerSetting extends FlexiFormHandlerSetting {

    private static $casting = array(
        'Value' => 'Text'
    );

    public function getCMSField($name, $title = null)
    {
        return new TextareaField($name, $title);
    }

}