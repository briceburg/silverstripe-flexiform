<?php

class FlexiFormControllerExtension extends Extension
{

    protected $flexiform_object = null;

    protected $flexiform_posted = false;

    private static $allowed_actions = array(
        'FlexiFormPost'
    );

    public function FlexiForm($identifier = null)
    {
        $flexi = $this->getFlexiFormObject($identifier);
        $handler = $flexi->FlexiFormHandler();

        $fields = $flexi->getFlexiFormFrontEndFields();
        $actions = new FieldList(
            FormAction::create('FlexiFormPostHandler')->setTitle($handler->SubmitButtonText));
        $validator = $handler->getFrontEndFormValidator($flexi);

        $form_class = $flexi->stat('flexiform_form_class');
        $form_name = Config::inst()->get($form_class, 'flexiform_post_action');
        $form = new $form_class($this->owner, $form_name, $fields, $actions, $validator);

        // identify the form in post
        $form->setFormAction(
            Controller::join_links($this->owner->Link(), $form_name, $flexi->FlexiFormID()));


        // if the form is successfull and the onSuccess handler returns
        //  a non boolean value, return its value. else return  the form.
        if ($this->FlexiFormPosted($flexi->FlexiFormID()) && $success = $handler->onSuccess($form, $flexi)) {
            if (! is_bool($success)) {
                return $success;
            }
        }

        return $form;
    }

    public function FlexiFormPost($request)
    {
        return $this->FlexiForm($request->param('ID'));
    }

    public function FlexiFormPostHandler($data, $form, $request)
    {
        $flexi = $this->owner->getFlexiFormObject($request->param('ID'));
        $handler = $flexi->FlexiFormHandler();

        if ($handler->onSubmit($data, $form, $request, $flexi)) {
            $this->flexiform_posted = $flexi->FlexiFormID();

            // mark submitted, reset token to prevent re-submissions
            $form->markSubmitted();

            $action = $form->getFlexiFormOrigin();
            if ($this->owner->checkAccessAction($action)) {
                return $this->owner->getViewer($action)->process($this->owner);
            }
        }

        // else, form is not valid (handler onSubmit returned falsey)
        $this->owner->redirectBack();
    }

    public function FlexiFormPosted($identifier = null)
    {
        return ($identifier) ? ($this->flexiform_posted == $identifier) : (bool) $this->flexiform_posted;
    }

    public function getFlexiFormObject($identifier = null)
    {
        // by default, we assume the flexi form is the controller's data record.
        // You may provide an identifier or override as neccessary.
        $flexi = ($this->flexiform_object) ?  : $this->owner->data();

        if ($identifier && ! $flexi = FlexiFormUtil::GetFlexiByIdentifier($identifier)) {
            throw new Exception("No Flexi with identifier `$identifier` found");
        }

        if (! $flexi->hasExtension('FlexiFormExtension')) {
            throw new Exception('Flexi Form not found. Try passing an Identifier, or setting.');
        }
        return $flexi;
    }

    public function setFlexiFormObject($flexi)
    {
        return $this->flexiform_object = $flexi;
    }
}