<?php

class FlexiFormHandlerSetting extends DataObject
{
    private static $casting = array(
        'Value' => 'Varchar'
    );

    private static $db = array(
        'Setting' => 'Varchar', // matches $db component of handler
        'Value' => 'Text'
    );

    private static $has_one = array(
        'FlexiFormConfig' => 'FlexiFormConfig',
        'Handler' => 'FlexiFormHandler'
    );


    // returns the setting field used to edit its value
    public function getCMSField($name)
    {
        return new TextField($name,null,$this->getValue());
    }

    public function CastedValue()
    {
        return DBField::create_field($this->stat('casting')['Value'], $this->getValue(), 'Value', $this);
    }

    public function getValue(){
        return ($this->getField('Value')) ?: $this->Handler()->getField($this->Setting);
    }

}