<?php

class FlexiForm extends Form
{

    private static $url_handlers = array(
        'post//$ID/$OtherID' => 'post'
    );

    private static $allowed_actions = array(
        'post'
    );

    private static $flexiform_post_action = 'post';

    public function __construct($controller, $name, FieldList $fields, FieldList $actions, $validator = null)
    {
        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->enableSecurityToken();
        $this->setFormMethod('POST', true);
        $this->loadDataFrom($controller->getRequest()
            ->postVars());

        // remember the origin action that instantiated flexiform
        $this->setFlexiFormOrigin();
    }

    public function post($request)
    {
        // TODO: do not allow double posts (e.g. refresh being clicked)

        $action = $request->getVar('flexiform_origin');
        $token = $request->getVar('flexiform_token');

        if (! $this->checkAccessAction($action) || $token != $this->getSecurityToken()->getValue()) {
            //return $this->httpError(403, "Unauthorized Origin.");
            // unauthorized origin, quitely redirect back
            return $this->controller->redirectBack();
        }
        $this->controller->setFlexiFormPosted(true);
        return $this->controller->handleAction($request, $action);
    }

    public function getPostLink($flexi, $handler)
    {
        return $this->controller->join_links($this->controller->Link($this->controller->getAction()),
            $this->stat('flexiform_post_action'), $flexi->getFlexiFormNickname(),
            '?flexiform_origin=' . $this->getFlexiFormOrigin(), '?flexiform_token=' . $this->getSecurityToken()->getValue());
    }

    protected function setFlexiFormOrigin()
    {
        // @TODO: Mask origin in session / token since we're using session anyways via SecurityToken...?
        if (! $this->Fields()->fieldByName('flexiform_origin')) {
            $this->Fields()->push($origin = new HiddenField('flexiform_origin'));
            $origin->setValue($this->controller->getAction());
        }
    }

    protected function getFlexiFormOrigin()
    {
        if (! $field = $this->Fields()->fieldByName('flexiform_origin')) {
            return 'index';
        }

        return $field->Value();
    }
}