<?php

/**
 * Utility functions for the FlexiForms module.
 */
class FlexiFormUtil
{
    public static function get_module_dir()
    {
        return basename(dirname(dirname(__DIR__)));
    }


    public static function include_requirements()
    {
        $moduleDir = self::get_module_dir();
        Requirements::css($moduleDir . '/css/flexiforms.css');
    }

    public static function CreateFlexiField($definition){

        if(!isset($definition['Type']) || empty($definition['Type'])) {
            throw new ValidationException('Flexi Field definitions must specify a Type');
        }

        if(!isset($definition['Name']) || empty($definition['Name'])) {
            throw new ValidationException('Flexi Field definitions must specify a Name');
        }

        if(isset($definition['Options']) && !is_array($definition['Options'])) {
            throw new ValidationException('Options must be an Array in Flexi Field definitions');
        }

        if(!class_exists($definition['Type'])) {
            throw new ValidationException($definition['Type'] . ' is an unknown FlexiFormField Type');
        }

        $field = new $definition['Type']();
        $field->FieldName = $definition['Name'];

        if(isset($definition['DefaultValue'])) {
            $field->FieldDefaultValue = $definition['DefaultValue'];
        }

        // add field properties
        foreach(array_intersect_key($definition,$field->db()) as $property => $value) {
            $field->$property = $value;
        }

        // disable validation while we write
        $flag = Config::inst()->get('DataObject', 'validation_enabled');
        Config::inst()->update('DataObject', 'validation_enabled',false);
        $field->write();
        Config::inst()->update('DataObject', 'validation_enabled',$flag);


        if(isset($definition['Options'])) {
            $options = $field->Options();
            foreach($definition['Options'] as $value => $label) {
                $option = new FlexiFormFieldOption();
                $option->Value = $value;
                $option->Label = $label;
                $options->add($option);
            }
        }

        return $field;
    }
}
