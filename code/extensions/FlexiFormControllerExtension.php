<?php

class FlexiFormControllerExtension extends Extension
{

    public function FlexiForm()
    {
        $flexi = $this->owner->data();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            throw new Exception('FlexiForm is not availabe on my dataRecord');
        }

        $handler = $flexi->FlexiFormHandler();

        $fields = $flexi->getFlexiFormFrontEndFields();
        $actions = new FieldList(FormAction::create('FlexiFormPost')->setTitle($handler->SubmitButtonText));

        $form = new Form($this->owner, "FlexiForm{$flexi->ID}", $fields, $actions);
        $form->setFormMethod('POST', true);
        $form->loadDataFrom($this->owner->getRequest()->postVars());

        return $form;
    }

    public function FlexiFormPost()
    {
        $flexi = $this->owner->data();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            return $this->owner->httpError(403, 'FlexiForm is not availabe on my dataRecord');
        }

        die();
    }
}