<?php

class FlexiFormField extends DataObject
{

    protected $field_class = 'FormField';

    protected $field_label = 'Override Me';

    protected $field_description = 'Override Me';

    // used to automatically generate fields during /dev/build
    protected $field_definition = null;

    private static $db = array(
        'FieldName' => 'Varchar(16)',
        'FieldDefaultValue' => 'Varchar',
        'Readonly' => 'Boolean'
    );

    private static $has_many = array(
        'Options' => 'FlexiFormFieldOption'
    );

    private static $belongs_many_many = array(
        'FlexiForms' => 'FlexiForm'
    );

    private static $searchable_fields = array(
        'FieldName' => array(
            'title' => 'Name',
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter'
        )
    );

    private static $summary_fields = array(
        'FieldName' => 'Name'
    );

    function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Options');
        $fields->removeByName('FlexiForms');

        $field = $fields->dataFieldByName('FieldName');
        $field->setTitle('Name');
        $field->setMaxLength(16);
        $field->description = 'Shown in submissions. Should be short and without special characters.';

        $field = $fields->dataFieldByName('FieldDefaultValue');
        $field->setTitle('Default Value');
        $field->description = 'Optional. Will prepopulate the field with this value.';

        $field = new LiteralField('Description',
            "<strong>{$this->field_label} Field &mdash;</strong> {$this->field_description} <hr />");
        $fields->addFieldToTab('Root.Main', $field, 'FieldName');

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        if ($result->valid()) {
            if (empty($this->FieldName)) {
                $result->error('Name cannot be blank');
            }
        }

        return $result;
    }

    // override this method for custom behavior
    public function getFormField($name, $value = null)
    {
        $field_class = $this->field_class;
        $field = new $field_class($name);

        if ($value !== null) {
            $field->setValue($value);
        }

        return $field;
    }

    public function Label()
    {
        return $this->field_label;
    }

    public function Description()
    {
        return $this->field_description;
    }

    public function OptionsPreview()
    {
        return '-';
    }

    public function getTitle()
    {
        $system = ($this->SystemField) ? '*' : '';
        return "{$this->FieldName} ({$this->field_label})$system";
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

    public function getFlexiFieldDefinition()
    {
        return $this->field_definition;
    }

    public function setFlexiFieldDefinition(Array $flexi_field_definition)
    {
        return $this->field_definition = $flexi_field_definition;
    }

    public function requireDefaultRecords()
    {
        // if this field has a definition, attempt to create it
        $definition = $this->getFlexiFieldDefinition();

        if ($definition !== null) {

            $filter = array(
                'FieldName' => $definition['Name'],
                'Readonly' => (isset($definition['Readonly']) && $definition['Readonly'])
            );

            // only create field if it's name doesn't yet exist
            if (! FlexiFormField::get()->filter($filter)->first()) {

                if ($field = FlexiFormUtil::CreateFlexiField($definition)) {
                    $readonly = ($field->Readonly) ? 'Readonly' : 'Normal';
                    DB::alteration_message(
                        "flexiforms - Created $readonly $this->className} named `{$field->FieldName}`.",
                        "created");
                }
            }
        }

        return parent::requireDefaultRecords();
    }
}
