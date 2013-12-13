<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * render accrual report as a csv
*/

    // headings
    foreach ($headings as $heading) {
        $this->Csv->addField($heading);
    }

    $this->Csv->endRow();

    foreach ($rows as $row) {
        $this->Csv->addField($row['name']);

        foreach ($columns as $column) {
            if (!$column['display']) {
                continue;
            }

            $columnName = $column['name'];

            if ($column['sites']) {
                foreach ($sites as $site) {
                    $siteName = $site['Site']['name'];
                    $this->Csv->addField($row[$columnName][$siteName]);
                }
            }

            if (!isset($row[$columnName]['total'])) {
                // total must be percentage
                $this->Csv->addField("{$row[$columnName]['totalPercent']}%");
            } else {
                $this->Csv->addField($row[$columnName]['total']);

                if (isset($row[$columnName]['totalPercent'])) {
                    $this->Csv->addField("{$row[$columnName]['totalPercent']}%");
                }
            }
        }

        $this->Csv->endRow();
    }

    $datetime = str_replace(' ', '_', date('Y-m-d'));
    echo $this->Csv->render("accrual.$datetime.csv");
?>
