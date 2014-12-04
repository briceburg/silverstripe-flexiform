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

    public function updateCMSFlexiTabs(TabSet $fields, $flexi)
    {
        $field = new HiddenField('FlexiFormHandlerSettings', 'FlexiFormHandlerSettings', true);
        $fields->insertAfter($field, 'HandlerSettings');

        // FlexiFormHandlerSetting[<fieldname>] hack to allow editing handler
        //  from form gridfield, perhaps use gridfieldaddons editor instead?


        $field = new TextField('FlexiFormHandlerSetting[SubmitButtonText]', 'Submit Button Text',
            $this->SubmitButtonText);
        $fields->insertBefore($field, 'FlexiFormHandlerSettings');
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
     * @param Array $data
     * @param Form $form
     * @param DataObject $flexi The object extended by FlexiFormExtension
     * @return Boolean
     */
    public function onSubmit($data, $form, $flexi)
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
     * @param Array $data
     * @param Form $form
     * @param DataObject $flexi The object extended by FlexiFormExtension
     */
    public function onSuccess($flexi)
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