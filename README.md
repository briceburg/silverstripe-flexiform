silverstripe-flexiforms
=======================

Add CMS configurable forms to your SilverStripe objects. 

Features
--------

* Add forms to DataObjects or Pages
* GridField based management of fields, options, submissions, actions, &c.
  * 100% compatible with [holder pages](https://github.com/briceburg/silverstripe-holderpage) & VersionedGridfield
* Programmatically define initial fields and handlers + build them from the Environment Builder
* **Many-many** between Form and `FlexiFormField`, **has_many** between `FlexiFormHandler`
  * reduced repetitiveness and improved consistency
  * _extraFields_ allows per-form customization without disturbing other forms using the same field
 

**Pre-Release Status** - Field managment + submissions are in place. Still tuning & documenting.

For now, **be the source, be the source Danny**. 

Comments / PRs welcome!


Requirements
------------

The venerable GridFieldExtensions https://github.com/ajshort/silverstripe-gridfieldextensions

Tested in SilverStripe 3.1

Screenshots
-----------

![flexiform fields](https://github.com/briceburg/silverstripe-flexiform/blob/master/docs/screenshots/flexiform_1.png?raw=true)

![field editing](https://github.com/briceburg/silverstripe-flexiform/blob/master/docs/screenshots/flexiform_2.png?raw=true)



Usage 
=====

* Add configurable forms to your DataObjects and Pages by extending them with the
`FlexiFormExtension` DataExtension.  E.g.

```php
class Event extends DataObject
{

    private static $extensions = array(
        'FlexiFormExtension'
    );

}
```
Trigger the environment builder (/dev/build) after extending objects --
You will now see the Form tab when editing Event in the CMS.

* To display flexiforms, add **$FlexiForm** to your template. Here's a sample Event.ss;

```html
<div class="width-30">
    <% include SectionNav %>
</div>
  
  
<div class="width-70">
  <% if not FlexiFormPosted %>
    $Content
  <% end_if %>
   
  $FlexiForm    
</div>
```

FlexiForm extends ContentController out-of-box to make forms work. 
The _FlexiForm_ method returns a standard SilverStripe `Form`, and the _FlexiFormPosted_ method
returns true if the FlexiForm has been **successfully** posted. 

* To change the form template, do so in your controller. E.g.

```php

class FormPage_Controller extends Page_Controller {
  public function MyFlexiForm() {
    $form = $this->FlexiForm();
    $form->setTemplate('MyFormTemplate');
    return $form;    
  }
}

// and modify MyFormTemplate.ss in your themedir accordingly...
```

Configuration
=============

Most configuration is accomplished through the CMS -- however you can further 
tailor behavior through subclassing (protected properties, getters, and setters)
and [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration).

This approach allots flexibility and enables different strategies to accomplish 
behavioral needs.


### Limiting Field Types

The choice of fields types can be defined per form. Here's a couple examples. 


* Strategy 1: Using [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration)

```yaml
---
FormPage:
  flexiform_field_types:
    - FlexiFormTextField
    - FlexiFormDropdownField
```

* Strategy 2: Overload  the **$flexiform_field_types** property

```php
class FormPage extends Page {

  private static $extensions = array(
    'FlexiFormExtension'
  );
    
  private static $flexiform_field_types = array(
    'FlexiFormTextField',
    'FlexiFormDropdownField'
  );

}
```

* Strategy 3: Use the **setFlexiFormFieldTypes** setter method 

```php
class FormPage extends Page {

  private static $extensions = array(
    'FlexiFormExtension'
  );

  public function getCMSFields()
  {
    // make configuration changes _BEFORE_ calling parent getCMSFields...
    $allowed_types = array(
      'FlexiFormTextField',
      'FlexiFormDropdownField'
    );
    $this->setFlexiFormFieldTypes($allowed_types);
    
    $fields = parent::getCMSFields();
    
    return $fields;
   }
}
```

### Changing the Tab FlexiForm appears in

By default, flexiform will add a Form to "Root.Form". You can change it a couple of ways;

* Strategy 1: Using [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration)

```yaml
---

# Global Change
FlexiFormExtension:
  flexiform_tab: Root.Addresses
  
# Class Specific
FormPage:
  flexiform_tab: Root.Main
  flexiform_insertBefore: Metadata
```

* Strategy 2: Overload the **setFlexiFormTab** property

```php
class FormPage extends Page {

  private static $extensions = array(
    'FlexiFormExtension'
  );
  
  private static $flexiform_tab = 'Root.Main';
  private static $flexiform_insertBefore = 'Metadata';

}
```

* Strategy 3: Use the **setFlexiFormTab** setter method 

```php
class RegistrationForm extends DataObject {

  public function getCMSFields()
  {
    $this->setFlexiFormTab('Root.Registration');
    
    $fields = parent::getCMSFields();
    
    return $fields;
  }

}
```
 
### Automatically adding fields to a form

Newly created forms can be programmatically initialized with fields. Fields
are created on-the-fly, or existing fields can be referenced and reused. This
greatly reduces administrative repetitiveness and improves consistency.

* Field definitions are defined as map of _Arrays_, with the key representing Field Type.
  * If the value is a string, the field with mating Name and Type will be reused.  
  * If the value is an array, a field will be created from the array components.
    * Name is required. 
    * If supplying Options, use Value => Label.

* Strategy 1: Overload the **$flexiform_initial_fields** property

```php
class AuthorChoiceForm extends DataObject {

  private static $extensions = array(
      'FlexiFormExtension'
  );

  private static $flexiform_initial_fields = array(
      // link the existing FlexiFormEmailField with Name "Email"
      'FlexiFormEmailField' => 'Email',

      // create a new FlexiFormDropdownField to spec
      'FlexiFormDropdownField' => array(
          'Name' => 'Author',
          'Type' => '',
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

}
```

* Strategy 2: Use the **setDefaultFlexiFields** setter method 


_This example assumes that fields named FirstName, LastName, and Email 
already exist. Perhaps by manually being created or better yet - created
as a Readonly fields during /dev/build_

```php
class Event extends SiteTree {

  private static $extensions = array(
      'FlexiFormExtension'
  );
  
  public function getCMSFields()
  {
      // make configuration changes _BEFORE_ calling parent getCMSFields...
      $this->setFlexiFormTab('Root.Registration');
      $this->setFlexiFormInitialFields(
          array(
              'FlexiFormTextField' => 'FirstName',
              'FlexiFormTextField' => 'LastName',
              'FlexiFormEmailField' => 'Email'
          ));
      
      $fields = parent::getCMSFields();
      
      return $fields;
  }

}
```

  
Custom Fields
-------------

New custom fields can be created by subclassing `FlexiFormField` types.
* If your field has selectable options (like a Dropdown), extend the `FlexiFormOptionField` type.
* If your field does not have selectable options (like an Email), extend the `FlexiFormField` type.
* Alternatively, extend the [existing type](https://github.com/briceburg/silverstripe-flexiforms/tree/master/code/model/fieldtypes) that best matches your behavior.


TODO: frontend field documentation &c.

### Programmatically adding fields

The Environment Builder (/dev/build) is used to automatically create fields
specifying a **$required_field_definitions** property. Fields can 
share a FieldName providing they're of a different type.

* Strategy 1: Overload the **$required_field_definitions** property

First, create your Custom Field with a valid **$field_definitions** property.
```php
<?php
class FlexiAuthorField extends FlexiFormDropdownField
{
  protected $field_description = 'Author Preference Dropdown';
  protected $field_label = 'Author';

  private static $required_field_definitions = array(
    array(
      'Name' => 'Author',
      'EmptyString' => 'Other',
      'Options' => array(
        'Balzac' => 'Honoré de Balzac',
        'Dumas' => 'Alexandre Dumas',
        'Flaubert' => 'Gustave Flaubert',
        'Hugo' => 'Victor Hugo',
        'Verne' => 'Jules Verne',
        'Voltaire' => 'Voltaire'
      )
    )
  );

}
```


* Strategy 2: Using [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration), **especially useful for creating fields from built-in field types**

```yaml
---
FlexiFormTextField:
  required_field_definitions: 
    - { Name: FirstName }
    - { Name: LastName }
    
FlexiFormDropdownField:
  required_field_definitions:
    - { Name: Preference, Options: { Eastern: Abacus, Western: Calculator } }
``` 

Be sure trigger the Environment Builder (e.g. by visiting /dev/build) after making changes.

### Readonly fields

By default, all fields are editable. The CMS administrator can change the 
FieldName, list of selectable options, &c. This flexibility may be 
unwanted in certain situations. 

For instance, pretend you have a Custom Form that references a field named 
'Email' in  _$initial_flexi_fields_. Soon after, the admin renamed the 'Email' 
field to 'WorkEmail'. Now, whenever the Custom Form is created, a Validation 
Exception will occur complaining that 'Email' is not found. 

Readonly fields are a great way to protect against this. They differ from
normal fields as follows;
* **Readonly fields have their FieldName, FieldDefaultValue, and Options locked**
* **Readonly fields are marked with an asterix (*) when searched for**
* **Readonly fields must be created programmatically**
* **Readonly field names must be unique, regardless of underlying field type**

```php
class FlexiAuthorField extends FlexiFormDropdownField
{
  protected $required_field_definitions = array(
    array(
      'Name' => 'Author',
      'Readonly' => true,
      ...
}
```

or

```yaml
---
FlexiAuthorField:
  required_field_definitions: 
    - { Name: Author, Readonly: true }
```