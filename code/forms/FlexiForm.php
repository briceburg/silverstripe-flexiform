<?php

class FlexiForm extends Form
{

    private static $flexiform_post_action = 'FlexiFormPost';

    public function __construct(Controller $controller, $name, FieldList $fields, FieldList $actions,
        $validator = null)
    {
        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->enableSecurityToken();
        $this->setFormMethod('POST', true);
        $this->loadDataFrom($controller->getRequest()
            ->postVars());

        // remember the origin action that instantiated flexiform
        $this->setFlexiFormOrigin();
    }

    // allow posts with $Action//$ID/$OtherID on this form
    //////////////////////////////////////////////////////
    protected function handleAction($request, $action)
    {
        if ($action != 'httpSubmission' && $flexi = FlexiFormUtil::GetFlexiByIdentifier($action)) {
            $action = 'httpSubmission';
        }

        return parent::handleAction($request, $action);
    }

    public function hasAction($action)
    {
        return ($action != 'httpSubmission' && FlexiFormUtil::GetFlexiByIdentifier($action)) ? true : parent::hasAction(
            $action);
    }

    public function checkAccessAction($action)
    {
        return ($action != 'httpSubmission' && FlexiFormUtil::GetFlexiByIdentifier($action)) ? true : parent::checkAccessAction(
            $action);
    }

    // remember action that instantiated this form
    //////////////////////////////////////////////
    public function setFlexiFormOrigin()
    {
        // @TODO can we use a mock the result of a mock request for a URL instead?
        //   ..Needed in case action requires URL params to display form.
        $action = $this->controller->getAction();
        if($action != $this->stat('flexiform_post_action')) {
            Session::set("FormInfo.{$this->FormName()}.flexi_origin", $action);
        }
    }

    public function getFlexiFormOrigin()
    {
        $action = Session::get("FormInfo.{$this->FormName()}.flexi_origin");
        return ($action) ?: 'index';
    }

    // allow customization of CSRF/Re-submission messages
    /////////////////////////////////////////////////////
    public function markSubmitted()
    {
        // mark submitted, reset token to prevent re-submissions
        Session::set("FormInfo.{$this->FormName()}.flexi_submit", true);
        $this->getSecurityToken()->reset();
    }

    public function isSubmitted()
    {
        return (bool) Session::get("FormInfo.{$this->FormName()}.flexi_submit");
    }

    public function httpSubmission($request)
    {
        if ($this->isSubmitted() && ! $this->getSecurityToken()->checkRequest($request)) {

            $this->sessionMessage(_t("FlexiForm.RESUBMISSION_MESSAGE", "Your submission was received."),
                "warning");

            return $this->controller->redirectBack();
        }

        return parent::httpSubmission($request);
    }
}