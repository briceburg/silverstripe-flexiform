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


        $component = $this->getComponentByType('GridFieldAddExistingAutocompleter');
        $component->setPlaceholderText('Search Existing Fields by Name');

        // Validation
        // ///////////
        $component = $this->getComponentByType('GridFieldDetailForm');
        $component->setValidator(new RequiredFields(array(
            'FieldName'
        )));

        // Sort Order
        // ///////////
        $this->addComponent(new GridFieldOrderableRows('SortOrder'));

        // Inline Editing
        // ///////////////
        $component = $this->getComponentByType('GridFieldEditableColumns');
        $component->setDisplayFields(array(
            'Label' => array(
                'title' => 'Type',
                'field' => 'ReadonlyField'
            ),
            'Name' => array(
                'title' => 'Name',
                'field' => 'TextField'
            ),
            'Prompt' => array(
                'title' => 'Prompt',
                'field' => 'TextField'
            ),
            'DefaultValue' => array(
                'title' => 'Default Value',
                'callback' => function ($record, $column_name, $grid)
                {
                    return ($record->hasMethod('getDefaultValueFormField')) ?
                        $record->getDefaultValueFormField('DefaultValue') : new TextField($column_name);
                }
            ),
            'OptionsPreview' => array(
                'title' => 'Options',
                'field' => 'ReadonlyField'
            ),
            'Required' => array(
                'title' => 'Required',
                'field' => 'CheckboxField'
            )
        ));

        // CSS improvements
        // /////////////////

        self::include_requirements();
    }
}

