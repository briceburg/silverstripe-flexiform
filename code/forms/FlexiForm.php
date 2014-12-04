<?php

class FlexiForm extends Form
{
    private static $flexiform_post_action = 'FlexiFormPost';

    public function __construct(Controller $controller, $name, FieldList $fields, FieldList $actions, $validator = null)
    {

        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->enableSecurityToken();
        $this->setFormMethod('POST', true);
        $this->loadDataFrom($controller->getRequest()
            ->postVars());

        // remember the origin action that instantiated flexiform
        $this->setFlexiFormOrigin();
    }

    protected function handleAction($request, $action) {
        if($action != 'httpSubmission' && $flexi = FlexiFormUtil::GetFlexiByIdentifier($action)) {
            $action = 'httpSubmission';
        }

        return parent::handleAction($request, $action);
    }

    public function hasAction($action) {
        return ($action != 'httpSubmission' && FlexiFormUtil::GetFlexiByIdentifier($action)) ? true : parent::hasAction($action);
    }


    public function checkAccessAction($action) {
        return ($action != 'httpSubmission' && FlexiFormUtil::GetFlexiByIdentifier($action)) ? true : parent::checkAccessAction($action);
    }


    public function setFlexiFormOrigin()
    {
        // @TODO can we use a mock the result of a mock request for a URL instead?
        // @TODO: Mask origin in session / token since we're using session anyways via SecurityToken...?
        if (! $this->Fields()->fieldByName('flexiform_origin')) {
            $this->Fields()->push($origin = new HiddenField('flexiform_origin'));

            // @TODO store URL params for mock request as well
            $origin->setValue($this->controller->getAction());
        }
    }

    public function getFlexiFormOrigin()
    {
        if (! $field = $this->Fields()->fieldByName('flexiform_origin')) {
            return 'index';
        }

        return $field->Value();
    }


    public function renderFlexiFormOrigin(){
        $action = $this->getFlexiFormOrigin();

        // @TODO set origin requestvars
        //$origin_request = new SS_HTTPRequest('GET', $origin_url);
        if ($this->controller->checkAccessAction($action)) {

            // routine taken from controller handleAction,
            //  we cannot call b/c it's protected


            if($this->controller->hasMethod($action)) {
                $result = $this->controller->$action($request);
                return (is_array($result)) ? $this->controller->getViewer($action)->process($this->controller->customise($result)) : $result;
            }

            return $this->controller->getViewer($action)->process($this->controller);
        }

    }


}