<?php

class GridFieldConfig_FlexiForm extends GridFieldConfig
{

    public static function include_requirements()
    {
        $moduleDir = self::get_module_dir();
        Requirements::css($moduleDir . '/css/flexiforms.css');
    }

    public static function get_module_dir()
    {
        return basename(dirname(__DIR__));
    }

    public function __construct()
    {
        $this->addComponent(new GridFieldButtonRow('before'));
        $this->addComponent(new GridFieldAddNewMultiClass('buttons-before-left'));
        $this->addComponent(new GridFieldAddExistingAutocompleter('buttons-before-right'));
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldEditButton());
        $this->addComponent(new GridFieldDeleteAction(true));
        $this->addComponent(new GridFieldDetailForm());

        $component = $this->getComponentByType('GridFieldDetailForm');
        $component->setValidator(new RequiredFields(array('FieldName')));

        self::include_requirements();
    }
}

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

    }
}

