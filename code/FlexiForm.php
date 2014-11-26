<?php

class FlexiForm extends Page
{

    protected $flexiform_tab = 'Root.Form';

    private static $db = array();



    private static $has_many = array(
        'Submissions'
    );

    private static $many_many = array(
        'Fields' => 'FlexiFormField'
    );

    private static $many_many_extraFields = array(
        'Fields' => array(
            'Name' => 'Varchar',
            'Prompt' => 'Varchar',
            'DefaultValue' => 'Varchar',
            'Required' => 'Boolean',
            'SortOrder' => 'Int'
        )
    );

    protected $allowed_field_types = array(
        'FlexiFormTextField',
        'FlexiFormEmailField',
        'FlexiFormDropdownField',
        'FlexiFormCheckboxField',
        'FlexiFormRadioField',
        'FlexiFormCheckboxSetField'
    );

    public function populateDefaults()
    {
        return parent::populateDefaults();
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->ID) {
            $fields->addFieldToTab($this->flexiform_tab, $this->getFlexiGridField());
        } else {
            $fields->addFieldToTab($this->flexiform_tab, new LiteralField('FlexiForm', '<p>Please save before editing the form.</p>'));
        }

        return $fields;
    }

    public function validate() {
        $result = parent::validate();

        $names = array();
        if($result->valid()) {
            foreach($this->Fields() as $field) {

                if(empty($field->Name)) {
                    $result->error("Field names cannot be blank. Encountered a blank {$field->Label()} field.");
                    break;
                }


                if(in_array($field->Name,$names)) {
                    $result->error("Field Names must be unique per form. {$field->Name} was encountered twice.");
                    break;
                }
                $names[] = $field->Name;

                $field_options = $field->Options();
                if(!empty($field->DefaultValue) && $field_options->exists()) {
                    if(!in_array($field->DefaultValue,$field_options->column('Value'))) {
                        $result->error("The default value of {$field->Name} must exist as an option value");
                        break;
                    }
                }
            }
        }
        return $result;
    }

    public function addFieldType($className)
    {
        if (! class_exists($className)) {
            throw new Exception("FlexiFormField class $className not found");
        }

        if (! singleton($className)->is_a('FlexiFormField')) {
            throw new Exception("$className is not a FlexiFormField");
        }

        $this->allowed_field_types[] = $className;
    }

    public function setFieldTypes(Array $types)
    {
        return $this->allowed_field_types = $types;
    }

    public function getFormFieldList()
    {
        $fields = new FieldList();

        foreach ($this->Fields() as $field) {
            $fields->push($field);
        }
    }

    public function getFlexiGridField()
    {
        $config = new GridFieldConfig_FlexiForm();

        // Sort Order
        /////////////
        $config->addComponent(new GridFieldOrderableRows('SortOrder'));


        // Multi-Class Add Button
        /////////////////////////
        $classes = array();
        foreach($this->allowed_field_types as $className) {
            $class = singleton($className);
            $classes[$className] = "{$class->Label()} Field";
        }

        $component = $config->getComponentByType('GridFieldAddNewMultiClass');
        $component->setClasses($classes);
        $component->setTitle('Add Field');

        // Inline Editing
        /////////////////
        $component = $config->getComponentByType('GridFieldEditableColumns');
        $component->setDisplayFields(array(
            'Label' => array('title' => 'Type', 'field' => 'ReadonlyField'),
            'Name' => array('title' => 'Name', 'field' => 'TextField'),
            'Prompt' => array('title' => 'Prompt', 'field' => 'TextField'),
            'DefaultValue' => array('title' => 'Default Value', 'field' => 'TextField'),
            'OptionsPreview' => array('title' => 'Options', 'field' => 'ReadonlyField'),
            'Required' => array('title' => 'Required', 'field' => 'CheckboxField'),
        ));


        return new GridField('FlexiForm', 'Form Fields', $this->Fields(), $config);
    }


}

class FlexiForm_Controller extends Page_Controller
{

}
