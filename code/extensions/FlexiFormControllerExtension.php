<?php

class FlexiFormControllerExtension extends Extension
{
    protected $session_posted_key = 'flexiform_posted';

    private static $allowed_actions = array(
        'FlexiForm'
    );

    public function FlexiForm()
    {
        $flexi = $this->getFlexiFormObject();
        $handler = $flexi->FlexiFormHandler();

        if($this->FlexiFormPosted()) {
            Session::clear($this->session_posted_key);
            if($success = $handler->onSuccess($flexi)) {
                return $success;
            }
        }

        $fields = $flexi->getFlexiFormFrontEndFields();
        $actions = new FieldList(FormAction::create('FlexiFormPost')->setTitle($handler->SubmitButtonText));
        $validator = $handler->getFrontEndFormValidator($flexi);

        $form = new Form($this->owner, "FlexiForm", $fields, $actions, $validator);
        $form->setFormMethod('POST', true);
        $form->loadDataFrom($this->owner->getRequest()->postVars());

        return $form;
    }

    public function FlexiFormPost($data, $form)
    {
        $flexi = $this->getFlexiFormObject();
        $handler = $flexi->FlexiFormHandler();

        if($handler->onSubmit($data, $form, $flexi)) {
            Session::set($this->session_posted_key, true);
        }
        return $this->owner->redirectBack();

    }

    public function FlexiFormPosted(){
        return (bool) Session::get('flexiform_posted');
    }

    // by default, we assume the flexi form is the controller's data record.
    //  if it is another object, override to provide it.
    public function getFlexiFormObject() {
        $flexi = $this->owner->data();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            throw new Exception('FlexiForm is not availabe on my dataRecord');
        }
        return $flexi;
    }
}