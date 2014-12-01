<?php

class FlexiFormCheckboxField extends FlexiFormField {
    protected $field_class = 'CheckboxField';
    protected $field_label = 'Checkbox';
    protected $field_description = 'Displays a checkbox field. Yes/No Value.';

    public function getDefaultValueFormField($field_name = 'FieldDefaultValue')
    {
        $field = new DropdownField($field_name, 'Default Value', array(
            0 => 'unchecked',
            1 => 'checked'
        ));
        return $field;
    }

}