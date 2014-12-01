<?php

class FlexiFormSubmission extends DataObject {

    private static $db = array(
        'FormID' => 'Int',
        'FormClass' => 'Varchar',
        'IPAddress' => 'Varchar(45)'
    );

    private static $has_one = array(
        'Member' => 'Member'
    );

    private static $has_many = array(
        'Values' => 'FlexiFormSubmissionValue'
    );


    public function populateDefaults() {

        $this->IPAddress = Controller::curr()->getRequest()->getIP();
        $this->MemberID = Member::currentUserID();

        return parent::populateDefaults();
    }
}


