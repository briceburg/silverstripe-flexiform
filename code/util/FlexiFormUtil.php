<?php

/**
 * Utility functions for the FlexiForms module.
 */
class FlexiFormUtil
{
    protected static $identifier_cache = array();

    public static function get_module_dir()
    {
        return basename(dirname(dirname(__DIR__)));
    }

    public static function include_requirements()
    {
        $moduleDir = self::get_module_dir();
        Requirements::css($moduleDir . '/css/flexiform.css');
    }

    public static function GetFlexiByIdentifier($identifier){
        if(isset(self::$identifier_cache[$identifier])) {
            return self::$identifier_cache[$identifier];
        }

        if($config = FlexiFormConfig::get()->filter('FormIdentifier',$identifier)->first()) {
            if($flexi = $config->getFlexi()) {
                self::$identifier_cache[$identifier] = $flexi;
                return $flexi;
            }
        }
    }

    /* @deprecated no longer used
    public static function GetFlexiFormClasses(){
        // @todo more efficient manner for finding classes extended by _extension_ ?

        $classes = array();
        foreach(SS_ClassLoader::instance()->getManifest()->getDescendantsOf('DataObject') as $className) {
            if(Object::has_extension($className,'FlexiFormExtension')) {
                $classes[] = $className;
            }
        }
        return $classes;
    }
    */

    public static function CreateFlexiField($className, $definition)
    {
        if (! isset($definition['Name']) || empty($definition['Name'])) {
            throw new ValidationException('Flexi Field definitions must specify a Name');
        }

        if (isset($definition['Options']) && ! is_array($definition['Options'])) {
            throw new ValidationException('Options must be an Array in Flexi Field definitions');
        }

        if (! class_exists($className)) {
            throw new ValidationException($className . ' is an unknown FlexiFormField Type');
        }

        $singleton = singleton($className);

        if (! $singleton->is_a('FlexiFormField')) {
            throw new ValidationException($className . ' must subclass FlexiFormField');
        }

        if (isset($definition['Options']) && ! $singleton->is_a('FlexiFormOptionField')) {
            throw new ValidationException($className . ' must subclass FlexiFormOptionField to contain options');
        }

        // instantiate new object
        /////////////////////////


        $obj = new $className();
        $obj->FieldName = $definition['Name'];

        if (isset($definition['DefaultValue'])) {
            $obj->FieldDefaultValue = $definition['DefaultValue'];
        }

        // add field properties
        foreach (array_intersect_key($definition, $obj->db()) as $property => $value) {
            $obj->$property = $value;
        }

        // disable validation while we write
        $flag = Config::inst()->get('DataObject', 'validation_enabled');
        Config::inst()->update('DataObject', 'validation_enabled', false);
        $obj->write();
        Config::inst()->update('DataObject', 'validation_enabled', $flag);

        if (isset($definition['Options'])) {
            $options = $obj->Options();
            foreach ($definition['Options'] as $value => $label) {
                $option = new FlexiFormFieldOption();
                $option->Value = $value;
                $option->Label = $label;
                $options->add($option);
            }
        }

        return $obj;
    }

    public static function AutoCreateFlexiField($className, $definition)
    {
        $readonly = (isset($definition['Readonly']) && $definition['Readonly']);

        $filter = array(
            'FieldName' => $definition['Name'],
            'Readonly' => $readonly
        );

        // allow same names on non readonly fields if they're different classes
        if (! $readonly) {
            $filter['ClassName'] = $className;
        }

        // only create field if it's name doesn't yet exist
        if (! FlexiFormField::get()->filter($filter)->first()) {

            if ($obj = FlexiFormUtil::CreateFlexiField($className, $definition)) {
                $prefix = ($obj->Readonly) ? 'Readonly' : 'Normal';
                DB::alteration_message("flexiform - Created $prefix $className named `{$obj->FieldName}`.",
                    "created");
            }
        }
    }

    public static function CreateFlexiHandler($className, $definition)
    {
        if (! isset($definition['Name']) || empty($definition['Name'])) {
            throw new ValidationException('Flexi Handler definitions must specify a Name');
        }

        if (! class_exists($className)) {
            throw new ValidationException($className . ' is an unknown FlexiFormHandler Type');
        }

        $singleton = singleton($className);

        if (! $singleton->is_a('FlexiFormHandler')) {
            throw new ValidationException($className . ' must subclass FlexiFormHandler');
        }

        // instantiate new object
        /////////////////////////


        $obj = new $className();
        $obj->HandlerName = $definition['Name'];
        $obj->SystemCreated = true; // flag as system created

        // add field properties
        foreach (array_intersect_key($definition, $obj->db()) as $property => $value) {
            $obj->$property = $value;
        }

        // disable validation while we write
        $obj->write();

        return $obj;
    }

    public static function AutoCreateFlexiHandler($className, $definition)
    {
        // Handler Names are Unique.
        $filter = array(
            'HandlerName' => $definition['Name'],
        );

        // only create handler it doesn't yet exist
        if (! FlexiFormHandler::get()->filter($filter)->first()) {

            if ($obj = FlexiFormUtil::CreateFlexiHandler($className, $definition)) {
                DB::alteration_message("flexiform - Created $className named `{$obj->HandlerName}`.",
                    "created");
            }
        }
    }

}
