<?php

class FlexiForm extends Page
{

    protected $flexiform_tab = 'Root.Form';

    private static $db = array();

    private static $has_many = array(
        'Submissions'
    );

    private static $many_many = array(
        'FlexiFields' => 'FlexiFormField'
    );

    private static $many_many_extraFields = array(
        'FlexiFields' => array(
            'Name' => 'Varchar',
            'Prompt' => 'Varchar',
            'DefaultValue' => 'Varchar',
            'Required' => 'Boolean',
            'SortOrder' => 'Int'
        )
    );

    protected $allowed_flexi_types = array(
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

            $config = new GridFieldConfig_FlexiForm();

            // Multi-Class Add Button
            // ///////////////////////
            $classes = array();
            foreach ($this->getAllowedFlexiTypes() as $className) {
                $class = singleton($className);
                $classes[$className] = "{$class->Label()} Field";
            }

            $component = $config->getComponentByType('GridFieldAddNewMultiClass');
            $component->setClasses($classes);
            $component->setTitle('Create New Field');

            $fields->addFieldToTab($this->flexiform_tab,
                new GridField('FlexiForm', 'Form Fields', $this->FlexiFields(), $config));
        } else {
            $fields->addFieldToTab($this->flexiform_tab,
                new LiteralField('FlexiForm', '<p>Please save before editing the form.</p>'));
        }

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        $names = array();
        if ($result->valid()) {
            foreach ($this->FlexiFields() as $field) {

                if (empty($field->Name)) {
                    $result->error("Field names cannot be blank. Encountered a blank {$field->Label()} field.");
                    break;
                }

                if (in_array($field->Name, $names)) {
                    $result->error(
                        "Field Names must be unique per form. {$field->Name} was encountered twice.");
                    break;
                } else {
                    $names[] = $field->Name;
                }

                $default_value = $field->DefaultValue;
                if (! empty($default_value) && $field->Options()->exists() &&
                     ! in_array($default_value, $field->Options()->column('Value'))) {
                    $result->error("The default value of {$field->getName()} must exist as an option value");
                    break;
                }
            }
        }
        return $result;
    }

    public function getAllowedFlexiTypes()
    {
        return $this->allowed_flexi_types;
    }

    public function addAllowedFlexiType($className)
    {
        if (! class_exists($className)) {
            throw new Exception("FlexiFormField class $className not found");
        }

        if (! singleton($className)->is_a('FlexiFormField')) {
            throw new Exception("$className is not a FlexiFormField");
        }

        $this->allowed_flexi_types[] = $className;
    }

    public function setAllowedFlexiTypes(Array $classNames)
    {
        return $this->allowed_flexi_types = $classNames;
    }

    public function getFrontEndFlexiFormFields()
    {
        $fields = new FieldList();

        foreach ($this->FlexiFields() as $field) {
            $fields->push($field);
        }
    }
}

class FlexiForm_Controller extends Page_Controller
{
}
