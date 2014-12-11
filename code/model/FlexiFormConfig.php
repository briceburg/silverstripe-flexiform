<?php

class FlexiFormConfig extends DataObject {

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar',
        'FormIdentifier' => 'Varchar'
    );


    private static $has_one = array(
        'Handler' => 'FlexiFormHandler',
    );

    private static $has_many = array(
        'HandlerSettings' => 'FlexiFormHandlerSetting'
    );


    public function getFlexi(){
        return DataObject::get_by_id($this->FlexiFormClass, $this->FlexiFormID);
    }


    public function onBeforeDelete()
    {
        foreach($this->HandlerSettings() as $setting) {
            $setting->delete();
        }

        return parent::onBeforeDelete();
    }

}