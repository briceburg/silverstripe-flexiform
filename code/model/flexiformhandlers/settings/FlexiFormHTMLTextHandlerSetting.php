<?php

class FlexiFormHTMLTextHandlerSetting extends FlexiFormHandlerSetting
{

    private static $casting = array(
        'Value' => 'HTMLText'
    );

    public function getCMSField($name)
    {
        return new HtmlEditorField($name, null, $this->Value);
    }
}