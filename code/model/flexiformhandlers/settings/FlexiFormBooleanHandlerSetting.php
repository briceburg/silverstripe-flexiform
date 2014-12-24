<?php

class FlexiFormBooleanHandlerSetting extends FlexiFormHandlerSetting {

    private static $casting = array(
        'Value' => 'Boolean'
    );

    public function getCMSField($name)
    {
        return new CheckboxField($name,null,$this->getValue());
    }

    public function getValue(){
        return (bool) parent::getValue();
    }

}