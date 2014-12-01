<?php

class GridFieldConfig_FlexiFormSubmission extends GridFieldConfig
{

    public function __construct($allowed_types = array())
    {
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldDataColumns());
        $this->addComponent(new GridFieldViewButton());
        $this->addComponent(new GridFieldDeleteAction(false));
        $this->addComponent(new GridFieldDetailForm());

    }
}

