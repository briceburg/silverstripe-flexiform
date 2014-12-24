<?php

class FlexiFormSubmission extends DataObject
{

    private static $db = array(
        'FlexiFormID' => 'Int',
        'FlexiFormClass' => 'Varchar',
        'IPAddress' => 'Varchar(45)',
        'StatusMessages' => 'Text'
    );

    private static $has_one = array(
        'Member' => 'Member'
    );

    private static $has_many = array(
        'Values' => 'FlexiFormSubmissionValue'
    );

    private static $summary_fields = array(
        'SubmittedBy' => 'Submitted By',
        'Created' => 'Time Submitted',
        'Values.Count' => 'Response Count'
    );

    protected $messages = array();

    public function populateDefaults()
    {
        $this->IPAddress = Controller::curr()->getRequest()->getIP();
        $this->MemberID = Member::currentUserID();

        return parent::populateDefaults();
    }

    public function getCMSFields()
    {
        $fields = singleton('DataObject')->getCMSFields();

        $fields->addFieldsToTab('Root.Main',
            array(
                new ReadonlyField('SubmittedBy', 'Submitted By'),
                new ReadonlyField('IPAddress', 'IP Address'),
                new ReadonlyField('Created', 'Time Submitted'),
                new ReadonlyField('StatusMessages'),
                $field = new GridField('Values', 'Responses', $this->Values(),
                    new GridFieldConfig_FlexiFormSubmissionValues())
            ));

        $field->addExtraClass('flexiform');

        return $fields;
    }

    // utility
    //////////
    public function getSubmittedBy()
    {
        return ($this->MemberID) ? $this->Member()->getName() : 'Site Visitor';
    }

    public function relField($fieldName)
    {
        // check if fieldName is 'Values.<fieldname'
        if (substr($fieldName, 0, 7) == 'Values.') {
            if ($submission_value = FlexiFormSubmissionValue::get()->filter(
                array(
                    'SubmissionID' => $this->ID,
                    'Name' => substr($fieldName, 7)
                ))->first()) {

                return $submission_value->ColumnValue();
            }
        }

        return parent::relField($fieldName);
    }

    public function addStatusMessage($message) {
        $this->messages[] = $message;
    }

    public function onBeforeWrite(){
        while($message = array_shift($this->messages)) {
        $prefix = empty($this->StatusMessages) ? '' : ' ~~ ';
            $this->StatusMessages .= $prefix . $message;
        }

        return parent::onBeforeWrite();
    }
}


