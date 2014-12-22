<?php
//@TODO Validate Identifier / force aphanumeric_
class FlexiFormExtension extends DataExtension
{
    private static $flexiform_tab = 'Root.Form';

    private static $flexiform_insertBefore = null;

    private static $flexiform_addButton = 'Create New Field';

    private static $flexiform_form_class = 'FlexiForm';

    /**
     * Specify allowed FlexiFormField Types for this form. Empty to allow all.
     * @var Array
     */
    private static $flexiform_field_types = array();

    /**
     * An array of field definitions that are automatically added to newly
     * created forms. See documentation for field definitions.
     * @var Array
     */
    private static $flexiform_initial_fields = array();

    /**
     * The name of the default handler for this form.
     * @var String
     */
    private static $flexiform_default_handler_name = 'Default';

    private static $has_one = array(
        'FlexiFormConfig' => 'FlexiFormConfig'
    );

    private static $many_many = array(
        'FlexiFormFields' => 'FlexiFormField'
    );

    private static $many_many_extraFields = array(
        'FlexiFormFields' => array(
            'Name' => 'Varchar',
            'Prompt' => 'Varchar',
            'DefaultValue' => 'Varchar',
            'Required' => 'Boolean',
            'SortOrder' => 'Int'
        )
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('FlexiFormFields');
        $fields->removeByName('FlexiFormConfigID');

        if ($this->owner->exists()) {

            $fields_tab = new Tab('Fields');
            $settings_tab = new Tab('Settings');

            $fields->addFieldToTab($this->getFlexiFormTab(),
                $flexi_tabs = new TabSet('flexiform', $fields_tab, $settings_tab),
                $this->getFlexiFormInsertBefore());

            // Fields
            /////////
            $field_types = array();
            foreach ($this->getFlexiFormFieldTypes() as $className) {
                $singleton = singleton($className);
                if ($singleton->canCreate(Member::currentUser())) {
                    $field_types[$className] = "{$singleton->Label()}";
                }
            }

            $config = new GridFieldConfig_FlexiForm();
            $component = $config->getComponentByType('GridFieldAddNewMultiClass');
            $component->setTitle($this->getFlexiFormAddButton());
            $component->setClasses($field_types);

            // hint allowed types to FlexiFormField search fields
            singleton('FlexiFormField')->set_stat('allowed_types', $field_types);

            $fields_tab->push(
                new GridField('FlexiForm', 'Form Fields', $this->owner->FlexiFormFields(), $config));

            // Settings
            ///////////


            $settings_tab->push(new HiddenField('FlexiFormConfigs')); // trigger setFlexiFormConfigs method
            $settings_tab->push(
                new TextField('FlexiFormConfig[FormIdentifier]', 'Form Identifier',
                    $this->FlexiFormConf('FormIdentifier')));

            $settings_tab->push(
                new DropdownField('FlexiFormConfig[HandlerID]', 'Form Handler', FlexiFormHandler::get()->map(),
                    $this->FlexiFormConf('HandlerID')));

            $field = new ToggleCompositeField('ManageHandlers', 'Manage Handlers',
                array(
                    new GridField('FlexiHandlers', 'Handlers', FlexiFormHandler::get(),
                        new GridFieldConfig_FlexiFormHandler())
                ));

            $settings_tab->push($field);

            // Handler-specific Fields
            //////////////////////////


            $handler = $this->FlexiFormHandler();
            if ($handler->exists()) {
                $handler->updateCMSFlexiTabs($flexi_tabs, $settings_tab, $this->owner);
            }
        } else {
            $fields->addFieldToTab($this->getFlexiFormTab(),
                new LiteralField('FlexiForm', '<p>Please save before editing the form.</p>'));
        }
    }

    /**
     * Get the FieldList for this form
     *
     * @return FieldList
     */
    public function getFlexiFormFrontEndFields()
    {
        $fields = new FieldList();
        foreach ($this->owner->FlexiFormFields()->sort('SortOrder') as $flexi_field) {
            $title = (empty($flexi_field->Prompt)) ? $flexi_field->getName() : $flexi_field->Prompt;
            $fields->push(
                $flexi_field->getFormField($title, $flexi_field->DefaultValue, $flexi_field->Required));
        }
        return $fields;
    }

    // Getters & Setters
    ////////////////////
    public function getFlexiFormTab()
    {
        return $this->owner->stat('flexiform_tab');
    }

    public function setFlexiFormTab($tab_name)
    {
        return $this->owner->set_stat('flexiform_tab', $tab_name);
    }

    public function getFlexiFormInsertBefore()
    {
        return $this->owner->stat('flexiform_insertBefore');
    }

    public function setFlexiFormInsertBefore($field_name)
    {
        return $this->owner->set_stat('flexiform_insertBefore', $field_name);
    }

    public function getFlexiFormAddButton()
    {
        return $this->owner->stat('flexiform_addButton');
    }

    public function setFlexiFormAddButton($button_name)
    {
        return $this->owner->set_stat('flexiform_addButton', $button_name);
    }

    public function getFlexiFormFieldTypes()
    {
        $field_types = $this->owner->stat('flexiform_field_types');

        if (empty($field_types)) {
            // allow all field types by default
            $field_types = SS_ClassLoader::instance()->getManifest()->getDescendantsOf(
                'FlexiFormField');

            // remember for later...
            $this->setFlexiFormFieldTypes($field_types);
        }

        return $field_types;
    }

    public function setFlexiFormFieldTypes(Array $field_types)
    {
        return $this->owner->set_stat('flexiform_field_types', $field_types);
    }

    public function getFlexiFormInitialFields()
    {
        return $this->owner->stat('flexiform_initial_fields');
    }

    public function setFlexiFormInitialFields(Array $field_definitions)
    {
        return $this->owner->set_stat('flexiform_initial_fields', $field_definitions);
    }

    public function getFlexiFormDefaultHandlerName()
    {
        return $this->owner->stat('flexiform_default_handler_name');
    }

    public function setFlexiFormDefaultHandlerName($handler_name)
    {
        return $this->owner->set_stat('flexiform_default_handler_name', $name);
    }

    // allow editing config, handler settings in form gridfield
    public function setFlexiFormConfigs($value)
    {
        if ($configs = Controller::curr()->getRequest()->requestVar('FlexiFormConfig')) {

            $conf = $this->FlexiFormConf();

            foreach ($configs as $property => $value) {
                if ($property == 'Setting') {
                    $settings = ($value[$conf->HandlerID]) ?  : array();
                    $conf->updateHandlerSettings($settings);
                } elseif ($conf->hasDatabaseField($property)) {
                    $conf->$property = $value;
                }
            }

            $conf->write();
        }
    }
    // Utility Methods
    //////////////////
    public function FlexiFormConf($fieldName = null)
    {
        $conf = $this->owner->FlexiFormConfig();
        return ($fieldName) ? $conf->relField($fieldName) : $conf;
    }

    public function FlexiFormID()
    {
        return $this->FlexiFormConf('FormIdentifier');
    }

    public function FlexiFormHandler()
    {
        return $this->FlexiFormConf('Handler');
    }

    public function FlexiFormSetting($setting){
        return $this->FlexiFormConf("Setting.$setting");
    }

    public function validate(ValidationResult $result)
    {
        $names = array();
        if ($result->valid()) {
            foreach ($this->owner->FlexiFormFields() as $field) {

                if (empty($field->Name)) {
                    $result->error("Field names cannot be blank. Encountered a blank {$field->Label()} field.");
                    break;
                }

                if (in_array($field->Name, $names)) {
                    $result->error(
                        "Field Names must be unique per form. {$field->Name} was encountered more than once.");
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

            if ($this->FlexiFormID() && $flexi = FlexiFormUtil::GetFlexiByIdentifier($this->FlexiFormID())) {
                if ($flexi->ID != $this->owner->ID) {
                    $result->error('Form Identifier in use by another form.');
                }
            }
        }
    }

    public function onAfterWrite()
    {
        $conf = $this->FlexiFormConf();

        // ensure valid config
        //////////////////////
        if (! $conf->exists()) {

            if ($name = $this->getFlexiFormDefaultHandlerName()) {
                if ($handler = FlexiFormHandler::get()->filter('HandlerName', $name)->first()) {
                    $conf->HandlerID = $handler->ID;
                }
            }
            $conf->FlexiFormID = $this->owner->ID;
            $conf->FlexiFormClass = $this->owner->class;
            $conf->write();

            $this->owner->FlexiFormConfigID = $conf->ID;
            $this->owner->write();
        }

        // initialize fields on new forms
        /////////////////////////////////

        if (!$this->FlexiFormConf('InitializedFields')) {
            $definitions = $this->getFlexiFormInitialFields();
            if(!empty($definitions)) {
                $fields = $this->owner->FlexiFormFields();
                foreach ($definitions as $definition) {
                    if (! is_array($definition) || ! isset($definition['Name']) || ! isset($definition['Type'])) {
                        throw new ValidationException(
                            'Initial Field Definitions must be an associative array, with at least Name and Type provided.');
                    }

                    // lookup field name, prioritizing Readonly fields
                    if (! $field = FlexiFormField::get()->sort('Readonly', 'DESC')
                        ->filter(
                        array(
                            'FieldName' => $definition['Name'],
                            'ClassName' => $definition['Type']
                        ))
                        ->first()) {
                        $field = FlexiFormUtil::CreateFlexiField($definition['Type'], $definition);
                    }

                    $extraFields = array();
                    foreach (array_intersect_key($definition, $fields->getExtraFields()) as $property => $value) {
                        $extraFields[$property] = $value;
                    }

                    $fields->add($field, $extraFields);
                }
                $conf->InitializedFields = true;
                $conf->write();
            }
        }

        // seed Form Identifier
        ///////////////////////


        if (! $this->FlexiFormID()) {
            $conf = $this->FlexiFormConf();

            // @TODO perhaps base on title of extended object??
            $conf->FormIdentifier = "{$this->owner->class}_{$this->owner->ID}";
            $conf->write();
        }

        return parent::onAfterWrite();
    }

    public function onBeforeWrite()
    {}

    public function onBeforeDelete()
    {
        $conf = $this->FlexiFormConf();
        if ($conf->exists()) {
            $conf->delete();
        }
        return parent::onBeforeDelete();
    }
}