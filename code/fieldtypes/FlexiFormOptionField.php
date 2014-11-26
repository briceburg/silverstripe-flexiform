<?php

class FlexiFormOptionField extends FlexiFormField
{

    function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField('FieldDefaultValue', $this->getDefaultValueFormField());

        $config = new GridFieldConfig_FlexiFormOption();

        $fields->addFieldToTab('Root.Main', new GridField('Options', 'Options', $this->Options(), $config));

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        if ($result->valid()) {
            if (! $this->Options()->exists()) {
                $result->error("{$this->Label()} fields require at least one option to choose from");
                return $result;
            }

            $default_value = $this->FieldDefaultValue;
            if (! empty($default_value) && ! in_array($default_value, $this->Options()->column('Value'))) {
                $result->error("The default value of {$this->Name} must exist as an option value");
                return $result;
            }
        }

        return $result;
    }

    public function OptionsPreview()
    {
        $field = DBField::create_field('Text', implode(', ', $this->Options()->column('Value')));
        return $field->LimitCharacters(24);
    }

    public function getDefaultValueFormField($field_name = 'FieldDefaultValue')
    {
        $field = new DropdownField($field_name, 'Default Value');
        $field->setSource($this->Options()->map('Value','Label')->toArray());
        $field->setEmptyString('None (Displays Empty String)');

        $field->description = 'Optional. This value will be preselectd.';
        return $field;
    }
}