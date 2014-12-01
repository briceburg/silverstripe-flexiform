<?php

class FlexiFormBasicHandler extends FlexiFormHandler
{

    protected $handler_label = 'Basic Handler';

    protected $handler_description = 'Submissions are notified and stored. Presents a thank you message.';

    public function updateCMSFlexiTabs(TabSet $fields, $form)
    {
        // Submissions
        //////////////
        $submissions_tab = new Tab('Submissions');
        $fields->insertBefore($submissions_tab, 'Settings');

        $submissions_tab->push(
            new GridField('FlexiFormSubmissions', 'Submissions', $this->getFormSubmissions($form),
                new GridFieldConfig_FlexiFormSubmission()));

        return parent::updateCMSFlexiTabs($fields, $form);
    }

    public function getFormSubmissions($form)
    {
        return FlexiFormSubmission::get()->filter(
            array(
                'FormID' => $form->ID,
                'FormClass' => $form->class
            ));
    }
}