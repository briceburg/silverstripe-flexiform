<?php

class FlexiFormHandlerSetting extends DataObject
{
    private static $casting = array(
        'Value' => 'Varchar'
    );

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar',
        'Component' => 'Varchar',
        'Value' => 'Text'
    );

    private static $has_one = array(
        'Handler' => 'FlexiFormHandler'
    );


    // returns the setting field used to edit its value
    public function getCMSField($name, $title = null)
    {
        return new TextField($name, $title);
    }

    public function CastedValue()
    {
        return DBField::create_field($this->stat('casting')['Value'], $this->getField('Value'), 'Value', $this);
    }

}