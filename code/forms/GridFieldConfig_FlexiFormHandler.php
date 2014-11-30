<?php

class GridFieldConfig_FlexiFormHandler extends GridFieldConfig
{

    public function __construct($allowed_types = array())
    {
        $this->addComponent(new GridFieldAddNewMultiClass());
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldEditButton());
        $this->addComponent(new GridFieldDeleteAction(false));
        $this->addComponent(new GridFieldDetailForm());

        // Multi-Class Add Button
        /////////////////////////
        $classes = array();

        if (empty($allowed_types)) {
            $allowed_types = SS_ClassLoader::instance()->getManifest()->getDescendantsOf('FlexiFormHandler');
        }

        foreach ($allowed_types as $className) {
            $class = singleton($className);
            $classes[$className] = "{$class->Label()}";
        }

        $component = $this->getComponentByType('GridFieldAddNewMultiClass');
        $component->setClasses($classes);

        // Inline Editing
        // ///////////////
        $component = $this->getComponentByType('GridFieldDataColumns');
        $component->setDisplayFields(
            array(
                'Selected' => array(
                    'title' => 'Selected',
                    'callback' => function ($record, $column_name, $grid)
                    {
                        return new CheckboxField_Readonly($column_name);
                    }
                ),
                'HandlerName' => array(
                    'title' => 'Name',
                    'field' => 'ReadonlyField'
                ),
                'Label' => array(
                    'title' => 'Type',
                    'field' => 'ReadonlyField'
                ),
                'DescriptionPreview' => array(
                    'title' => 'Description',
                    'field' => 'ReadonlyField'
                ),
                'FormCount' => array(
                    'title' => 'Form Count',
                    'field' => 'ReadonlyField'
                )
            ));
    }
}

