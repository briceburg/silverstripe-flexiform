<?php

class GridFieldConfig_FlexiFormOption extends GridFieldConfig
{

    public function __construct()
    {
        $this->addComponent(new GridFieldButtonRow('before'));
        $this->addComponent(new GridFieldAddNewInlineButton('buttons-before-left'));
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldDeleteAction(false));

        $component = $this->getComponentByType('GridFieldAddNewInlineButton');
        $component->setTitle('Add Option');

        $component = $this->getComponentByType('GridFieldEditableColumns');
        $component->setDisplayFields(array(
            'Value' => array(
                'title' => 'Value (required)',
                'field' => 'TextField'
            ),
            'Label' => array(
                'title' => 'Label (optional, defaults to Value)',
                'field' => 'TextField'
            )
        ));

        // Sort Order
        // ///////////
        $this->addComponent(new GridFieldOrderableRows('SortOrder'));

    }
}

