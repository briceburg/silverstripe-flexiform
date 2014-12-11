<?php

class FlexiFormConfig extends DataObject {

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar'
    );


    private static $has_one = array(
        'FlexiFormHandler',
        'FlexiFormHandlerSetting'
    );



}