<?php

class FlexiFormCheckboxField extends FlexiFormField
{

    private static $field_class = 'CheckboxField';

    private static $field_label = 'Checkbox Field';

    private static $field_description = 'Displays a checkbox field. Yes/No Value.';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField('FieldDefaultValue', $this->getDefaultValueFormField());

        return $fields;
    }

    public function getDefaultValueFormField($field_name = 'FieldDefaultValue')
    {
        $field = new DropdownField($field_name, 'Default Value',
            array(
                0 => 'unchecked',
                1 => 'checked'
            ));
        return $field;
    }

    public function transformValue($value) {
        return ($value) ? 'checked' : 'unchecked';
    }
}