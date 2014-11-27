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
  * Field settings are controlled as _extrafields_, allowing per-form customization without disturbing other forms using the same field.
* Ability to define System Field types, automatically created during /dev/build  
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

Fields can be programatically defined and added to newly created forms. Because 
FlexiForms features shared fields via many_many relationships, you can reuse a 
field over and over. This all greatly reduces administrative repetitiveness and 
improves consistency.

* Field definitions are defined in an _Array_
* If the array value is a string, the field whose Name matches the value will be linked to the form. 
* If the value is an array, a field will be created from the array components.
  * Name and Type are required. 
  * If supplying Options, use Value as array Key and Label as array Value .
  

* Strategy 1: Overload **$default_flexi_fields** in your custom form

```php
class AuthorChoiceForm extends FlexiForm {

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

}
```

* Strategy 2: Set via  **setDefaultFlexiFields** 


_This example assumes that fields named FirstName, LastName, and Email 
already exist. Perhaps by manually being created or better yet - created
as a System Field_

```php
class Event extends FlexiForm {

  public function getCMSFields()
  {
    $this->setFlexiFormTab('Root.Registration');
    $this->setDefaultFlexiFields(array(
      'FirstName',
      'LastName',
      'Email'
    ));
    
    $fields = parent::getCMSFields();
    
    return $fields;
  }

}
```

  
Custom Fields
-------------

Documentation coming soon....

  
  
