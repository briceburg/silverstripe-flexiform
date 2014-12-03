<?php

class GridFieldConfig_FlexiFormSubmissionValues extends GridFieldConfig
{

    public function __construct()
    {
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldDeleteAction(false));

        // Inline Editing
        // ///////////////
        $component = $this->getComponentByType('GridFieldEditableColumns');
        $component->setDisplayFields(
            array(
                'Name' => array(
                    'title' => 'Field Name',
                    'field' => 'TextField'
                ),
                'Value' => array(
                    'title' => 'Response Value',
                    'callback' => function ($record, $column_name, $grid)
                    {
                        return new ReadonlyField($column_name,'XXX','XXX');
                    }
                )

            ));
    }
}

