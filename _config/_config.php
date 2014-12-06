<?php

$flexiform_cb = function($params, $content, $parser, $tag, $info){
    $controller = Controller::curr();

    $identifier = null;

    foreach($params as $param => $value) {
        switch (strtolower($param)) {
            case 'id':
            case 'identifier':
                $identifier = $value;
                break;
        }
    }

    $content = $controller->FlexiForm($identifier);
    return (is_string($content)) ? $content : $content->forTemplate();
};

ShortcodeParser::get('default')->register('flexiform',$flexiform_cb);
ShortcodeParser::get('default')->register('FlexiForm',$flexiform_cb);
