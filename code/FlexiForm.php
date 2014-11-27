<?php

class FlexiForm extends Page
{

    protected $flexiform_tab = 'Root.Form';

    /**
     * An array of fields to prepopulate this newly created form with.
     *
     * If the value is a string, the field whose Name matches the value will
     * be linked to the form. This combines well with _System Fields_, as their
     * name cannot change.
     *
     * If the value is an array, a field will be created from the components
     * of the array. Name and Type are required. If supplying Options,
     * use Value as array Key and Label as array Value .
     *
     * e.g.
     *

     protected $default_flexi_fields = array(
        'Email',   // will link the existing field with Name "Email"
        array(     // creates a new field to spec
            'Name' => 'Author',
            'Type' => 'FlexiFormDropdownField',
            'EmptyString' => 'Select your favorite Author',
            'Options' => array(
                'Balzac' => 'Honoré de Balzac',
                'Dumas' => 'Alexandre Dumas',
                'Flaubert' => 'Gustave Flaubert',
                'Hugo' => 'Victor Hugo',
                'Verne' => 'Jules Verne',
                'Voltaire' => 'Voltaire')
        )
     );

     *
     * @var Array $flexi_field_definitions
     */
    protected $default_flexi_fields = array();

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

    public function getDefaultFlexiFields()
    {
        return $this->default_flexi_fields;
    }

    public function setDefaultFlexiFields(Array $flexi_field_definitions)
    {
        return $this->default_flexi_fields = $flexi_field_definitions;
    }

    public function getFrontEndFlexiFormFields()
    {
        $fields = new FieldList();

        foreach ($this->FlexiFields() as $field) {
            $fields->push($field);
        }
    }

    public function onAfterWrite()
    {
        if ($this->isChanged('ID')) {
            // this is a newly created form, prepopulate fields


            $fields = $this->FlexiFields();
            foreach ($this->getDefaultFlexiFields() as $flexi_field_definition) {

                if (is_string($flexi_field_definition)) {

                    if (! $field = FlexiFormField::get()->filter('FieldName', $flexi_field_definition)->first()) {
                        throw new ValidationException("No field found by name '$flexi_field_definition'");
                    }
                } elseif (is_array($flexi_field_definition)) {
                    $field = FlexiFormUtil::CreateFlexiField($flexi_field_definition);
                } else {
                    throw new ValidationException('Unknown Field Definition Encountered');
                }

                $fields->add($field);
            }
        }

        return parent::onAfterWrite();
    }
}

class FlexiForm_Controller extends Page_Controller
{
}
