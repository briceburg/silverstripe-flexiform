<?php

class FlexiFormSubmission extends DataObject {

    private static $db = array(

    );

    private static $has_one = array(
        'Form' => 'FlexiForm'
    );


    public function populateDefaults() {

        return parent::populateDefaults();
    }





}