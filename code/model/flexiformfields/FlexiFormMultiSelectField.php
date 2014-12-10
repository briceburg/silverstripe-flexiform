<?php

class FlexiFormMultiSelectField extends FlexiFormDropdownField
{

    private static $field_class = 'ListboxField';

    private static $field_label = 'Multi Select Field';

    private static $field_description = 'Displays supplied options to choose one or more from.';

    protected $default_empty_string = 'Please Choose';

    private static $db = array(
        'EmptyString' => 'Varchar'
    );


    public function getFormField($title = null, $value = null, $required = false)
    {
        $field = parent::getFormField($title, $value, $required);

        $field->setMultiple(true);

        return $field;
    }


}