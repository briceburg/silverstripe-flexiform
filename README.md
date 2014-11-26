silverstripe-flexiforms
=======================

GridField friendly, CMS configurable forms. 

Work in progress. Pre-Release.

Notes
=====
* FlexiForms extend `Page` , similar to userforms module
* Fields extend `FlexiFormField`, simple dataobjects
* Fields are related to Forms via *many_many* relationship so they can be used 
  * Field Name, Prompt, Default Value, and Requried/Validations are defined via _many_many_extraFields_
  * this encourages sharing of fields between forms, may create custom built-in field types(?)
  
  
Event Registration Page Example
===============================

NOTE: Actual fields are controlled in the CMS. Will come up with a method to programatically assign default fields. 

```
<?php

class Event extends FlexiForm
{
    protected $flexiform_tab = 'Root.Registration';
    private static $default_parent = 'EventsPage';

    private static $db = array(
        'Date' => 'SS_Datetime',
        'Timezone' => "Enum(array('EST', 'CST', 'PST', 'MST'))",
        'AllowRegistration' => 'Boolean',
        'RegistrationCutoff' => 'TinyInt'
    );

    private static $summary_fields = array(
        'Title' => 'Event',
        'DateTime' => 'Date/Time',
        'AllowRegistration.Nice' => 'Allow Registration',
    );

    private static $default_sort = "Date desc";


    public function populateDefaults()
    {
        $this->Date = date('Y-m-d 08:00:00');
        $this->Timezone = 'EST';
        $this->AllowRegistration = false;

        return parent::populateDefaults();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = new DatetimeField('Date','Date');
        $field->setTimeField(new TimePickerField('Date[time]',""));
        $field->getDateField()->setConfig('showcalendar', 1);
        $field->getTimeField()->setConfig('timeformat', 'hh:mm a');
        $field->getTimeField()->setTimePickerConfig('showPeriod',1);


        $fields->addFieldsToTab('Root.Main',array(
            $field,
            new DropdownField('Timezone','Timezone',$this->dbObject('Timezone')->enumValues()),
        ),'Content');


        // Registration Form
        ////////////////////

        // place fields above FlexiForm
        $fields->addFieldsToTab($this->flexiform_tab,array(
            new CheckboxField('AllowRegistration'),
            $cutoff = new DropdownField('RegistrationCutoff','Registration Cutoff'),
        ),'FlexiForm');

        $cutoff->setSource(array(
            0 => 'None',
            1 => '1 Day',
            2 => '2 Days',
            3 => '3 Days',
            4 => '4 Days',
            5 => '5 Days',
            6 => '6 Days',
            7 => '1 Week',
            14 => '2 Weeks'
        ));

        $cutoff->description = 'Days before Event to stop taking registrations';

        return $fields;
    }

    public function getDateTime() {
        return $this->dbObject('Date')->format('m/d/Y g:i a') . ' ' . $this->Timezone;
    }
}

class Event_Controller extends FlexiForm_Controller
{
}
```
  
  
  
  
