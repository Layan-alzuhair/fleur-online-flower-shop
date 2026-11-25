<?php
/**
 * AppException: an error whose message is safe to show to the user,
 * for example "Only 2 of Velvet Dahlia left in stock".
 *
 * Pages catch AppException and display its message. Any other exception
 * (database errors, bugs) goes to the global handler in includes/init.php,
 * which logs the details and shows a generic message instead, so SQL,
 * file paths and stack traces never reach the browser.
 */
class AppException extends RuntimeException
{
}
