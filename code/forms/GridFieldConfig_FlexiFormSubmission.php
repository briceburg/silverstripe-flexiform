<?php

class GridFieldConfig_FlexiFormSubmission extends GridFieldConfig
{

    public function __construct($flexi)
    {
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent($sort = new GridFieldSortableHeader());
        $this->addComponent($filter = new GridFieldFilterHeader());
        $this->addComponent(new GridFieldDataColumns());
        $this->addComponent(new GridFieldViewButton());
        $this->addComponent(new GridFieldDeleteAction(false));
        $this->addComponent(new GridFieldDetailForm());
        $this->addComponent($export = new GridFieldExportButton());
        $this->addComponent($pagination = new GridFieldPaginator());

        $sort->setThrowExceptionOnBadDataType(false);
        $filter->setThrowExceptionOnBadDataType(false);
        $pagination->setThrowExceptionOnBadDataType(false);

        // add submission values to exports
        ///////////////////////////////////

        $export->setExportColumns($this->getCSVColumns($flexi));
    }

    private function getCSVColumns($flexi) {

        $columns = array(
            'SubmittedBy' => 'Submitted By',
            'IPAddress' => 'IP Address',
            'Created' => 'Created'
        );

        $sql = new SQLQuery();
        $sql->setFrom('FlexiFormSubmissionValue');
        $sql->setSelect('"FlexiFormSubmissionValue"."Name"');
        $sql->addLeftJoin('FlexiFormSubmission','"FlexiFormSubmissionValue"."SubmissionID" = "FlexiFormSubmission"."ID"');
        $sql->addWhere('"FlexiFormSubmission"."FlexiFormID" = ' . $flexi->ID);
        $sql->addWhere('"FlexiFormSubmission"."FlexiFormClass" = \'' . $flexi->class . '\'');
        $sql->setDistinct(true);

        foreach($sql->execute() as $row) {
            $columns['Values.' . $row['Name']] = $row['Name'];
        }

        return $columns;
    }
}

