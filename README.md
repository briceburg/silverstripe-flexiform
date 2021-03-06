silverstripe-flexiforms
=======================

Add CMS configurable forms to your SilverStripe objects. 

Features
--------

* Add forms to any DataObject or Page
* GridField based management of fields, options, submissions, actions, &c.
  * 100% compatible with [holder pages](https://github.com/briceburg/silverstripe-pageholder) & VersionedGridfield
* Programmatically define initial fields and handlers + build them from the Environment Builder
* **Many-many** between Form and `FlexiFormField`, **has_many** between `FlexiFormHandler`
  * reduced repetitiveness and improved consistency
  * _extraFields_ allows per-form customization without disturbing other forms using the same field
* Protection against form re-submissions
* Definable, friendly post URLs for logs and analytics
* Support for multiple forms per page 


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

* Add flexiforms to Pages and DataObjects by extending with `FlexiFormExtension`. E.g.

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
<div class="event-content">
  <% if not FlexiFormPosted %>
    $Content
  <% end_if %>
   
  $FlexiForm    
</div>
```

Here we use **$FlexiFormPosted** to hide $Content if a form has been posted.

Flexiform also provides a convenience wrapper around the standard **$Form** method.
Calling $Form from a Page extended by FlexiFormExtension will output the associated
flexiform. E.g.

```html
<div class="event-content">
  <% if not FlexiFormPosted %>
    $Content
  <% end_if %>
   
  $Form    
</div>
```

Works exactly the same as the first example.


### Form Identifiers


Use Form Identifiers when you have **multiple forms on a page**, need to 
**reference a form** (e.g from another page), or want to **control the post URL**. 

FlexiForm extends `ContentController` to provide the **$FlexiForm** method to all
pages. By default it expects the controller's _dataRecord_ to be an object
extended by `FlexiFormExtension`. You can explicitly set the flexiform
object by calling the **setFlexiFormObject** method on your controller,  or by passing 
an _Identifer_ to **$FlexiForm**.

Form Identifiers are defined in the  _Settings_ tab on flexiforms. The
identifier is also used in post URLs for easy tacking of form submissions in
_server logs_ and _analytics_.

```html
<!-- .ss template -->

<div class="page-content">
  $FlexiForm('newsletter_form')    
</div>
```


### Shortcodes

Alternately, you can use the **[flexiform]** [shortcode](http://doc.silverstripe.org/framework/en/reference/shortcodes)
in content areas. This is especially useful for controlling placement of 
a form inside existing content.

Optionally pass a _Form Identifier_ through the ID paramater. 

```
Some WYSIWYG Content

Default Form: <br /> [FlexiForm]

Explicit Form: <br /> [flexiform id=registration_form]

```


### Templates, Custom Form Classes

By default, flexiform uses _Form.ss_ to render the form. You can change the template by

* Simple Means: Adding a **FlexiForm.ss** to your theme

* Powerful Means: Provide an alternate form class via **$flexiform_form_class** 

```php
class Event extends DataObject
{
    private static $extensions = array(
        'FlexiFormExtension'
    );
    
    private static $flexi_form_class = 'EventForm';

}

// attempts to use EventForm.ss by default, falling back to Form.ss 
class EventForm extends FlexiForm {
    
    // optional: provide a specific template
    // public function getTemplate() { return 'EventSpecificTemplate'; } 
}

```


Configuration
=============

Most configuration is accomplished through the CMS -- however you can further 
tailor behavior through subclassing (protected properties, getters, and setters)
and [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration).

See [docs/CONFIGURATION.md](docs/CONFIGURATION.md) for documentation and examples.

