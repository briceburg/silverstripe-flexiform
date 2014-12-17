<?php

class FlexiFormConfig extends DataObject
{

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar',
        'InitializedFields' => 'Boolean',
        'FormIdentifier' => 'Varchar'
    );

    private static $has_one = array(
        'Handler' => 'FlexiFormHandler'
    );

    private static $has_many = array(
        'HandlerSettings' => 'FlexiFormHandlerSetting'
    );

    public function getFlexi()
    {
        return DataObject::get_by_id($this->FlexiFormClass, $this->FlexiFormID);
    }

    /**
     * Shorthand fetching of settings via $flexi->FlexiConf('Setting.<name>')
     * @see DataObject::relField()
     */
    public function relField($fieldName)
    {
        if (strpos($fieldName, '.') !== false) {
            $relations = explode('.', $fieldName);
            if ($relations[0] == 'Setting') {
                return $this->HandlerSettings()
                    ->filter('Setting', $relations[1])
                    ->first();
            }
        }

        return parent::relField($fieldName);
    }

    public function updateHandlerSettings(Array $settings)
    {
        foreach ($settings as $component => $value) {
            if ($setting = $this->relField("Setting.$component")) {
                $setting->Value = $value;
                $setting->write();
            }
        }
    }

    public function onBeforeWrite()
    {
        if ($this->exists() && $this->isChanged('HandlerID')) {
            foreach ($this->HandlerSettings()->filter('HandlerID:not', $this->HandlerID) as $item) {
                // remove on HasManyList only orphans item. Actually delete it.
                $item->delete();
            }
        }
        return parent::onBeforeWrite();
    }

    public function onBeforeDelete()
    {
        foreach ($this->HandlerSettings() as $item) {
            // remove on HasManyList only orphans item. Actually delete it.
            $item->delete();
        }

        return parent::onBeforeDelete();
    }
}