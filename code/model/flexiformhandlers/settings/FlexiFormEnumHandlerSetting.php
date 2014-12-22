<?php

class FlexiFormEnumHandlerSetting extends FlexiFormHandlerSetting {

    public function getCMSField($name)
    {

        $source = array();

        if($handler = $this->Handler()) {
            if($dbField = $handler->dbObject($name)) {
                $source = $dbField->enumValues();
            }
        }

        return new DropdownField($name,null,$source,$this->getValue());
    }

}