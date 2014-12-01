<?php

// helper to keep track of form classes. Class manifest does not allow us
// to lookup classes that have been extended by a particular extension...
class FlexiFormHandlerMapping extends DataObject
{

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar'
    );

    private static $has_one = array(
        'Handler' => 'FlexiFormHandler'
    );

    public static function count($handler)
    {
        if (! $handler->exists()) {
            throw new Exception('Handler does not exist');
        }

        return DataObject::get(get_class())->filter('HandlerID', $handler->ID)->count();
    }

    public static function addMapping($handler, $flexi)
    {
        if (! $handler->exists()) {
            throw new Exception('Handler does not exist');
        }
        if (! $flexi->exists()) {
            throw new Exception('Form does not exist');
        }

        if (! $mapping = DataObject::get(get_class())->filter(
            array(
                'FlexiFormID' => $flexi->ID,
                'FlexiFormClass' => $flexi->class
            ))->first()) {
            $mapping_class = get_class();
            $mapping = new $mapping_class();
            $mapping->FlexiFormID = $flexi->ID;
            $mapping->FlexiFormClass = $flexi->class;
        }

        $mapping->HandlerID = $handler->ID;
        $mapping->write();
    }

    public static function removeFormMapping($flexi)
    {
        if (! $flexi->exists()) {
            throw new Exception('Form does not exist');
        }

        foreach (DataObject::get(get_class())->filter(
            array(
                'FlexiFormID' => $flexi->ID,
                'FlexiFormClass' => $flexi->class
            )) as $mapping) {
            $mapping->delete();
        }
    }

    public static function removeHandlerMappings($handler)
    {
        if (! $handler->exists()) {
            throw new Exception('Handler does not exist');
        }

        foreach (DataObject::get(get_class())->filter(
            array(
                'HandlerID' => $handler->ID
            )) as $mapping) {
            $mapping->delete();
        }
    }
}