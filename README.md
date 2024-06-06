Csd, acronym of "CodeIgniter Spa by DaSCoS", is a framework for SPA applications (Single Page Application: more info) based on the CodeIgniter 3.1.0 framework, which we have extended with the integration of the xajax framework to manage the logical part that creates the Spa, of jquery for advanced features and functions and Bootstrap for the graphic part. Csd framework allows you to create rich web applications that offer a smooth and interactive user experience.

CodeIgniter is a lightweight and flexible PHP framework that focuses on development speed and simplicity. It uses the Model-View-Controller (MVC) pattern to separate presentation logic from business logic. In this way, robust and maintainable web applications can be created.

Xajax, on the other hand, is an AJAX framework for PHP that makes it easy to implement AJAX functionality in a web application. This framework allows you to make AJAX calls to the server without having to rewrite the PHP code. Additionally, xajax offers a number of useful features, such as data validation and error handling.

Bootstrap is a CSS and JavaScript based front-end framework that provides pre-built and responsive UI components. With Bootstrap, you can quickly create an attractive and easy-to-use user interface, without having to write a lot of CSS or JavaScript code.

JQuery is a lightweight and fast JavaScript library that makes it easy to manipulate the DOM and interact with the server via AJAX. With jQuery, you can write clean and compact JavaScript code, saving time and effort.

Together, these frameworks allow you to build a powerful and interactive single-page web application with an attractive and responsive user interface. With xajax integration, you can use AJAX features to improve user experience and make your application more efficient. Also, thanks to the use of Bootstrap and jQuery, you can quickly create a high-quality user interface without having to write a lot of code.

In summary, Csd is a fork of CodeIgniter 3.1.0 that offers a rich and interactive user experience thanks to xajax integration and the use of Bootstrap and jQuery. With Csd, you can create attractive and efficient single page web applications quickly and easily.

############################################

## Basic concepts for the construction of tables suitable for CSD (state of the art).


The names of the tables to be managed, therefore not the system ones, are preferable to all be in the "PascalCase" or "camelCase" format.

The names of the fields of the tables that you will create and that you want to manage with Csd must be all lowercase or "camelCase".

There must be a unique primary key, possibly of type autoincrement, on a single field (always use "id").

To relate a field of a table to another table, it is necessary to assign the name "id_tablename" to the main field, where "tablename" is the full name of the table on which to perform the joins.

**For example:**
>CREATE TABLE `exampleTable` (
>
> `id` int(10) UNSIGNED NOT NULL COMMENT 'n,o:1',
>
> `id_joinedTable` int(10) UNSIGNED NOT NULL COMMENT 'n,o:2',
>
> `name` tinytext DEFAULT NULL COMMENT 't,o:4',
>
>.....
>
>);

"id_joinedTable" therefore represents a link to the table "joinedTable" on the corresponding "id" field.

### In the comments of the table columns it is possible to specify some information that helps the system to model itself correctly.

+ If the field is numeric, enter "n" in the comment.
+ If the field is text, enter "t".
+ If the field is of type timestamp (understood as standard unixTimeStamp, i.e. an integer representing a date), enter "d".
+ If you want that particular column to be hidden in Csd's record view, enter "h".
+ If you want that particular column to be placed in a different order than it is in the database, enter "o:x", where "x" represents its position.
+ If the field represents the value you want to display when a join relationship exists, i.e. in the "select" elements of the graphic, enter "s".

**For example:**
>CREATE TABLE `exampleTable` (
>
> `id` int(10) UNSIGNED NOT NULL COMMENT 'n,o:1',
>
> `id_joinedTable` int(10) UNSIGNED NOT NULL COMMENT 'n,o:2',
>
> `name` tinytext DEFAULT NULL COMMENT 't,o:4',
>
> `unique_code` tinytext NOT NULL COMMENT 's,t,o:3',
>
> `insert_date` int(10) UNSIGNED NOT NULL COMMENT 'd,o:6',
>
> `update_date` int(10) UNSIGNED NOT NULL COMMENT 'd,o:5',
>
> `active` tinyint(1) DEFAULT 0 COMMENT 'n')

This indicates that:
- "id" will be in position 1 and is numeric.
- "id_joinedTable" will be in position 2 and is of numeric type: since its name is "id_joinedTable", the system understands that its values ​​come from a joined table and therefore, when the graphic is created, the system performs a join on the table "joinedTable" and will show the value of the column of "joinedTable" which has "s" in its comment.
- "name" is of text type and goes in position 4 (without the indication of "o:4", in the graphics it would be found immediately after "id_joinedTable")
- "unique_code" is of text type, it must be positioned as the 3rd element and, in a possible table that has a link with "exampleTable", its value will be used in the construction of the Select graphics.
- "insert_date" is of type date (in unixTimeStamp format) and must be positioned as the 6th element

Once cloned and configured a WebServer, just visit your WebAddress and go trought the installation steps
