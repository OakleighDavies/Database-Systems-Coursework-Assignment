<!--
Copyright (C) 2025 Oakleigh Davies.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org>.
-->


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            #displayTable {
                max-width: 95% !important;
                min-width: 95% !important;
            }
        </style>
    </head>
    <body>
        <?php
        $tableToUse = $_POST['table'] ?? 'Store';
        $tableToUse = trim($tableToUse, '"');

        $columToUse = $_POST['columnToUse'] ?? '';
        $columnUpdateValue = $_POST['columnUpdateValue'] ?? '';
        $primaryKeyToUse = $_POST['primaryKeyValue'] ?? '';

        validation($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue);

        function validation($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue) {
            $allowedSQL = ["Addressing", "CollectionOrder", "CollectionProduct", "Customer", "DeliveryOrder", "DeliveryProduct",
                        "Department", "Employee", "Invoice", '"Order"', "Payment", "Product", "ProductQuantity", "Supplier", "Supply", "Store", "ProductQuantityOnHand"];

            $cleansePattern = '/^[A-Za-z0-9_"]+$/';

            if (preg_match($cleansePattern, $tableToUse) && in_array($tableToUse, $allowedSQL, true)) {
                fetchColumnHead($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue);
            } else {
                echo "<section>
                        <p class='p-3 ms-3 me-3 mb-0 mt-3 text-warning-emphasis bg-warning-subtle border border-bototm-0 border-warning rounded-top-3 d-flex'>
                            <i>Invalid Request! '$tableToUse'</i>
                        </p>
                        <p class='p-3 ms-3 me-3 mb-3 mt-0 text-dark-emphasis bg-dark-subtle border border-top-0 border-dark rounded-bottom-3 d-flex'>
                            Ignoring Request.
                        </p>
                    </section>";
                fetchColumnHead($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue);
            }
        }

        function fetchColumnHead($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue) {
            if ($Connection = oci_connect("x9b80", "x9b80")) {
                $sql = "SELECT * FROM " . $tableToUse;
                $Statement = oci_parse($Connection, $sql);
                oci_execute($Statement);
                $numcols = oci_num_fields($Statement);

                $columToUse  = filter_input(INPUT_POST, 'columnToUse', FILTER_SANITIZE_STRING) ?? '';
                $columnUpdateValue = filter_input(INPUT_POST, 'columnUpdateValue', FILTER_SANITIZE_STRING) ?? '';

                // --- UPDATE FORM (unchanged)
                echo '<section>
                        <div class="d-flex justify-content-center">
                            <form method="POST" action="" class="d-flex align-items-center">
                                <select class="form-select ps-3 pe-3 ms-3 me-3" name="columnToUse" aria-label="Select column">
                                    <option value="">Select Column</option>';

                for ($i = 1; $i <= $numcols; $i++) {
                    $colName  = oci_field_name($Statement, $i);
                    $selected = ($columToUse === $colName) ? ' selected' : '';
                    
                    if ($i === 1) {
                        echo '<option value="' . htmlspecialchars($colName) . '"' . $selected . '" disabled>' . htmlspecialchars($colName) . '</option>';
                        echo '<hr class="dropdown">';
                    } else {
                        echo '<option value="' . htmlspecialchars($colName) . '"' . $selected . '>' . htmlspecialchars($colName) . '</option>';
                    }
                }

                echo '</select>';

                // PK dropdown
                $rowStmt = oci_parse($Connection, $sql);
                oci_execute($rowStmt);
                echo '<select class="form-select ps-3 pe-3 me-3" name="primaryKeyValue" aria-label="Select PK">
                        <option value="">Select PK Value</option>';
                while (($row = oci_fetch_array($rowStmt, OCI_NUM | OCI_RETURN_NULLS)) !== false) {
                    $val = $row[0];
                    if ($val === null) continue;
                    $valueAttr = (string)$val;
                    echo '<option value="' . $valueAttr . '">' . htmlspecialchars($valueAttr) . '</option>';
                }
                echo '</select>';

                echo '<input type="text" onfocus="this.value=\'\'" name="columnUpdateValue" class="form-control me-3" placeholder="UPDATE Value" value="' . htmlspecialchars($columnUpdateValue) . '">
                        <input type="text" name="table" class="form-control me-3" value="' . htmlspecialchars($tableToUse) . '" readonly>
                        <button type="submit" class="btn btn-warning ps-3 pe-3 me-3">Update</button>
                    </form></div></section>';

                echo '<section>
                        <div class="d-flex align-items-center my-3">
                            <hr class="flex-grow-1">
                            <span class="mx-2 text-muted"><i>Insert/Delete Parameters</i></span>
                            <hr class="flex-grow-1">
                        </div></section>';

                // --- INSERT FORM
                echo '<section>
                        <div class="d-flex justify-content-center table-responsive w-100 flex-grow-1">
                            <form method="POST" action="" id="displayTable" class="d-flex flex-wrap justify-content-center">';

                // Fetch columns metadata, skip PK (first column)
                $columns = [];
                for ($i = 2; $i <= $numcols; $i++) {
                    $colName = oci_field_name($Statement, $i);
                    $columns[] = ['name' => $colName]; 
                }

                foreach ($columns as $column) {
                    echo '<input type="text" name="' . htmlspecialchars($column['name']) . '" class="form-control mt-2 mb-2" placeholder="' . htmlspecialchars($column['name']) . '">';
                }

                echo '<button type="submit" class="btn btn-success ps-3 pe-3 mt-2 mb-2">Insert</button>
                    </form>
                </div></section>';
                
                // insert scripting
                if (!empty($tableToUse)) {
                    $columnsToUse = [];
                    $valuesToUse  = [];

                    foreach ($_POST as $col => $val) { // after $_POST for each values sent, value
                        $columnsToUse[] = $col; // column to use
                        $valuesToUse[]  = $val; // values to insert
                    }

                    if (!empty($columnsToUse)) { // if columns not empty
                        $columnsStr = implode(', ', $columnsToUse); // from https://www.w3schools.com/php/func_string_implode.asp // column names
                        $placeholders = ':' . implode(', :', $columnsToUse); // :PLACEHGOLDER NAME // column name placeholders for binding

                        $sqlInsert = "INSERT INTO {$tableToUse} ({$columnsStr}) VALUES ({$placeholders})";
                        $insertStatment = oci_parse($Connection, $sqlInsert);

                        foreach ($columnsToUse as $i => $col) {
                            $val = trim((string)$valuesToUse[$i]);
                            if (is_numeric($val) ) {
                                oci_bind_by_name($insertStatment, ':' . $col, $val, -1, SQLT_NUM);
                            } else {
                                oci_bind_by_name($insertStatment, ':' . $col, $val, -1, SQLT_CHR);
                            }
                        }

                        $ok = @oci_execute($insertStatment, OCI_NO_AUTO_COMMIT);
                        // $ok = oci_execute($insertStatment, OCI_NO_AUTO_COMMIT);
                        if ($ok) {
                            oci_commit($Connection);
                            echo '<section class="mt-2"><p class="p-3 ms-3 me-3 mb-0 mt-3 text-success-emphasis bg-success-subtle border border-success rounded-3"><i>Insert Successful!</i></p></section>';
                        } else {
                            $error = oci_error($insertStatment);
                            oci_rollback($Connection);
                            echo '<section class="mt-2"><p class="p-3 ms-3 me-3 mb-0 mt-3 text-danger-emphasis bg-danger-subtle border border-danger rounded-3"><i>Insert Failed: ' . htmlspecialchars($error['message']) . '</i></p></section>';
                        }
                        oci_free_statement($insertStatment);
                    }
                }

                // Free PK dropdown statement
                oci_free_statement($rowStmt);
            }

            fetchTable($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue);
        }


        function fetchTable($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue) {
            // var_dump($tableToUse, $primaryKeyToUse, $columToUse, $columnUpdateValue); // for testing purposes only

            if ($Connection = oci_connect("x9b80", "x9b80")) {

                if (!empty($columnUpdateValue) && !empty($primaryKeyToUse)) {

                    if (empty($columToUse)) {
                        echo '<section><div><p class="ps-3 mt-3 pe-3 ms-3 me-3 mb-0">Bad Input! Fill all Fields</p></div></section>';
                        echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-danger-emphasis bg-danger-subtle border border-danger rounded-3 d-flex"><i>Update Failed!</i></p></section>';
                    } else {

                        if (!is_numeric($primaryKeyToUse)) {
                            die('<section><p class="p-3 ms-3 me-3 mb-0 text-danger-emphasis bg-danger-subtle border border-danger rounded-3">Invalid Primary Key: must be numeric.</p></section>');
                        }

                        $pkColumnSql = "SELECT * FROM {$tableToUse}";
                        $pkStmt = oci_parse($Connection, $pkColumnSql);
                        oci_execute($pkStmt);
                        $primaryKeyColumnName = oci_field_name($pkStmt, 1);
                        oci_free_statement($pkStmt);

                        $sql = "UPDATE {$tableToUse} SET {$columToUse} = :val WHERE {$primaryKeyColumnName} = :pk";
                        $rep = oci_parse($Connection, $sql);

                        $pkVal = (float)$primaryKeyToUse; // cast to float for Oracle NUMBER
                        oci_bind_by_name($rep, ':pk', $pkVal, -1, SQLT_CHR);

                        $valInput = trim((string)$columnUpdateValue);
                        if ($valInput === '') {
                            oci_bind_by_name($rep, ':val', $valInput, -1, SQLT_CHR);
                        } elseif (is_numeric($valInput)) {
                            $valNum = 0 + $valInput;
                            oci_bind_by_name($rep, ':val', $valNum, -1, SQLT_NUM);
                        } else {
                            oci_bind_by_name($rep, ':val', $valInput, -1, SQLT_CHR);
                        }

                        $ok = @oci_execute($rep, OCI_NO_AUTO_COMMIT); // for standard runtime
                        // $ok = oci_execute($rep, OCI_NO_AUTO_COMMIT); // for testing purposes, including error and line number

                        if ($ok) {
                            $affected = oci_num_rows($rep);
                            if ($affected === 1) {
                                oci_commit($Connection);
                                echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-success-emphasis bg-success-subtle border border-success rounded-3"><i>Update Successful — 1 row updated.<i></p></section>';
                            } else {
                                oci_rollback($Connection);
                                echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-warning-emphasis bg-warning-subtle border border-warning rounded-3"><i>Bad Input! ' . htmlspecialchars((string)$affected) . ' rows changed.</i></p></section>';
                            }
                        } else {
                            $error = oci_error($rep);
                            oci_rollback($Connection);
                            echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-danger-emphasis bg-danger-subtle border border-danger rounded-3"><i>Update Failed! ' . htmlspecialchars($error['message']) . '</i></p></section>';
                        }

                        oci_free_statement($rep);
                    }
                }

                // Display the table
                $sql = "SELECT * FROM {$tableToUse}";
                $Statement = oci_parse($Connection, $sql);
                oci_execute($Statement);
                $numcols = oci_num_fields($Statement);

                echo '<section>
                        <div class="d-flex justify-content-center table-responsive w-100 flex-grow-1">
                            <table id="displayTable" class="mt-4 mb-4 table table-primary table-bordered table-hover border border-2 border-black">
                            <thead><tr>';
                            
                for ($i = 1; $i <= $numcols; $i++) {
                    $colname = oci_field_name($Statement, $i);
                    echo "<th scope=\"col\">" . htmlspecialchars($colname) . "</th>";
                }

                echo '</tr></thead><tbody>';

                while (($row = oci_fetch_array($Statement, OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS)) !== false) {
                    echo '<tr>';
                    for ($i = 1; $i <= $numcols; $i++) {
                        $colName = oci_field_name($Statement, $i);
                        $val = $row[$colName] ?? '';
                        echo '<td>' . htmlspecialchars($val) . '</td>';
                    }
                    echo '</tr>';
                }

                echo '        </tbody></table>
                        </div>
                    </section>';
            } else {
                $error = var_dump(oci_error());
                echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-danger-emphasis bg-danger-subtle border border-danger rounded-3"><b>Fatal Error:</b> ' . htmlspecialchars($error['message']) . '</p></section>';
            }
        }
        ?>
    </body>
</html>
