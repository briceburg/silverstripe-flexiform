<?php

class FlexiFormHTMLTextHandlerSetting extends FlexiFormHandlerSetting {

    private static $casting = array(
        'Value' => 'HTMLText'
    );


    public function getCMSField($name, $title = null)
    {
        return new HtmlEditorField($name, $title);
    }

}