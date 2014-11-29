<?php

class FlexiFormFieldOption extends DataObject {

    private static $db = array(
        'Label' => 'Varchar',
        'Value' => 'Varchar',
        'SortOrder' => 'Int'
    );

    private static $has_one = array(
        'Field' => 'FlexiFormField'
    );

    private static $default_sort = array(
        'SortOrder'
    );


    public function validate() {
        $result = parent::validate();

        if($result->valid()) {
            if(empty($this->Value)) {
                $result->error('Option Values cannot be blank.');
            }
        }

        return $result;
    }

    public function getLabel(){

        $label = $this->getField('Label');
        return (empty($label)) ? $this->Value : $label;
    }

}