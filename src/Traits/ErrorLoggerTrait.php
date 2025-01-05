<?php
namespace WP_Statistics\Traits;

use WP_Statistics\Service\Debugger\Provider\ErrorsDetectorProvider;

/**
* Trait for handling error logging functionality.
* 
* This trait provides methods for logging errors consistently across classes.
* Uses ErrorDetectorProvider to store errors and implements basic caching
* to avoid creating multiple provider instances.
*/
trait ErrorLoggerTrait
{
   /**
    * Cached instance of ErrorDetectorProvider.
    *
    * @var ErrorsDetectorProvider|null
    */
   private static $errorDetector = null;

   /**
    * Log errors using ErrorsDetectorProvider
    * 
    * Logs the most recent PHP error using a cached instance 
    * of ErrorsDetectorProvider to avoid multiple instantiations.
    *
    * @return void
    */
   protected static function errorListener()
   {
       if (self::$errorDetector === null) {
           self::$errorDetector = new ErrorsDetectorProvider();
       }
       
       self::$errorDetector->errorListener();
   }
}