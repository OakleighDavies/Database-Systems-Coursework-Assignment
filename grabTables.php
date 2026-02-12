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
    <style>
        #displayTable {
            max-width: 95%;
        }
    </style>
    <?php
        $authMarker = FALSE;
        $tableToUse = $_POST['table'] ?? 'Store';
        $columnToUse = $_POST['columnToUse'] ?? '';
        $columnSearch = $_POST['columnSearch'] ?? '';

        validation($tableToUse, $columnToUse);

        function validation ($tableToUse, $columnToUse) {
            // FIX: make $authMarker refer to outer variable
            global $authMarker;

            $allowedSQL = ["Addressing", "CollectionOrder", "CollectionProduct", "Customer", "DeliveryOrder", "DeliveryProduct",
                        "Department", "Employee", "Invoice", '"Order"', "Payment", "Product", "ProductQuantity", "Supplier", "Supply", "Store", "ProductQuantityOnHand"];
            
            $cleansePattern = '/^[A-Za-z0-9_"]+$/';
            
            if (preg_match($cleansePattern, $tableToUse) and in_array($tableToUse, $allowedSQL, true)) {
                $authMarker = TRUE; // FIX: set global
                fetchColumnHead($tableToUse, $columnToUse, $authMarker);
            } else {
                $authMarker = FALSE; // FIX: set global
                echo "<section>
                            <p class='p-3 ms-3 me-3 mb-0 mt-3 text-warning-emphasis bg-warning-subtle border border-bototm-0 border-warning rounded-top-3 d-flex'>
                                <i>Invalid Request! '" . htmlspecialchars($tableToUse) . "'</i>
                            </p>
                            <p class='p-3 ms-3 me-3 mb-3 mt-0 text-dark-emphasis bg-dark-subtle border border-top-0 border-dark rounded-bottom-3 d-flex'>
                                Ignoring Request.
                            </p>
                        </section>";
                fetchColumnHead($tableToUse, $columnToUse, $authMarker);
            }
        }

        function fetchColumnHead ($tableToUse, $columnToUse, $authMarker) {
            if ($authMarker && ($Connection = oci_connect("REDACTED", "REDACTED"))) {
                $sql = "SELECT * FROM $tableToUse"; # change per table
                $Statement = oci_parse($Connection, $sql);
                oci_execute($Statement);
                $numcols = oci_num_fields($Statement);
            }

            // sanitise for bad inputs for the sql later
            $columnToUse  = filter_input(INPUT_POST, 'columnToUse', FILTER_SANITIZE_STRING) ?? '';
            $columnSearch = filter_input(INPUT_POST, 'columnSearch', FILTER_SANITIZE_STRING) ?? '';

            echo '<section><div class="d-flex justify-content-center">
                    <form method="POST" action="" class="d-flex align-items-center">
                        <select class="form-select ps-3 pe-3 ms-3 me-3" name="columnToUse" aria-label="Select column">
                            <option value="">All Columns</option>';

            if (isset($Statement) && isset($numcols)) {
                for ($i = 1; $i <= $numcols; $i++) {
                    // echo column headings
                    $colName  = oci_field_name($Statement, $i);
                    $selected = ($columnToUse === $colName) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($colName) . '"' . $selected . '>' . htmlspecialchars($colName) . '</option>';
                    if ($i === 1) {
                        echo "<hr class='dropdown m-0 p-0'>";
                    }
                }
                oci_free_statement($Statement);
                oci_close($Connection);
            }

            echo '</select>
                        <input type="text" onfocus="this.value=\'\'" name="columnSearch" class="form-control me-3" placeholder="ITEM/KEY Value" value="' . htmlspecialchars($columnSearch) . '">
                        <input type="text" name="table" class="form-control me-3" value="' . htmlspecialchars($tableToUse) . '" readonly>
                        <button type="submit" class="btn btn-dark ps-3 pe-3 me-3">Search</button>
                    </form>
                </div></section>';
        }

        function fetchTable ($tableToUse, $columnToUse, $columnSearch, $authMarker) {
            // Program to read the contents of ITEM and display them in a table
            if ($Connection = oci_connect("REDACTED", "REDACTED")) {
                if (!empty($columnToUse) && !empty($columnSearch)) {
                    $sql = "SELECT * FROM {$tableToUse} WHERE {$columnToUse} = :search_value";
                    $Statement = oci_parse($Connection, $sql);
                    oci_bind_by_name($Statement, ":search_value", $columnSearch);
                } else if (empty($columnSearch) && !empty($columnToUse)) {
                    $sql = "SELECT {$columnToUse} FROM {$tableToUse}";
                    $Statement = oci_parse($Connection, $sql);
                } else {
                    $sql = "SELECT * FROM {$tableToUse}";
                    $Statement = oci_parse($Connection, $sql);
                }

                // Execute Oracle query
                oci_execute($Statement);
                $numcols = oci_num_fields($Statement);
                echo "<section><div class='d-flex justify-content-center'>
                        <table id='displayTable' class='mt-4 mb-0 table table-primary table-bordered table-hover table-responsive border border-2 border-black'><tr><thead>";
                for ($i = 1; $i <= $numcols; $i++) {
                    // echo column headings
                    $colname = oci_field_name($Statement, $i);
                    echo "<th scope='col'>" . htmlspecialchars($colname) . "</th>";
                }

                if ($authMarker === TRUE) {
                    $numrows = fetchContents ($Statement, $numcols);
                }
                echo "<div class='d-flex m-0 justify-content-center'><p class='text-center'><i>Retrieved $numcols columns and $numrows rows</i><br></p></div> 
                </section>";
                oci_free_statement($Statement);
                oci_close($Connection);
            } else {
                $error = var_dump(oci_error());
                echo '<section class="mt-4 mb-0"><p class="p-3 ms-3 me-3 mb-0 text-danger-emphasis bg-danger-subtle border border-danger rounded-3"><b>Fatal Error:</b> ' . htmlspecialchars($error['message']) . '</p></section>';
            } // end of if expression
        }

        echo '<section></div>
                        <div class="d-flex align-items-center my-3">
                            <hr class="flex-grow-1">
                                <span class="mx-2 text-muted"><i>Table View</i></span>
                            <hr class="flex-grow-1">
                        </div></section>';

        fetchTable($tableToUse, $columnToUse, $columnSearch, $authMarker);

        function fetchContents ($Statement, $numcols) {
            echo "</thead></tr>";
            // POST a row and echo it column by column
            $rows = 0; // COUNT rows for display
            while (oci_fetch($Statement)) {
                echo "<tr><tbody>";
                for ($i = 1; $i <= $numcols; $i++) {
                    $col = oci_result($Statement, $i);
                    echo "<td>" . htmlspecialchars($col ?? '') . "</td>";
                }
                $rows++;
            }
            echo "</tbody></tr></table></div>";
            return $rows; // RETURN number of rows fetched
        }
    ?>
</html>
