<?php

class FlexiFormEmailHandler extends FlexiFormBasicHandler 
{

    private static $handler_label = 'Email Notification Handler';

    private static $handler_description = 'Submissions are stored. Email notification is sent. Presents a thank you message.';

    private static $handler_settings = array(
        'NotificationEmails' => 'FlexiFormTextHandlerSetting'
    );

    private static $db = array(
        'NotificationEmails' => 'Text'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = $fields->dataFieldByName('NotificationEmails');
        $field->setDescription($this->getEmailFieldDescription());

        $fields->addFieldToTab('Root.Main', $field,'SuccessMessage');

        return $fields;
    }

    public function updateCMSFlexiTabs(TabSet $fields, TabSet $settings_tab, $flexi)
    {
        parent::updateCMSFlexiTabs($fields, $settings_tab, $flexi);

        $settings_tab->fieldByName($this->getSettingFieldName('NotificationEmails'))->setDescription($this->getEmailFieldDescription());
    }

    private function getEmailFieldDescription(){
      return 'Optional, comma separated list of email addresses that receive submission notifications.';
    }

    // Submission Handling
    //////////////////////
    public function onSubmit(Array $data, FlexiForm $form, SS_HTTPRequest $request, DataObject $flexi)
    {
      if(parent::onSubmit($data, $form, $request, $flexi)) {
        // extraplate emails and send notifications
        $emails = $flexi->FlexiFormSetting('NotificationEmails')->getValue();
        if(!empty($emails)) {

          //$from = $flexi->FlexiFormSetting('NotificationFromAddress')->getValue() ?: null;
          $email = new Email(null, $emails, 'Submission from form: ' . $flexi->FlexiFormID(), $this->getEmailBody());

          $email->sendPlain();
        }

        return true;
      }

      return false;
    }

    // Utility Methods
    //////////////////
    protected function getEmailBody()
    {
        $body = "A visitor with the IP Address of submitted the following values: \r\n\n";

        foreach($this->submission->Values() as $value) {
          $body .= "[{$value->Name}] : {$value->Value} \r\n";
        }

        return $body;
    }
}
