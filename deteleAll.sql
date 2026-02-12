-- Copyright (c) 2025 Oakleigh Davies. All rights reserved.

-- This work is licensed under a Creative Commons Attribution-NonCommercial 4.0 International License (CC BY-NC 4.0).
-- You should have received a copy of the license along with this work. 
-- If not, see <https://creativecommons.org/licenses/by-nc/4.0/>.

BEGIN
   FOR t IN (SELECT table_name FROM user_tables) LOOP
      EXECUTE IMMEDIATE 'DROP TABLE "' || t.table_name || '" CASCADE CONSTRAINTS';
   END LOOP;
END;
/