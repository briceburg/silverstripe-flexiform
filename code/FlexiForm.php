<?php

class FlexiForm extends Page
{

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

    /**
     * An array of allowed FlexiFormField Types for this form.
     *
     * @var Array
     */
    protected $allowed_flexi_types = array(
        'FlexiFormTextField',
        'FlexiFormEmailField',
        'FlexiFormDropdownField',
        'FlexiFormCheckboxField',
        'FlexiFormRadioField',
        'FlexiFormCheckboxSetField'
    );

    /**
     * An array of field definitions that are automatically added to this
     * form when it is first created. See documentation for field definitions.
     *
     * @var Array
     */
    protected $default_flexi_fields = array();

    public function canCreate($member = null)
    {
        return (singleton('Page')->canCreate($member) && $this->stat('can_create'));
    }

    public function canDelete($member = null)
    {
        return (singleton('Page')->canDelete($member) && $this->stat('can_delete'));
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

            $fields->addFieldToTab($this->getFlexiFormTab(),
                new GridField('FlexiForm', 'Form Fields', $this->FlexiFields(), $config));
        } else {
            $fields->addFieldToTab($this->getFlexiFormTab(),
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

    public function getFlexiFormTab()
    {
        return $this->stat('form_tab');
    }

    public function setFlexiFormTab($tab_name)
    {
        return $this->set_stat('form_tab');
    }

    /**
     * Get the FieldList for this form
     *
     * @return FieldList
     */
    public function getFrontEndFlexiFormFields()
    {
        return new FieldList($this->FlexiFields()->toArray());
    }

    /**
     * Get a list of fields associated with this form that match type passed.
     *
     * @param string $flexi_type The class name of a FlexiFormField type
     * @throws Exception if the type is not registered with this form
     *
     * @return ArrayList
     */
    public function getFlexiFieldsByType($flexi_type)
    {
        if (! in_array($flexi_type, $this->getAllowedFlexiTypes())) {
            throw new Exception("The $flexi_type type is not allowed on this form");
        }
        return new ArrayList(
            $this->FlexiFields()
                ->Filter('ClassName', $flexi_type)
                ->toArray());
    }

    /**
     * Get a list of fields associated with this form that match name passed.
     *
     * @param string $flexi_type The class name of a FlexiFormField type
     *
     * @return ArrayList
     */
    public function getFlexiFieldsByName($field_name)
    {
        return $this->FlexiFields()->Filter('Name', $field_name);
    }

    public function onAfterWrite()
    {
        // if this is a newly created form, prepopulate fields
        if ($this->isChanged('ID')) {

            $fields = $this->FlexiFields();
            foreach ($this->getDefaultFlexiFields() as $field_type => $definition) {

                if (is_string($definition)) {

                    // lookup field name, prioritizing Readonly fields
                    if (! $field = FlexiFormField::get()->sort('Readonly', 'DESC')
                        ->filter(
                        array(
                            'FieldName' => $definition,
                            'ClassName' => $field_type
                        ))
                        ->first()) {
                        throw new ValidationException("No $field_type field found named `$definition`");
                    }
                } elseif (is_array($definition)) {
                    $field = FlexiFormUtil::CreateFlexiField($field_type, $definition);
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
