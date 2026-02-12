# Copyright (c) 2025 Oakleigh Davies. All rights reserved.
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.

<!DOCTYPE html>
<html data-bs-theme="light">
    <head>
        <title>IDEA. A Home Furniture Company</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    </head>
    <body>
        <section>
            <figure class="text-center m-1 p-1">
                <h1>IDEA.</h1>
                <h5><i>Table View</i></h5>
            </figure>
            <?php
                if (oci_connect("x9b80", "x9b80")) {
                    print "<section>
                                <p class='p-3 ms-3 me-3 text-success-emphasis bg-success-subtle border border-success rounded-3 d-flex'>
                                    <i>Connection Successful!</i>
                                </p>
                            </section>";
                } else {
                    print "<section>
                                <p class='p-3 text-danger-emphasis bg-danger-subtle border border-danger-subtle rounded-3'>
                                    Connection Failed!
                                </p>
                            </section>";
                }
            ?>
            <div class="d-flex align-items-center my-3">
                <hr class="flex-grow-1">
                    <span class="mx-2 text-muted"><i>Table Selection</i></span>
                <hr class="flex-grow-1">
            </div>
            <div class="d-flex justify-content-center">
                <div class='btn-group dropend'>
                    <button type='button' class='btn btn-primary dropdown-toggle ps-3 pe-3 ms-3 me-3' data-bs-toggle='dropdown' aria-expanded='false'>
                        Table Select
                    </button>
                    <form action="" method="POST">
                        <ul class='dropdown-menu dropdown-menu-end'>
                            <li><button class="dropdown-item" type="submit" name="table" value="Addressing">Addressing</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="CollectionOrder">Collection Order</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="CollectionProduct">Collection Product</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Customer">Customer</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="DeliveryOrder">Delivery Order</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="DeliveryProduct">Delivery Product</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Department">Department</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Employee">Employee</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Invoice">Invoice</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Order">Order</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Payment">Payment</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Product">Product</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="ProductQuantity">Product Quantity</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Supplier">Supplier</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Supply">Supply</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="Store">Store</button></li>
                            <li><button class="dropdown-item" type="submit" name="table" value="ProductQuantityOnHand">Quantity On Hand</button></li>
                        </ul>
                    </form>
                    <button type="button" onclick="window.location.href='https://teach.cs.keele.ac.uk/users/x9b80/databaseSystems/updatingTables.php';" class="btn btn-dark ps-3 pe-3 ms-3 me-3 rounded-2">Table Updating Mode</button>
                </div>
                <?php
                    $tableToUse = $_POST['table'] ?? 'Store';
                    echo "<p class='ps-2 ms-2 me-3 mb-0 pt-2'>Current Table: <i><b>'$tableToUse'</b></i>.
</p>";
                ?>
            </div>
            <div class="d-flex align-items-center my-3">
                <hr class="flex-grow-1">
                    <span class="mx-2 text-muted"><i>Search Parameters</i></span>
                <hr class="flex-grow-1">
            </div>
        </section>
        <?php include "grabTables.php"; ?>
    </body>
</html>