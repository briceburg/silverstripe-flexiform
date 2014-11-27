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

    public static function CreateFlexiField($field_type, $definition){

        if(!isset($definition['Name']) || empty($definition['Name'])) {
            throw new ValidationException('Flexi Field definitions must specify a Name');
        }

        if(isset($definition['Options']) && !is_array($definition['Options'])) {
            throw new ValidationException('Options must be an Array in Flexi Field definitions');
        }

        if(!class_exists($field_type)) {
            throw new ValidationException($field_type . ' is an unknown FlexiFormField Type');
        }

        if(isset($definition['Options']) && !singleton($field_type)->is_a('FlexiFormOptionField')) {
            throw new ValidationException($field_type . ' must subclass FlexiFormOptionField to contain options');
        }

        $field = new $field_type();
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

    public static function AutoCreateFlexiField($field_type, $definition){

        $readonly = (isset($definition['Readonly']) && $definition['Readonly']);

        $filter = array(
            'FieldName' => $definition['Name'],
            'Readonly' => $readonly
        );

        // allow same names on non readonly fields if they're different classes
        if(!$readonly) {
            $filter['ClassName'] = $field_type;
        }

        // only create field if it's name doesn't yet exist
        if (! FlexiFormField::get()->filter($filter)->first()) {

            if ($field = FlexiFormUtil::CreateFlexiField($field_type, $definition)) {
                $prefix = ($field->Readonly) ? 'Readonly' : 'Normal';
                DB::alteration_message(
                "flexiforms - Created $prefix $field_type named `{$field->FieldName}`.",
                "created");
            }
        }
    }
}
