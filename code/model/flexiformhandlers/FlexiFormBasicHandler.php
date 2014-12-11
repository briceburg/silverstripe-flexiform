<?php

class FlexiFormBasicHandler extends FlexiFormHandler
{

    private static $handler_label = 'Basic Handler';

    private static $handler_description = 'Submissions are stored. Presents a success you message upon completion.';

    private static $handler_settings = array(
        'SuccessMessage' => 'FlexiFormHTMLTextHandlerSetting'
    );

    private static $db = array(
        'SuccessMessage' => 'HTMLText'
    );

    public function populateDefaults()
    {
        $this->SuccessMessage = '<p>' . _t("FlexiFormBasicHandler.DEFAULT_SUCCESS_MESSAGE", "Thank You.") .
             '</p>';

        return parent::populateDefaults();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //$fields->addFieldToTab('Root.Main', new HtmlEditorField('SuccessMessage.Value','GGG'));



        return $fields;
    }

    public function updateCMSFlexiTabs(TabSet $fields, $flexi)
    {
        parent::updateCMSFlexiTabs($fields, $flexi);

        // Settings
        ///////////

        /*
        $field = new HtmlEditorField('FlexiFormHandlerSetting[SuccessMessage]', 'Success Message',
            $this->SuccessMessage);
        $fields->insertAfter($field, 'FlexiFormHandlerSettings');
        */

        // Submissions
        //////////////
        $submissions_tab = new Tab('Submissions');
        $fields->insertBefore($submissions_tab, 'Settings');

        $submissions_tab->push(
            new GridField('FlexiFormSubmissions', 'Submissions', $this->getFormSubmissions($flexi),
                new GridFieldConfig_FlexiFormSubmission($flexi)));
    }

    public function getFormSubmissions($flexi)
    {
        return FlexiFormSubmission::get()->filter(
            array(
                'FlexiFormID' => $flexi->ID,
                'FlexiFormClass' => $flexi->class
            ));
    }

    // Submission Handling
    //////////////////////
    public function onSubmit(Array $data, FlexiForm $form, SS_HTTPRequest $request, DataObject $flexi)
    {
        // persist the submission
        $this->saveSubmission($data, $flexi);

        return true;
    }

    public function onSuccess(FlexiForm $form, DataObject $flexi)
    {
        return $this->SuccessMessage;
    }

    // Utility Methods
    //////////////////
    protected function saveSubmission($data, $flexi)
    {
        $submission = new FlexiFormSubmission();
        $submission->FlexiFormID = $flexi->ID;
        $submission->FlexiFormClass = $flexi->class;
        $submission->write();

        $values = $submission->Values();
        foreach ($flexi->FlexiFormFields() as $field) {
            if (isset($data[$field->SafeName()])) {
                $value = new FlexiFormSubmissionValue();
                $value->FormFieldID = $field->ID;
                $value->FormFieldClass = $field->class;
                $value->Name = $field->getName();
                $value->Value = (is_array($data[$field->SafeName()])) ? implode(",", $data[$field->SafeName()]) : $data[$field->SafeName()];

                $values->add($value);
            }
        }
    }
}