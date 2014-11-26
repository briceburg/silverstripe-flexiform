<?php

class FlexiFormFieldOption extends DataObject {

    private static $db = array(
        'Label' => 'Varchar',
        'Value' => 'Varchar'
    );

    private static $has_one = array(
        'Field' => 'FlexiFormField'
    );




}