<?php

class FlexiFormOptionField extends FlexiFormField
{

    function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $config = new GridFieldConfig_FlexiFormOption();

        $fields->addFieldToTab('Root.Main', new GridField('Options', 'Options', $this->Options(), $config));

        return $fields;
    }

    public function validate() {
        $result = parent::validate();

        if($result->valid()) {
            if(!$this->Options()->exists()) {
                $result->error("{$this->Label()} fields require at least one option to choose from");
            }
        }

        return $result;
    }

    public function OptionsPreview()
    {
        $field = DBField::create_field('Text', implode(', ', $this->Options()->column('Value')));
        return $field->LimitCharacters(24);
    }
}