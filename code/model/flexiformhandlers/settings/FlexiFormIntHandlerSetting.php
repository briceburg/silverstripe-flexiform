<?php

class FlexiFormIntHandlerSetting extends FlexiFormHandlerSetting {

    private static $casting = array(
        'Value' => 'Int'
    );

    public function getCMSField($name)
    {
        return new NumericField($name,null,$this->getValue());
    }

    public function getValue(){
        return (int) parent::getValue();
    }

}