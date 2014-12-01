<?php

class FlexiFormControllerExtension extends Extension
{

    private static $allowed_actions = array(
        'FlexiForm'
    );

    public function FlexiForm()
    {
        $flexi = $this->getFlexiFormObject();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            throw new Exception('FlexiForm is not availabe on my dataRecord');
        }

        $handler = $flexi->FlexiFormHandler();

        $fields = $flexi->getFlexiFormFrontEndFields();
        $actions = new FieldList(FormAction::create('FlexiFormPost')->setTitle($handler->SubmitButtonText));

        $form = new Form($this->owner, "FlexiForm", $fields, $actions);
        $form->setFormMethod('POST', true);
        $form->loadDataFrom($this->owner->getRequest()->postVars());

        return $form;
    }

    public function FlexiFormPost($data, $form)
    {
        $flexi = $this->getFlexiFormObject();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            return $this->owner->httpError(403, 'FlexiForm is not availabe on my dataRecord');
        }



        die();
    }

    // by default, we assume the flexi form is the controller's data record.
    //  if it is another object, override to provide it.
    public function getFlexiFormObject() {
        return $this->owner->data();
    }
}