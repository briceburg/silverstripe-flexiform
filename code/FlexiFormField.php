<?php

class FlexiFormField extends DataObject
{

    protected $field_class = 'FormField';

    protected $field_label = 'Override Me';

    protected $field_description = 'Override Me';

    protected $field_has_options = false;

    private static $db = array(
        'FieldName' => 'Varchar(16)',
        'FieldDefaultValue' => 'Varchar'
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

        $field = $fields->dataFieldByName('FieldName');
        $field->setTitle('Name');
        $field->setMaxLength(16);
        $field->description = 'Shown in submissions. Should be short and without special characters.';

        $field = $fields->dataFieldByName('FieldDefaultValue');
        $field->setTitle('Default Value');
        $field->description = 'Optional. Will prepopulate the field with this value.';

        $field = new LiteralField('Description', "<strong>{$this->field_label} Field &mdash;</strong> {$this->field_description} <hr />");
        $fields->addFieldToTab('Root.Main', $field, 'FieldName');

        $fields->removeByName('Options');
        if ($this->field_has_options) {
            $config = new GridFieldConfig_FlexiForm();
            $config->removeComponentsByType('GridFieldAddNewMultiClass');
            $config->removeComponentsByType('FlexiFormAddExistingAutocompleter');
            $config->removeComponentsByType('GridFieldEditButton');
            $config->removeComponentsByType('GridFieldDeleteAction');
            $config->removeComponentsByType('GridFieldDetailForm');
            $config->addComponent(new GridFieldDeleteAction(false));

            $component = new GridFieldAddNewInlineButton();
            $component->setTitle('Add Option');
            $config->addComponent($component);

            $component = $config->getComponentByType('GridFieldEditableColumns');
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

            $fields->addFieldToTab('Root.Main', new GridField('Options', 'Options', $this->Options(), $config));
        }

        return $fields;
    }

    public function validate() {
        $result = parent::validate();

        if($result->valid()) {
            if(empty($this->FieldName)) {
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
        $preview = array();
        if ($this->field_has_options) {
            $field = DBField::create_field('Text', implode(', ', $this->Options()->column('Value')));
            return $field->LimitCharacters(24);
        }

        return 'n/a';
    }

    public function getTitle() {
        return "{$this->FieldName} ({$this->field_label})";
    }



}