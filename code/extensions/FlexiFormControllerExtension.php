<?php

class FlexiFormControllerExtension extends Extension
{

    protected $flexiform_object = null;

    protected $flexiform_posted = false;

    private static $allowed_actions = array(
        'FlexiForm'
    );

    public function FlexiForm()
    {
        $flexi = $this->owner->getFlexiFormObject();
        $handler = $flexi->FlexiFormHandler();

        if ($this->FlexiFormPosted() && $success = $handler->onSuccess($flexi)) {
            if (! is_bool($success)) {
                return $success;
            }
        }

        $fields = $flexi->getFlexiFormFrontEndFields();
        $actions = new FieldList(FormAction::create('FlexiFormPost')->setTitle($handler->SubmitButtonText));
        $validator = $handler->getFrontEndFormValidator($flexi);

        return new FlexiForm($this->owner, "FlexiForm", $fields, $actions, $validator);
    }

    public function FlexiFormPost($data, $form)
    {
        $flexi = $this->owner->getFlexiFormObject();
        $handler = $flexi->FlexiFormHandler();

        if ($handler->onSubmit($data, $form, $flexi)) {
            $this->owner->redirect($form->getPostLink($flexi, $handler));
        }

        return $this->owner->redirectBack();
    }

    public function FlexiFormPosted()
    {
        return $this->flexiform_posted;
    }

    // by default, we assume the flexi form is the controller's data record.
    //  if it is another object, override to provide it.
    public function getFlexiFormObject()
    {
        $flexi = ($this->flexiform_object) ?  : $this->owner->data();

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            throw new Exception('FlexiForm is not availabe on my dataRecord');
        }
        return $flexi;
    }

    public function setFlexiFormObject($flexi)
    {
        return $this->flexiform_object = $flexi;
    }

    public function setFlexiFormPosted($boolean)
    {
        $this->flexiform_posted = $boolean;
    }
}