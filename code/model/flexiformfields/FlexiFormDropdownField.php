<?php

class FlexiFormDropdownField extends FlexiFormOptionField
{

    private static $field_class = 'DropdownField';

    private static $field_label = 'Dropdown Field';

    private static $field_description = 'Displays supplied options to choose one from.';

    protected $default_empty_string = 'Please Choose';

    private static $db = array(
        'EmptyString' => 'Varchar'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('EmptyString')->description = 'Displays above all options';

        return $fields;
    }

    public function getFormField($title = null, $value = null, $required = false)
    {
        $field = parent::getFormField($title, $value, $required);

        if (! $this->DefaultValue) {
            $field->setEmptyString($this->getEmptyString());
        }

        return $field;
    }

    public function getEmptyString()
    {
        $value = $this->getField('EmptyString');
        return (empty($value)) ? $this->default_empty_string : $value;
    }
}