-- Copyright (c) 2025 Oakleigh Davies. All rights reserved.

-- This work is licensed under a Creative Commons Attribution-NonCommercial 4.0 International License (CC BY-NC 4.0).
-- "This work", this ONLY applies to the work not referenced in any way, and solely created by the copyright holder. Referenced work is referenced below, respective licensing and rights reserved by the indeviduals/companies respectively.
-- You should have received a copy of the license along with this work. 
-- If not, see <https://creativecommons.org/licenses/by-nc/4.0/>.

-- foreign & primary key from lecture slides
-- UNIQUE from the essentials textbook reading - page 242 chapter 7 - Thomas Connolly - PUBLISHER Pearson Education UK - 2015-01-12 - eidition 6 - 2025 Nov 26 - https://ebookcentral.proquest.com/lib/keeleuni/reader.action?docID=5136720&c=UERG&ppg=244
-- GENERATED ALWAYS AS IDENTITY from ...
-- REGEXP_LIKE from stck overflow - 26 Nov 2025 - Checking the Input value is Number or not in oracle - https://stackoverflow.com/questions/75317632/checking-the-input-value-is-number-or-not-in-oracle
--                                -      ""     - SQL Language Reference - Pattern-matching Conditions - https://docs.oracle.com/en/database/oracle/oracle-database/19/sqlrf/Pattern-matching-Conditions.html#GUID-D2124F3A-C6E4-4CCA-A40E-2FFCABFD8E19
--                                -      ""     -           ""           - Multilingual Regular Expression Syntax - https://docs.oracle.com/en/database/oracle/oracle-database/19/sqlrf/Pattern-matching-Conditions.html#GUID-D2124F3A-C6E4-4CCA-A40E-2FFCABFD8E19
--                                -      ""     -           ""           - Perl-influenced Extensions in Oracle Regular Expressions - https://docs.oracle.com/en/database/oracle/oracle-database/19/sqlrf/Perl-influenced-Extensions-in-Oracle-Regular-Expressions.html
--                                -      ""     - Apr 2024 -     Stack Overflow     - What characters are allowed in an email address? - https://stackoverflow.com/questions/2049502/what-characters-are-allowed-in-an-email-address

CREATE TABLE Addressing
(
    addressID NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    unitNameOrNo VARCHAR2(50) NOT NULL,
    street VARCHAR2(50) NOT NULL,
    city VARCHAR2(50) NOT NULL,
    county VARCHAR2(50) NOT NULL,
    postcode VARCHAR2(8) CHECK ( -- to allow for blank space in the middle, if applicable
        REGEXP_LIKE(postcode, '^[A-Za-z]{1,2}[0-9]{1,2}[A-Za-z]{0,1}\s?[0-9]{1}[A-Za-z]{2}$') -- 1 or 2 characters, 1 or 2 digits, if not 2 digits may have 1 extra character, 1 number, then 2 digits
                                                                                              -- "Regular Expression syntax"
    ) NOT NULL,
    PRIMARY KEY (addressID)
);

CREATE TABLE Supplier
(
    supplierID NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    supplierName VARCHAR2(30) NOT NULL,
    UNIQUE(supplierName), -- prevents redundancy, no need to list same supplier twice
    PRIMARY KEY (supplierID)
);

CREATE TABLE Store
(
    storeNo NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    storeAddressID NUMBER NOT NULL, -- {FK}
    storeName VARCHAR2(30) NOT NULL,
    storePhoneNo VARCHAR2(20) NOT NULL, -- national max length is 12 and 11 (mob/landline) incl. first zero
    UNIQUE (storeName, storePhoneNo), -- cannot have same store under different ID
    PRIMARY KEY (storeNo),
    FOREIGN KEY (storeAddressID) REFERENCES Addressing(addressID) ON DELETE CASCADE
);

CREATE TABLE Customer
(
    customerID NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    customerFName VARCHAR2(30) NOT NULL,
    customerLName VARCHAR2(30) NOT NULL,
    customerPhoneNo VARCHAR2(20) NOT NULL,
    customerEmail VARCHAR2(50) CHECK (
        REGEXP_LIKE(customerEmail, '^[A-Za-z0-9!#$%&''*+-/=?^_`{|}~]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$') -- for normal email cases (exclueds quoted emails, but these are uncommon for companies and regular customers)
    ) NOT NULL,
    UNIQUE (customerPhoneNo, customerEmail), -- more than one person can have the same names, and same person can have more than one account, so prevents redundancy
    PRIMARY KEY (customerID)
);

CREATE TABLE Employee
(
    employeeNo NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    storeNo NUMBER NOT NULL, -- {FK}
    employeeFName VARCHAR2(30) NOT NULL,
    employeeLName VARCHAR2(30) NOT NULL,
    employeeEmail VARCHAR2(50) CHECK (
        REGEXP_LIKE(employeeEmail, '^[A-Za-z0-9!#$%&''*+-/=?^_`{|}~]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$') -- for normal email cases (exclueds quoted emails, but these are uncommon for companies and regular customers)
    ) NOT NULL,
    employeeNINo VARCHAR2(13) CHECK (
        REGEXP_LIKE(employeeNINo, '^[A-Za-z]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[A-Za-z]$') -- edited version from https://stackoverflow.com/questions/10204378/regular-expression-to-validate-uk-national-insurance-number
    ) NOT NULL,
    employeeDOB DATE NOT NULL,
    employeeSex VARCHAR2(6) CHECK (employeeSex IN ('male','female','Male','Female')), -- may be empty if didnt want to say
    employeeSalary NUMBER(10, 2) NOT NULL, -- number of 10 digits with a precision of 2.d.p for pence
    employeeStartDate DATE NOT NULL,
    PRIMARY KEY (employeeNo),
    FOREIGN KEY (storeNo) REFERENCES Store(storeNo) ON DELETE CASCADE
);

-- collection employee
CREATE TABLE CollectionEmployee
(
    collectionEmployeeNo NUMBER, -- {PK}{FK}
    PRIMARY KEY (collectionEmployeeNo),
    FOREIGN KEY (collectionEmployeeNo) REFERENCES Employee(employeeNo) ON DELETE CASCADE
);

-- delivery employee
CREATE TABLE DeliveryEmployee
(
    deliveryEmployeeNo NUMBER, -- {PK}{FK}
    PRIMARY KEY (deliveryEmployeeNo),
    FOREIGN KEY (deliveryEmployeeNo) REFERENCES Employee(employeeNo) ON DELETE CASCADE
);

CREATE TABLE Department -- unique department, many procudcts could be in said department
(
    departmentID NUMBER GENERATED ALWAYS AS IDENTITY,
    departmentName VARCHAR2(30) NOT NULL,
    departmentDescription VARCHAR2(100), -- description technically doesnt need to exist on all departments
    PRIMARY KEY (departmentID)
);

CREATE TABLE Product
(
    productSKU NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    departmentID NUMBER NOT NULL, -- {FK}
    productName VARCHAR2(50),
    price NUMBER(8, 2) -- to 2.d.p, and 6 digit long number (one product isnt likely to cost over £999,999.99)
        CHECK (price >= 0) NOT NULL, -- price may be zero if on promotion, but cannot be -ve
    productDescription VARCHAR2(100), -- description technically doesnt need to exist on all products
    PRIMARY KEY (productSKU),
    FOREIGN KEY (departmentID) REFERENCES Department(departmentID) ON DELETE CASCADE
);

-- delivery product
CREATE TABLE DeliveryProduct
(
    productSKU NUMBER, -- {PK, FK} -- unique incrimenting PRIMARY KEY
    heightDimension FLOAT CHECK (heightDimension > 0) NOT NULL,
    widthDimension FLOAT CHECK (widthDimension > 0) NOT NULL,
    lengthDimension FLOAT CHECK (lengthDimension > 0) NOT NULL,
    PRIMARY KEY (productSKU),
    FOREIGN KEY (productSKU) REFERENCES Product(productSKU) ON DELETE CASCADE
);

-- collection product
CREATE TABLE CollectionProduct
(
    productSKU NUMBER, -- {PK, FK} -- GENERATED ALWAYS AS IDENTITY -- unique incrimenting PRIMARY KEY
    "WEIGHT" FLOAT CHECK ("WEIGHT" > 0) NOT NULL,
    PRIMARY KEY (productSKU),
    FOREIGN KEY (productSKU) REFERENCES Product(productSKU) ON DELETE CASCADE
);

CREATE TABLE Supply
(
    supplyID NUMBER GENERATED ALWAYS AS IDENTITY, -- {PK}
    supplierID NUMBER, -- {FK}.
    storeNo NUMBER, -- {FK}.
    productSKU NUMBER, -- {FK}.
    PRIMARY KEY (supplyID),
    FOREIGN KEY (supplierID) REFERENCES Supplier(supplierID) ON DELETE CASCADE,
    FOREIGN KEY (storeNo) REFERENCES Store(storeNo) ON DELETE CASCADE,
    FOREIGN KEY (productSKU) REFERENCES Product(productSKU) ON DELETE CASCADE
);

CREATE TABLE ProductQuantityOnHand
(
    qOH_ID NUMBER GENERATED ALWAYS AS IDENTITY, -- {PK}.
    productSKU NUMBER, -- {FK}.
    storeNo NUMBER, -- {FK}.
    quantityOnHand NUMBER DEFAULT 0 CHECK (quantityOnHand >= 0) NOT NULL, -- cannot have -ve quantity of a product
    PRIMARY KEY (qOH_ID),
    FOREIGN KEY (productSKU) REFERENCES Product(productSKU) ON DELETE CASCADE,
    FOREIGN KEY (storeNo) REFERENCES Store(storeNo) ON DELETE CASCADE
);

CREATE TABLE Payment
(
    paymentID NUMBER GENERATED ALWAYS AS IDENTITY,
    longCardNo VARCHAR(19) CHECK (
        REGEXP_LIKE(longCardNo, '^\d{4}\s?\d{4}\s?\d{4}\s?\d{4}$')
    ) NOT NULL, -- dont get -ve cardnumbers
    expiryDate DATE NOT NULL,
    securityCode NUMBER(4) NOT NULL, -- usually 3, but 4 is to allow for AMEX (would usually be stored encrypted)
    UNIQUE(longCardNo, expiryDate, securityCode), -- you cant have two of exactly the same card, so this prevents 
    PRIMARY KEY (paymentID)
);

CREATE TABLE "Order"
(
    orderNo NUMBER GENERATED ALWAYS AS IDENTITY, -- unique incrimenting PRIMARY KEY
    billingAddressID NUMBER NOT NULL, -- {FK}
    customerID NUMBER NOT NULL, -- {FK}.
    totalPrice NUMBER(36, 2) -- to 2.d.p, and 36 digit long number (ORACLE DBMS' max limit)
        CHECK(totalPrice >= 0) NOT NULL, -- -ve total cannot logically occur, so denies it
    PRIMARY KEY (orderNo),
    FOREIGN KEY (billingAddressID) REFERENCES Addressing(addressID) ON DELETE CASCADE,
    FOREIGN KEY (customerID) REFERENCES Customer(customerID) ON DELETE CASCADE -- maintains referential integrity
);

-- delivery "Order"
CREATE TABLE DeliveryOrder 
(
    deliveryOrderNo NUMBER, -- {PK}{FK}
    deliveryAddressID NUMBER NOT NULL, -- {FK}
    storeNo NUMBER NOT NULL, -- {FK}
    employeeNo NUMBER NOT NULL, -- {FK}
    totalWidthIN NUMBER NOT NULL,
    totalHeightIN NUMBER NOT NULL,
    totalLengthIN NUMBER NOT NULL,
    CHECK (totalHeightIN > 0 AND totalWidthIN > 0 AND totalLengthIN > 0),
    PRIMARY KEY (deliveryOrderNo),
    FOREIGN KEY (deliveryOrderNo) REFERENCES "Order"(orderNo) ON DELETE CASCADE,
    FOREIGN KEY (deliveryAddressID) REFERENCES Addressing(addressID) ON DELETE CASCADE,
    FOREIGN KEY (storeNo) REFERENCES Store(storeNo) ON DELETE CASCADE,
    FOREIGN KEY (employeeNo) REFERENCES Employee(employeeNo) ON DELETE CASCADE
);

-- collection "Order"
CREATE TABLE CollectionOrder 
(
    collectionOrderNo NUMBER, -- {PK}{FK}
    storeNo NUMBER NOT NULL, -- {FK}
    employeeNo NUMBER NOT NULL, -- {FK}
    totalWeightKG FLOAT CHECK (totalWeightKG > 0) NOT NULL,
    collectionArrangement VARCHAR2(200) NOT NULL, -- always needs a collection arrangement
    PRIMARY KEY (collectionOrderNo),
    FOREIGN KEY (collectionOrderNo) REFERENCES "Order"(orderNo) ON DELETE CASCADE,
    FOREIGN KEY (storeNo) REFERENCES Store(storeNo) ON DELETE CASCADE,
    FOREIGN KEY (employeeNo) REFERENCES Employee(employeeNo) ON DELETE CASCADE
);

CREATE TABLE ProductQuantity
(
    orderNo NUMBER, -- {PK}{FK}.
    productSKU NUMBER NOT NULL, -- {FK}.
    productQuantity NUMBER CHECK (productQuantity >= 0) NOT NULL, -- you cant have -ve quantities of items
    PRIMARY KEY (orderNo, productSKU),
    FOREIGN KEY (orderNo) REFERENCES "Order"(orderNo) ON DELETE CASCADE,
    FOREIGN KEY (productSKU) REFERENCES Product(productSKU) ON DELETE CASCADE -- maintains referential integrity
);

CREATE TABLE Invoice
(
    invoiceNo NUMBER GENERATED ALWAYS AS IDENTITY,
    orderNo NUMBER NOT NULL, -- {FK} -- only one invoice per order
    paymentID NUMBER NOT NULL, -- {FK} -- may have multiple payment methods
    orderDate DATE NOT NULL,
    datePaid DATE, -- may be null as may not be paid yet
    UNIQUE (orderNo),
    PRIMARY KEY (invoiceNo),
    FOREIGN KEY (orderNo) REFERENCES "Order"(orderNo),
    FOREIGN KEY (paymentID) REFERENCES Payment(paymentID)
);

CREATE OR REPLACE TRIGGER dateCheckPayment -- to prevent bad dates
    BEFORE INSERT OR UPDATE ON Payment
    FOR EACH ROW -- SQL 'for' loop as such
    BEGIN
    IF :NEW.expiryDate < SYSDATE THEN -- so for if date (and time, but not used here) is older than same day
        RAISE_APPLICATION_ERROR(-20001, 'Expiry date cannot be before: ' || TO_CHAR(SYSDATE, 'DD-MM-YY')); -- raising manual error
    END IF;
END;
/
