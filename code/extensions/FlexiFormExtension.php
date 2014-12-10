<?php
//@TODO Validate Identifier / force aphanumeric_
class FlexiFormExtension extends DataExtension
{

    /**
     * Toggles appending flexiform fields to the CMS.
     * @var Boolean
     */
    private static $flexiform_update_cms_fields = true;

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
     * The name of the default handler for this form. See flexiform.yml
     * @TODO ?
     * @var String
     */
    private static $flexiform_default_handler_name = 'Default';

    private static $db = array(
        'FlexiFormIdentifier' => 'Varchar'
    );

    private static $has_one = array(
        'FlexiFormHandler' => 'FlexiFormHandler'
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

    public function populateDefaults()
    {
        // do not attempt looking up handlers if database tables are not ready
        if (! Controller::curr()->is_a('DatabaseAdmin')) {
            if ($name = $this->getFlexiFormDefaultHandlerName()) {
                if ($handler = FlexiFormHandler::get()->filter('HandlerName', $name)->first()) {
                    $this->owner->FlexiFormHandlerID = $handler->ID;
                }
            }
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        if (! $this->getFlexiFormUpdateCMSFields()) {
            return;
        }

        $fields->removeByName('FlexiFormFields');
        $fields->removeByName('FlexiFormHandlerID');

        if ($this->owner->exists()) {

            $fields_tab = new Tab('Fields');
            $settings_tab = new Tab('Settings');

            $fields->addFieldToTab($this->getFlexiFormTab(),
                $flexi_tabs = new TabSet('flexiform', $fields_tab, $settings_tab),
                $this->getFlexiFormInsertBefore());

            // Fields
            /////////
            $field_types = array();
            foreach($this->getFlexiFormFieldTypes() as $className){
                $singleton = singleton($className);
                $field_types[$className] = "{$singleton->Label()}";
            }

            $config = new GridFieldConfig_FlexiForm();
            $component = $config->getComponentByType('GridFieldAddNewMultiClass');
            $component->setTitle($this->getFlexiFormAddButton());
            $component->setClasses($field_types);


            $fields_tab->push(
                new GridField('FlexiForm', 'Form Fields', $this->owner->FlexiFormFields(), $config));

            // Settings
            ///////////


            $settings_tab->push(new TextField('FlexiFormIdentifier', 'Form Identifier'));

            $singleton = singleton('FlexiFormHandler');
            $singleton->set_stat('selected_handler_id', $this->owner->FlexiFormHandlerID);

            $settings_tab->push(
                new DropdownField('FlexiFormHandlerID', 'Form Handler', FlexiFormHandler::get()->map()));

            $field = new ToggleCompositeField('ManageHandlers', 'Manage Handlers',
                array(
                    new GridField('FlexiHandlers', 'Handlers', FlexiFormHandler::get(),
                        new GridFieldConfig_FlexiFormHandler())
                ));

            $settings_tab->push($field);

            // Handler-specific Fields
            //////////////////////////


            $handler = $this->owner->FlexiFormHandler();
            if ($handler->exists()) {

                $other_form_count = $handler->FormCount() - 1;
                $plural = ($other_form_count > 1) ? 'forms' : 'form';
                $description = ($other_form_count) ? "<em>Your changes will impact <strong>$other_form_count other $plural</strong>. If you would like your changes to impact only this form, create a new handler.</em>" : '';
                $field = new LiteralField('HandlerSettings', "<h3>Handler Settings</h3>$description");

                $settings_tab->push($field);

                // let selected handler augment fields
                $handler->updateCMSFlexiTabs($flexi_tabs, $this->owner);
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
    public function getFlexiFormUpdateCMSFields()
    {
        return $this->lookup('flexiform_update_cms_fields');
    }

    public function setFlexiFormUpdateCMSFields($boolean)
    {
        return $this->owner->set_stat('flexiform_update_cms_fields', $boolean);
    }

    public function getFlexiFormTab()
    {
        return $this->lookup('flexiform_tab');
    }

    public function setFlexiFormTab($tab_name)
    {
        return $this->owner->set_stat('flexiform_tab', $tab_name);
    }

    public function getFlexiFormInsertBefore()
    {
        return $this->lookup('flexiform_insertBefore');
    }

    public function setFlexiFormInsertBefore($field_name)
    {
        return $this->owner->set_stat('flexiform_insertBefore', $field_name);
    }

    public function getFlexiFormAddButton()
    {
        return $this->lookup('flexiform_addButton');
    }

    public function setFlexiFormAddButton($button_name)
    {
        return $this->owner->set_stat('flexiform_addButton', $button_name);
    }

    public function getFlexiFormFieldTypes()
    {
        $field_types = $this->lookup('flexiform_field_types');

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
        return $this->lookup('flexiform_initial_fields');
    }

    public function setFlexiFormInitialFields(Array $field_types)
    {
        return $this->owner->set_stat('flexiform_initial_fields', $field_types);
    }

    public function getFlexiFormDefaultHandlerName()
    {
        return $this->lookup('flexiform_default_handler_name');
    }

    public function setFlexiFormDefaultHandlerName($handler_name)
    {
        return $this->owner->set_stat('flexiform_default_handler_name', $name);
    }

    // hack to allow editing handler from form gridfield,
    //   perhaps use gridfieldaddons linline editor instead?
    public function setFlexiFormHandlerSettings($value)
    {
        if ($settings = Controller::curr()->getRequest()->requestVar('FlexiFormHandlerSetting')) {

            $handler = $this->owner->FlexiFormHandler();
            foreach (array_intersect_key($settings, $handler->db()) as $property => $value) {
                $handler->$property = $value;
            }
            $handler->write();
        }
    }
    // Utility Methods
    //////////////////
    private function lookup($lookup, $do_not_merge = false)
    {
        if ($do_not_merge &&
             $unmerged = Config::inst()->get($this->owner->class, $lookup, Config::EXCLUDE_EXTRA_SOURCES)) {
            return $unmerged;
        }

        return $this->owner->stat($lookup);
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

            if ($this->owner->FlexiFormIdentifier &&
                 $flexi = FlexiFormUtil::GetFlexiByIdentifier($this->owner->FlexiFormIdentifier)) {
                if ($flexi->ID != $this->owner->ID) {
                    $result->error('Form Identifier is used by another form.');
                }
            }
        }
    }

    public function onAfterWrite()
    {
        // if this is a newly created form, prepopulate fields
        if ($this->owner->isChanged('ID')) {

            $fields = $this->owner->FlexiFormFields();
            foreach ($this->getFlexiFormInitialFields() as $field_type => $definition) {

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

        // add the handler mapping
        if ($this->owner->exists() && $this->owner->FlexiFormHandler()->exists()) {
            FlexiFormHandlerMapping::addMapping($this->owner->FlexiFormHandler(), $this->owner);
        }

        // seed the identifier
        // @TODO perhaps base on title of extended object??
        if (empty($this->owner->FlexiFormIdentifier)) {
            $this->owner->FlexiFormIdentifier = "{$this->owner->class}_{$this->owner->ID}";
            $this->owner->write();
        }

        return parent::onAfterWrite();
    }

    public function onBeforeWrite()
    {}

    public function onBeforeDelete()
    {
        FlexiFormHandlerMapping::removeFormMapping($this->owner);

        return parent::onBeforeDelete();
    }
}