<?php

class FlexiFormDropdownField extends FlexiFormOptionField {
    protected $field_class = 'DropdownField';
    protected $field_label = 'Dropdown';
    protected $field_description = 'Displays supplied options to choose one from.';

    protected $default_empty_string = 'Please Choose';

    private static $db = array(
        'EmptyString' => 'Varchar'
    );

    public function populateDefaults(){
        $this->EmptyString = $this->default_empty_string;
        return parent::populateDefaults();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('EmptyString')->description = 'Displays above all options';

        return $fields;
    }




}