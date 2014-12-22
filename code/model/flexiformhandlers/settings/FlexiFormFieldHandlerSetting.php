<?php

class FlexiFormFieldHandlerSetting extends FlexiFormIntHandlerSetting {

    private static $allowed_field_types = array();

    public function getCMSField($name)
    {

        $fields = array();

        if($config = $this->FlexiFormConfig()) {
            if($flexi = $config->getFlexi()) {
                $filter = $this->stat('allowed_field_types');
                foreach($flexi->FlexiFormFields() as $field){
                    if(!empty($filter)) {
                        foreach($filter as $class) {
                            if($field->is_a($class)) {
                                $fields[$field->ID] = $field->Name;
                                break;
                            }
                        }
                    } else {
                        $fields[$field->ID] = $field->Name;
                    }
                }
            }
        }

        return new DropdownField($name,null,$fields,$this->getValue());
    }

}