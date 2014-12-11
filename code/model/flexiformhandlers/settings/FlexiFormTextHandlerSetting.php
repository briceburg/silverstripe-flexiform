<?php

class FlexiFormTextHandlerSetting extends FlexiFormHandlerSetting {

    private static $casting = array(
        'Value' => 'Text'
    );

    public function getCMSField($name)
    {
        return new TextareaField($name, null, $this->Value);
    }

}