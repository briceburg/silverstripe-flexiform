<?php

class FlexiFormSubmissionValue extends DataObject {

    private static $db = array(
        'FormFieldID' => 'Int',
        'FormFieldClass' => 'Varchar',
        'Name' => 'Varchar',
        'Value' => 'Text'
    );

    private static $has_one = array(
        'Submission' => 'FlexiFormSubmission'
    );

    private static $default_sort = array(
        'Name'
    );

    public function ColumnValue(){
        return (class_exists($this->FormFieldClass)) ?
            singleton($this->FormFieldClass)->transformValue($this->Value) :
            $this->Value;
    }
}