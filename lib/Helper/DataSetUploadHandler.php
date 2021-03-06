<?php

namespace Xibo\Helper;

use Exception;
use Xibo\Exception\AccessDeniedException;
use Xibo\Factory\DataSetFactory;

class DataSetUploadHandler extends BlueImpUploadHandler
{
    protected function handle_form_data($file, $index)
    {
        // Handle form data, e.g. $_REQUEST['description'][$index]
        $fileName = $file->name;

        Log::debug('Upload complete for ' . $fileName . '.');

        // Upload and Save
        try {

            // Authenticate
            /* @var \Xibo\Controller\Base $controller */
            $controller = $this->options['controller'];
            $dataSet = DataSetFactory::getById($this->options['dataSetId']);

            if (!$controller->getUser()->checkEditable($dataSet))
                throw new AccessDeniedException();

            // We are allowed to edit - pull all required parameters from the request object
            $overwrite = Sanitize::getCheckbox('overwrite');
            $ignoreFirstRow = Sanitize::getCheckbox('ignorefirstrow');

            Log::debug('Options provided - overwrite = %d, ignore first row = %d', $overwrite, $ignoreFirstRow);

            // Enumerate over the columns in the DataSet and set a row value for each
            $spreadSheetMapping = [];

            foreach ($dataSet->getColumn() as $column) {
                /* @var \Xibo\Entity\DataSetColumn $column */
                if ($column->dataSetColumnTypeId == 1) {
                    // Has this column been provided in the mappings?
                    $spreadSheetColumn = isset($_REQUEST['csvImport_' . $column->dataSetColumnId]) ? $_REQUEST['csvImport_' . $column->dataSetColumnId][$index] : 0;

                    // If it has been left blank, then skip
                    if ($spreadSheetColumn != 0)
                        $spreadSheetMapping[($spreadSheetColumn - 1)] = $column->heading;
                }
            }

            // Delete the data?
            if ($overwrite == 1)
                $dataSet->deleteData();

            // Load the file
            ini_set('auto_detect_line_endings', true);

            $firstRow = true;

            $handle = fopen(Config::GetSetting('LIBRARY_LOCATION') . 'temp/' . $fileName, 'r');
            while (($data = fgetcsv($handle)) !== FALSE ) {

                $row = [];

                // The CSV file might have headings, so ignore the first row.
                if ($firstRow) {
                    $firstRow = false;

                    if ($ignoreFirstRow == 1)
                        continue;
                }

                for ($cell = 0; $cell < count($data); $cell++) {

                    // Insert the data into the correct column
                    if (isset($spreadSheetMapping[$cell])) {
                        $row[$spreadSheetMapping[$cell]] = $data[$cell];
                    }
                }

                $dataSet->addRow($row);
            }

            // Close the file
            fclose($handle);

            // Change the auto detect setting back
            ini_set('auto_detect_line_endings', false);

            // TODO: update list content definitions

            // Save the dataSet
            $dataSet->lastDataEdit = time();
            $dataSet->save(['validate' => false, 'saveColumns' => false]);

            // Tidy up the temporary file
            @unlink(Config::GetSetting('LIBRARY_LOCATION') . 'temp/' . $fileName);

        } catch (Exception $e) {
            $file->error = $e->getMessage();
        }
    }
}