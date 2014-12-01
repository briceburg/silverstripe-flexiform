<?php

class FlexiFormSubmissionValue extends DataObject {

    private static $db = array(
        'FormFieldID' => 'Int',
        'FormFieldClass' => 'Varchar',
        'Name' => 'Varchar',
        'Value' => 'Text'
    );

    private static $has_one = array(
        'Submission' => 'FormFieldSubmission',
        'Member' => 'Member'
    );

}