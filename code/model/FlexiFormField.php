<?php

class FlexiFormField extends DataObject
{
    // @TODO use required_records for consistency
    // @TODO use Title for consistency?
    private static $field_class = 'FormField';

    private static $field_label = 'Form Field';

    private static $field_description = 'A description of this field.';

    // set to false to disallow/hide creation of this field on forms
    private static $can_create = true;

    // used to automatically generate fields during /dev/build
    private static $required_field_definitions = array();

    private static $db = array(
        'FieldName' => 'Varchar(16)',
        'FieldDefaultValue' => 'Varchar',
        'Readonly' => 'Boolean'
    );

    private static $has_many = array(
        'Options' => 'FlexiFormFieldOption'
    );

    private static $searchable_fields = array(
        'FieldName' => array(
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter',
            'title' => 'Name'
        ),
        'ClassName' => array(
            'field' => 'DropdownField',
            'filter' => 'ExactMatchFilter',
            'title' => 'Type'
        )
    );

    private static $summary_fields = array(
        'FieldName' => 'Name'
    );

    private static $default_sort = array(
        'FieldName'
    );

    public function canCreate($member = null)
    {
        return ($this->stat('can_create') === false) ? false : parent::canCreate($member);
    }

    public function canDelete($member = null)
    {
        return ($this->Readonly) ? false : parent::canDelete($member);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Options');
        $fields->removeByName('FlexiForms');
        $fields->removeByName('Readonly');

        $field = $fields->dataFieldByName('FieldName');
        $field->setTitle('Name');
        $field->setMaxLength(16);
        $field->description = 'Shown in submissions. Should be short and without special characters.';


        if($this->hasMethod('getDefaultValueFormField')) {
            $fields->replaceField('FieldDefaultValue', $this->getDefaultValueFormField());
        }

        $field = $fields->dataFieldByName('FieldDefaultValue');
        $field->setTitle('Default Value');
        $field->description = 'Optional. Will prepopulate the field with this value.';




        $field = new LiteralField('Description',
            "<strong>{$this->Label()}&mdash;</strong> {$this->Description()} <hr />");
        $fields->addFieldToTab('Root.Main', $field, 'FieldName');

        if ($this->Readonly) {
            $fields = $fields->transform(new ReadonlyTransformation());
        }

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        if (empty($this->FieldName)) {
            $result->error("Name is required.");
        } elseif ($obj = DataObject::get($this->class)->filter(
            array(
                'FieldName' => $this->FieldName,
                'ID:not' => $this->ID,
                'Readonly' => $this->Readonly
            ))->first()) {
            $result->error("A {$obj->Label()} is already titled {$this->FieldName}.");
        }

        return $result;
    }

    public function scaffoldSearchFields($_params = null)
    {
        $fields = parent::scaffoldSearchFields($_params);

        if($allowed_types = $this->stat('allowed_types')) {
             $field = $fields->dataFieldByName('ClassName');
             $field->setSource($allowed_types);
             $field->setEmptyString('Select Type');
        }
        return $fields;
    }

    /*
     * Get the field used in the front end. A great hook for customizing
     * validation and attributes. Always use SafeName method for field name.
     *
     * @param string $title requested title / "prompt"
     * @param string $value requested
     * @param boolean $required if field is required for submission
     * @return FormField
     */
    public function getFormField($title = null, $value = null, $required = false)
    {
        $field_class = $this->stat('field_class');
        $field_title = ($title) ?: $this->getName();

        $field = new $field_class($this->SafeName(), $field_title);

        if ($value) {
            $field->setValue($value);
        }

        if ($required) {
            // add html5 required attribute
            $field->setAttribute('required', true);
        }

        return $field;
    }

    public function Label()
    {
        return $this->stat('field_label');
    }

    public function Description()
    {
        return $this->stat('field_description');
    }

    public function SafeName()
    {
        return sprintf('%s_%s', $this->ClassName, $this->ID);
    }

    public function OptionsPreview()
    {
        return '-';
    }

    public function transformValue($value)
    {
        return $value;
    }

    public function getTitle()
    {
        $readonly = ($this->Readonly) ? '*' : '';
        return "{$this->FieldName} ({$this->Label()})$readonly";
    }

    public function getDefaultValue()
    {
        $value = $this->getField('DefaultValue');
        $name = $this->getField('Name');
        // && empty($name) ensures DefaultValue is set only once, and subsequent allows empty values.
        return (empty($value) && empty($name)) ? $this->FieldDefaultValue : $value;
    }

    public function getName()
    {
        $value = $this->getField('Name');
        return (empty($value)) ? $this->FieldName : $value;
    }

    public function getRequiredFieldDefinitions()
    {
        return $this->stat('required_field_definitions');
    }

    public function setRequiredFieldDefinitions(Array $required_field_definitions)
    {
        return $this->set_stat('required_field_definitions', $required_field_definitions);
    }

    public function requireDefaultRecords()
    {
        foreach ($this->getRequiredFieldDefinitions() as $definition) {
            FlexiFormUtil::AutoCreateFlexiField($this->ClassName, $definition);
        }
        return parent::requireDefaultRecords();
    }

    private static $indexes = array(
        'Readonly' => true
    );
}
