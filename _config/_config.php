<?php

$flexiform_cb = function($params, $content, $parser, $tag, $info){
    $controller = Controller::curr();

    // did we pass a specific method name for fethcing the flexi object?
    //   e.g. [FlexiForm]myMethod[/FlexiForm]
    if(!empty($content) && method_exists($controller, $content)) {
        $flexi = $controller->$content();
        $controller->setFlexiFormObject($flexi);
    }

    $content = $controller->FlexiForm();
    return (is_string($content)) ? $content : $content->forTemplate();
};

ShortcodeParser::get('default')->register('flexiform',$flexiform_cb);
ShortcodeParser::get('default')->register('FlexiForm',$flexiform_cb);
