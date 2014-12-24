<?php

class FlexiFormOptionField extends FlexiFormField
{

    public function canCreate($member = null)
    {
        // allow creation of descendents, not this class itself.
        return ($this->class === __CLASS__) ? false : parent::canCreate($member);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->Readonly) {
            $fields->addFieldsToTab('Root.Main',
                new ReadonlyField('ReadonlyOptions', 'Options',
                    implode(', ', $this->Options()
                        ->column('Label'))));
        } else {

            $config = new GridFieldConfig_FlexiFormOption();

            $fields->addFieldToTab('Root.Main', new GridField('Options', 'Options', $this->Options(), $config));
        }

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

    // utility
    //////////
    public function getFormField($title = null, $value = null, $required = false)
    {
        $field = parent::getFormField($title, $value, $required);

        $field->setSource($this->Options()
            ->map('Value', 'Label')
            ->toArray());

        return $field;
    }

    public function getDefaultValueFormField($field_name = 'FieldDefaultValue')
    {
        $field = new DropdownField($field_name, 'Default Value', array());
        if ($this->Options()->exists()) {
            $field->setSource(
                $this->Options()
                    ->map('Value', 'Label')
                    ->toArray());
        }
        $field->setEmptyString('None (Displays Empty String)');

        $field->description = 'Optional. This value will be preselectd.';
        return $field;
    }
}