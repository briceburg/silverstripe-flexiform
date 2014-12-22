<?php

class FlexiFormHandler extends DataObject
{

    private static $handler_label = 'FlexiForm Handler';

    private static $handler_description = 'A Description of this Handler';


    /**
     * Define setting fields configurable by forms using this handler.
     * Limited to $db fields ATM, component name MUST match $db component name.
     *  <component> : <setting classname>
     *
     * @var Array
     */

    private static $handler_settings = array(
        'SubmitButtonText' => 'FlexiFormHandlerSetting'
    );

    // used to automatically generate handlers during /dev/build
    private static $required_handler_definitions = array();

    private static $db = array(
        'HandlerName' => 'Varchar',
        'Description' => 'Varchar(255)',
        'Readonly' => 'Boolean',

        // settings
        'SubmitButtonText' => 'Varchar'
    );

    private static $has_many = array(
        'Configs' => 'FlexiFormConfig'
    );

    public function populateDefaults()
    {
        $this->Description = $this->stat('handler_description');
        $this->SubmitButtonText = _t("FlexiFormHandler.DEFAULT_SUBMIT_BUTTON_TEXT", "Submit");

        return parent::populateDefaults();
    }

    public function canDelete($member = null)
    {
        if ($this->Readonly) {
            return false;
        }

        // forms are using this handler...
        if ($this->FormCount()) {
            return false;
        }

        return parent::canDelete($member);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Readonly');
        $fields->removeByName('Configs');
        $fields->dataFieldByName('HandlerName')->setTitle('Name');

        if ($this->Readonly) {
            $fields->replaceField('HandlerName',$fields->dataFieldByName('HandlerName')->performReadonlyTransformation());
            $fields->replaceField('Description',$fields->dataFieldByName('Description')->performReadonlyTransformation());
        }

        return $fields;
    }

    public function updateCMSFlexiTabs(TabSet $fields, TabSet $settings_tab, $flexi)
    {
        $field = new LiteralField('HandlerSettings', "<h3>Handler Settings</h3>");
        $settings_tab->push($field);


        $form_settings = $flexi->FlexiFormConf('HandlerSettings');
        foreach(Config::inst()->get($this->class, 'handler_settings', Config::INHERITED) as $component => $class) {

            if(!$setting = $flexi->FlexiFormSetting($component)) {
                $setting = new $class();
                $setting->Setting = $component;
                $setting->HandlerID = $this->ID;
                $form_settings->add($setting);
            }

            $field = $setting->getCMSField($component);

            // field name: FlexiFormConfig[Setting][<handler_id>][<setting_name>]
            $field->setName($this->getSettingFieldName($component));
            $settings_tab->push($field);
        }

    }

    public function getFrontEndFormValidator($flexi)
    {
        $validator = new RequiredFields();
        foreach ($flexi->FlexiFormFields()->filter('Required', true) as $field) {
            $validator->addRequiredField($field->SafeName());
        }

        return $validator;
    }

    /**
     * onSubmit is called after a submission is received and passed validation.
     * Use it to process form data - such as persisting the submission in
     * the database, sending [asynchronous] notifications , etc.
     *
     * Returning false will stop the form from processing. It's a good idea to
     * add an errorMessage to the form if you do this.
     *
     * @param Array $data Form Submission Data
     * @param FlexiForm $form Form Object
     * @param SS_HTTPRequest Request Object
     * @param DataObject $flexi The object extended by FlexiFormExtension
     * @return Boolean
     */
    public function onSubmit(Array $data, FlexiForm $form, SS_HTTPRequest $request, DataObject $flexi)
    {
        return true;
    }

    /**
     * onSuccess is called if onSubmit returns truthy. Use it to handle
     * post-submit workflow.
     *
     * If this function returns a value, the return value will be passed to
     * the template instead of the form. E.g. Display a "Thank You" Message.
     *
     * @param FlexiForm $form Form Object
     * @param DataObject $flexi The object extended by FlexiFormExtension
     */
    public function onSuccess(FlexiForm $form, DataObject $flexi)
    {}

    // Templates
    ////////////
    public function Label()
    {
        return $this->stat('handler_label');
    }

    public function DescriptionPreview()
    {
        return $this->dbObject('Description')->LimitCharacters(77);
    }

    public function FormCount()
    {
        return $this->Configs()->count();
    }

    public function getTitle()
    {
        $readonly = ($this->Readonly) ? '*' : '';
        return "{$this->HandlerName} ({$this->Label()})$readonly";
    }

    // Getters & Setters
    ////////////////////
    public function getRequiredHandlerDefinitions()
    {
        return $this->lookup('required_handler_definitions');
    }

    public function setRequiredHandlerDefinitions(Array $definitions)
    {
        return $this->set_stat('required_handler_definitions', $definitions);
    }


    // Utility Methods
    //////////////////
    private function lookup($lookup, $do_not_merge = false)
    {
        if ($do_not_merge &&
             $unmerged = Config::inst()->get($this->owner->class, $lookup, Config::EXCLUDE_EXTRA_SOURCES)) {
            return $unmerged;
        }

        return $this->owner->stat($lookup);
    }

    protected function getSettingFieldName($setting){
        return "FlexiFormConfig[Setting][{$this->ID}][$setting]";
    }

    public function requireDefaultRecords()
    {
        foreach ($this->getRequiredHandlerDefinitions() as $definition) {
            FlexiFormUtil::AutoCreateFlexiHandler($this->ClassName, $definition);
        }
        return parent::requireDefaultRecords();
    }

    public function onBeforeDelete()
    {
        foreach(FlexiFormHandlerSetting::get()->filter('HandlerID',$this->ID) as $item) {
            // remove on HasManyList only orphans item. Actually delete it.
            $item->delete();
        }

        return parent::onBeforeDelete();
    }
}