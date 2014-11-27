silverstripe-flexiforms
=======================

Add configurable forms to your pages. Features intuitive GridField based management of fields and submissions through the CMS.

**Work in progress. Pre-Release. Field Management is pretty much in place, need to finish
submission handling and frontend work.**

Features
--------

* GridField management of fields, field options, and submissions.
* Extensible Field Types (`FlexiFormField`)
  * Text, Email, Select, Checkbox, Radio, and Checkbox Set field types out-of-box.
  * Easily create new FlexiFormField types
* Extensible Forms (`FlexiForm`)
  * Programmatically define initial fields added to newly created forms
  * Limit allowed field types per form
* **Many-many** relationship between Forms and Fields
  * Reduces administrative repetitiveness and improves consistency. 
  * Settings are stored as _many_many_extraFields_, allowing per-form customization without disturbing other forms using the same field.
* Automatically create fields in the Environment Builder (during /dev/build)  
* Compatible with  [holder pages](https://github.com/briceburg/silverstripe-holderpage) + VersionedGridfield
 

Requirements
============

The venerable GridFieldExtensions https://github.com/ajshort/silverstripe-gridfieldextensions

Usage 
=====

`FlexiForm` - Extends the _Page_ class. 

`FlexiFormField` - Base class all FlexiFormField types extend from.

Basic Usage
-----------

1. Add a "Flexi Form" Page to the SiteTree
1. Save the page and configure fields via the __Form__ tab

By default, Flexi Forms can be created and deleted by anyone with access to
create or delete Pages. 


Custom Forms
------------

The behavior of a `FlexiForm` is customized through subclasses. 

* Create a Custom Form by extending `FlexiForm`
* Flush the cache to register it in your manifest.

Default behavior is controlled by visible protected properties, and 
manipulated with getters and setters. 

For instance, A form's allowed field types 
are retrieved by the **getAllowedFlexiTypes** method of the `FlexiForm` class. 
This method returns the protected **$allowed_flexi_types** property, which 
can be manipulated with **setAllowedFlexiTypes**, and fetched with
**addAllowedFlexiType**.

This approach allots flexibility and enables different strategies to accomplish 
behavioral needs.


### Limiting Field Types

The choice of fields can be limited on a per form basis. Here's a couple examples. 

* Strategy 1: Overload **$allowed_flexi_types** in your custom form

```php
class MyForm extends FlexiForm {

  protected $allowed_flexi_types = array(
    'FlexiFormTextField',
    'FlexiFormDropdownField'
  );

}
```

* Strategy 2: Append a custom type via **addAllowedFlexiType**

```php
class MyForm extends FlexiForm {

  public function getCMSFields()
  {
    $this->addAllowedFlexiType('MyCustomFlexiFormField');
    
    $fields = parent::getCMSFields();
    
    return $fields;
   }
}
```

### Changing the Tab FlexiForm appears in

The tab a FlexiForm appears in is controlled by  the **$flexiform_tab** property 
by default. It is manipulated with the **setFlexiFormTab**, and retreived via
**getFlexiFormTab**.


* Strategy 1: Overload **$flexiform_tab** in your custom form

```php
class MyForm extends FlexiForm {

  protected $flexiform_tab = 'Root.Main';

}
```

* Strategy 2: Set via  **setFlexiFormTab** 

```php
class MyForm extends FlexiForm {

  public function getCMSFields()
  {
    $this->setFlexiFormTab('Root.Registration');
    
    $fields = parent::getCMSFields();
    
    return $fields;
  }

}
```

### Allow only your Custom Form to be created


Disallow the creation and deletion of Flexi Forms through YAML 
configuration. E.g. add the following to mysite/config/config.yml

```
---
FlexiForm:
  can_create: false
  can_delete: false
  
```

Then, in your Custom Form class, override the **canCreate** and **canDelete**
methods.

```php
class MyForm extends FlexiForm {

  public function canCreate($member = null) {
    return singleton('Page')->canCreate($member);
  }

  public function canDelete($member = null) {
    return singleton('Page')->canDelete($member);
  }

}

```
 
### Automatically adding fields to a form

Newly created forms can be programmatically initialized with fields. Fields
can be created on-the-fly, or existing fields can be referenced and reused. This
greatly reduces administrative repetitiveness and improves consistency.

* Field definitions are defined as _Arrays_
  * If the array value is a string, the field whose Name matches the value will be linked to the form.
  * If the value is an array, a field will be created from the array components.
    * Name is required. 
    * If supplying Options, use Value as array Key and Label as array Value .
  

* Strategy 1: Overload **$default_flexi_fields** in your custom form

```php
class AuthorChoiceForm extends FlexiForm {

  protected $default_flexi_fields = array(
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
class Event extends FlexiForm {

  public function getCMSFields()
  {
      $this->setFlexiFormTab('Root.Registration');
      $this->setDefaultFlexiFields(
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

New custom fields can be created by subclassing FlexiFormField types.
* If your field has selectable options (like a Dropdown), extend the `FlexiFormOptionField` type.
* If your field does not have selectable options (like an Email), extend the `FlexiFormField` type.
* Alternatively, extend the [existing type](https://github.com/briceburg/silverstripe-flexiforms/tree/master/code/fieldtypes) that best matches your behavior.


TODO: frontend field documentation &c.

### Programmatically adding fields

The Environment Builder (/dev/build) is used to automatically create fields.
FlexiFormFields with valid $field_definitions will be created. Fields can 
share the same name providing they're a different type.

* First, create your Custom Field with a valid **$field_definition** property.
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
* Second, execute the Environment Builder (e.g. by visiting /dev/build)


### Readonly fields

By default, all fields are editable. The CMS administrator can change the base
field name, the list of selectable options, &c. This flexibility could be 
unwanted in certain situations. 

For instance, pretend you have a Custom Form that specifies a field named 
'Email' in  _$default_flexi_fields_. Soon after, the admin renamed the 'Email' 
field to 'WorkEmail'. Now, whenever the Custom Form is created, a Validation 
Exception will occur complainging that the 'Email' field is not found. 

Readonly fields are a great way to protect against this. They are different from
normal fields as follows;
* **Readonly fields have their FieldName, FieldDefaultValue, and Options locked**
* **Readonly fields are marked with an asterix (*) when searched for**
* **Readonly fields must be created programmatically**
* **Readonly fields must have a unique FieldName**

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

