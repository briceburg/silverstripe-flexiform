<?php

class FlexiFormHandler extends DataObject
{

    protected $handler_label = 'Override Me';

    protected $handler_description = 'Override Me';

    // used to automatically generate handlers during /dev/build
    private static $required_handler_definitions = array();

    private static $db = array(
        'HandlerName' => 'Varchar',
        'Description' => 'Varchar(255)',
        'Readonly' => 'Boolean',
        'SubmitButtonText' => 'Varchar'
    );

    public function populateDefaults()
    {
        $this->SubmitButtonText = 'Submit';

        return parent::populateDefaults();
    }

    public function canDelete($member = null)
    {
        if ($this->Readonly) {
            return false;
        }

        if ($this->getSelected()) {
            return false;
        }

        // more than the current form will be impacted...
        if ($this->FormCount()) {
            return false;
        }

        return parent::canDelete($member);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Readonly');
        $fields->dataFieldByName('HandlerName')->setTitle('Name');

        if ($this->Readonly) {
            $fields = $fields->transform(new ReadonlyTransformation());
        }

        return $fields;
    }

    public function updateCMSFlexiTabs(TabSet $fields, $form)
    {
        $field = new HiddenField('FlexiFormHandlerSettings', 'FlexiFormHandlerSettings', true);
        $fields->insertAfter($field, 'HandlerSettings');

        // FlexiFormHandlerSetting[<fieldname>] hack to allow editing handler
        //  from form gridfield, perhaps use gridfieldaddons editor instead?

        $field = new TextField('FlexiFormHandlerSetting[SubmitButtonText]', 'Submit Button Text',$this->SubmitButtonText);
        $fields->insertAfter($field, 'HandlerSettings');
    }

    public function getFrontEndFormValidator($flexi, $front_end_fields){

        $validator = new RequiredFields();
        foreach($flexi->FlexiFormFields()->filter('Required',true) as $field) {
            $validator->addRequiredField($field->SafeName());
        }

        return $validator;
    }

    protected function onSubmit()
    {}

    protected function onSuccess()
    {}

    // Templates
    ////////////
    public function Label()
    {
        return $this->handler_label;
    }

    public function DescriptionPreview()
    {
        return $this->dbObject('Description')->LimitCharacters(77);
    }

    public function FormCount()
    {
        return ($this->exists()) ? FlexiFormHandlerMapping::count($this) : 0;
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

    public function getSelected()
    {
        return ($this->ID == $this->stat('selected_handler_id'));
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

    public function getTitle()
    {
        $readonly = ($this->Readonly) ? '*' : '';
        return "{$this->HandlerName} ({$this->handler_label})$readonly";
    }

    public function requireDefaultRecords()
    {
        foreach ($this->getRequiredHandlerDefinitions() as $definition) {
            FlexiFormUtil::AutoCreateFlexiHandler($this->ClassName, $definition);
        }
        return parent::requireDefaultRecords();
    }

    public function onBeforeWrite()
    {
        if (empty($this->Description)) {
            $this->Description = $this->handler_description;
        }
        return parent::onBeforeWrite();
    }

    public function onBeforeDelete()
    {
        FlexiFormHandlerMapping::removeHandlerMappings($this);

        return parent::onBeforeDelete();
    }
}