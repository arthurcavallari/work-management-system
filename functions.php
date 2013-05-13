<?php
require_once('dbconn.php'); 
/* require/include do the same thing, except that if an error occurs on the file when you use require,  it doesn't allow the "page" to continue 
 * require_once/include_once simply checks if the file has already been included... used to avoid errors of redelaration of functions, etc
 */

include('includes/employers.php');
include('includes/worked_hours.php');
include('includes/payments.php');
include('navigation.php'); // class used to implement the paging system

/* If we have to retrieve large amount of data we use MYSQLI_USE_RESULT */
?>