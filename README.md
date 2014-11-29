silverstripe-flexiforms
=======================

Add CMS configurable forms to your SilverStripe objects. 

**Work in progress. Pre-Release. Field Management is pretty much in place, need to finish
submission handling and frontend work.**

Features
--------

* Add forms to DataObjects and Pages
* GridField based management of fields, options, submissions, actions, &c.
  * 100% compatible with [holder pages](https://github.com/briceburg/silverstripe-holderpage) & VersionedGridfield
* Extensible Field Types (`FlexiFormField`) and Forms (`FlexiForm`)
  * Programmatically define initial fields added to newly created forms
  * Limit allowed field types per form
* **Many-many** relationship between Forms and Fields - reduces administrative repetitiveness and improves consistency
  * Leverages _many_many_extraFields_ to allow per-form customization without disturbing other forms using the same field
* Programatically create fields in the Environment Builder (during /dev/build)  
 

Requirements
============

The venerable GridFieldExtensions https://github.com/ajshort/silverstripe-gridfieldextensions

Tested in SilverStripe 3.1

Usage 
=====

Add configurable forms to your DataObjects and Pages by extending them with the
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

To display forms on the Front-End, update your templates. Here's an example
Event.ss

```html

Coming Soon...

```

You may, as always, override the [built-in templates](https://github.com/briceburg/silverstripe-flexiaddress/tree/master/templates) by
adding them to your theme and changing markup as needed.



Configuration
=============

Most configuration is accomplished through the CMS -- however you can further 
tailor behavior through subclassing (protected properties, getters, and setters)
and [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration).

For instance, A form's allowed field types are retrieved by the
 **getFlexiFormFieldTypes** method of the `FlexiFormExtension` class. This method returns 
 the protected **$flexiform_field_types** property, which 
can be manipulated with **setFlexiFormFieldTypes** and **addFlexiFormFieldType** methods. 

This approach allots flexibility and enables different strategies to accomplish 
behavioral needs.


### Limiting Field Types

The choice of fields types can be defined per form. Here's a couple examples. 

* Strategy 1: Overload **$flexiform_field_types** in your custom form

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

* Strategy 2: Append a custom type via **addFlexiFormFieldType**

```php
class FormPage extends Page {

  private static $extensions = array(
    'FlexiFormExtension'
  );

  public function getCMSFields()
  {
    // make configuration changes _BEFORE_ calling parent getCMSFields...
    $this->addFlexiFormFieldType('MyCustomFlexiFormField');
    
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

* Strategy 3: Set via  **setFlexiFormTab** 

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
  

* Strategy 1: Overload **$flexiform_initial_fields** in your custom form

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

* Strategy 2: Set via  **setDefaultFlexiFields** 


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
* Alternatively, extend the [existing type](https://github.com/briceburg/silverstripe-flexiforms/tree/master/code/fieldtypes) that best matches your behavior.


TODO: frontend field documentation &c.

### Programmatically adding fields

The Environment Builder (/dev/build) is used to automatically create fields.
FlexiFormFields with valid $field_definitions will be created. Fields can 
share the same name providing they're a different type.

First, create your Custom Field with a valid **$field_definition** property.
```php
class FlexiAuthorField extends FlexiFormOptionField
{
  protected $field_definitions = array(
    array(
      'Name' => 'Author',
      'EmptyString' => 'Select your favorite Author',
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
Second, trigger the Environment Builder (e.g. by visiting /dev/build)

Alternatively, you can create fields through [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration).
This is especially useful for creating fields from built-in field types. E.g. add the 
following to mysite/config/config.yml

```yaml
---
FlexiFormTextField:
  field_definitions: 
    - { Name: FirstName }
    - { Name: LastName }
    
FlexiFormDropdownField:
  field_definitions:
    - { Name: Preference, Options: { Eastern: Abacus, Western: Calculator } }
``` 


### Readonly fields

By default, all fields are editable. The CMS administrator can change the 
FieldName, list of selectable options, &c. This flexibility may be 
unwanted in certain situations. 

For instance, pretend you have a Custom Form that references a field named 
'Email' in  _$default_flexi_fields_. Soon after, the admin renamed the 'Email' 
field to 'WorkEmail'. Now, whenever the Custom Form is created, a Validation 
Exception will occur complaining that 'Email' is not found. 

Readonly fields are a great way to protect against this. They differ from
normal fields as follows;
* **Readonly fields have their FieldName, FieldDefaultValue, and Options locked**
* **Readonly fields are marked with an asterix (*) when searched for**
* **Readonly fields must be created programmatically**
* **Readonly field names must be unique, regardless of underlying field type**

```php
class FlexiAuthorField extends FlexiFormOptionField
{
  protected $field_definitions = array(
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
  field_definitions: 
    - { Name: Author, Readonly: true }
```