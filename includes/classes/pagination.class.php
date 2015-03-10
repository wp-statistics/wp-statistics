<?php
/**
* Pagination Class
*
* This class displays a pagination navigation bar complete with links to first, last,
* previous, next, and all pages. This class handles cookie setting, page bounds checking/
* redirection, appropriate error reporting, CSS styling, and POST/GET retrieval all
* internally.
*
* PHP version 5
*
* @author 		Dane Gardow <dane.g87@gmail.com>
* @copyright	2013 Dane Gardow
* @date			01 January 2013
* @version		1.0
* @license		Free
*
******************************************************************************************/

if(!class_exists("WP_Statistics_Pagination")):	// Prevent multiple class definitions

class WP_Statistics_Pagination
{
	/*******************************************************
	  PROPERTIES / DATA MEMBERS
	*******************************************************/

	// Edit these as you desire
	const DEFAULT_ENTRIES_DISPLAY = 10;							 // Default number of entries to display per page	
	const PAGE_GETVAR_NAME		  = "page";					     // Name of GET variable name for page values (i.e., example.php?page=1)

	private $_paginationID		  = "pagination";		// ID Name of pagination object			"pagination" is default
														// used also for form name for select options

	// Do not edit these values; they are simply null initializations
	private $_totalEntries         = null;		// Total number of entries (usually supplied by MySQL query)
	private $_pagesPerSection      = null;		// Total number of pages displayed per section (supplied by admin)
	private $_entriesPerPage       = null;		// Total number of entries displayed per page (supplied by user)
	private $_currentPage          = null;		// Current page viewed by user
	private $_displayOptions       = array();		// Array of options for viewing how many entries per page (supplied by user)
	private	$_errors               = array();		// Array of encountered error messages
	private	$_styles               = array();		// Array of CSS styles for pagination navigation display




	/*******************************************************
	  CONSTRUCTOR
	*******************************************************/
	
	function __construct($totalEntries, $pagesPerSection, $options = "", $paginationID = "", $stylePageOff = "",
						 $stylePageOn = "", $styleErrors = "", $styleSelect = "")
	{	
		$this->setPaginationID($paginationID);			// Set ID name of pagination object
		$this->setTotalEntries($totalEntries);			// Set total entries
		$this->setPagesPerSection($pagesPerSection);	// Set pages per section
		$this->setDisplayOptions($options);				// Set viewing display options
		$this->setEntriesPerPage();						// Set entries per page (input from POST or cookies)
		$this->setCurrentPage();						// Set current page (input from GET)
														// ! This function must follow after setEntriesPerPage()
		$this->setStyles($stylePageOff, $stylePageOn,
						 $styleSelect, $styleErrors);	// Set CSS styles for pagination navigation display
	}



	/*******************************************************
	  UTILITY FUNCTIONS
	*******************************************************/
	
	public function deleteCookie()			// deletes the cookie if it exists
	{
		$cookieVar = $this->_getPOSTVarName();
	
		if(isset($_COOKIE[$cookieVar]))					// If cookie is set
		{
			$_COOKIE[$cookieVar] = "";					// Clear cookie
			setcookie($cookieVar, "", time()-3600, "/");	// Delete cookie
		}
	}
	
	private function _getURL($input = 1)							// returns appropriate URL with all GET variables intact
	{																// updates only the particular GET variable in question
		$getVars = $_GET;											// Get all GET variables		
		
		/* Uncomment this if you need to exclude any GET variables (due to HTACCESS issues, for example) from being
		*  processed in the ensuing URL. Simply enter in the GET variable name in the unset(...) function below.
		unset($getVars["foo"], $getVars["bar"], ... );	// Remove any so they do not appear in URL
		*/
		
		$output  = '?'.http_build_query(array_merge($getVars, array($this->_getIDGETVarName()=>$input)));
		$output .= '#'. $this->getPaginationID();	// Add #xxx at the end of URL for auto-scrolling
		
		return $output;
	}
	
	private function _isError()				// determines if an error exists and registers any errors
	{		
		if($this->_errors)				// If error already exists, return
			return true;

		if(!$this->_totalEntries)		// If total entries not set
			$this->_errors[] = "The value for <strong>total entries</strong> has not been specified.";
		
		if(!$this->_displayOptions)		// If display options not set
			$this->_errors[] = "The values for <strong>display options</strong> have not been specified.";
		
		if(!$this->_entriesPerPage)		// If entries per page not set
			$this->_errors[] = "The value for <strong>entries per page</strong> has not been specified.";
		
		if(!$this->_currentPage)			// If current page not set
			$this->_errors[] = "The value for <strong>current page</strong> has not been specified.";
		
		return ($this->_errors) ? true : false;
	}

	private function _validEntry($input)				// determines if input is valid
	{
		if(is_array($input))				// If array
		{		
			foreach($input as $element)
			{														// Recursion: evaluate each array element
				if(!$this->_validEntry($element))					// If invalid
					return false;
			}

			return true;											// If function makes it to this point, it is valid
		}
		else								// If not array
		{
			if( (preg_match("/^\d+$/",$input) && $input > 0) || strtolower($input) == "all")	// If positive digit or "all"
				return true;
			else
				return false;
		}
	}
	
	private function _navBox($text, $destinationPage, $end = 0)		// returns span-encased link for pagination bar
	{	
		switch($end)
		{
			case 1:
				$title = "First page";
				break;
			case 2:
				$title = "Previous page";
				break;
			case 3:
				$title = "Next page";
				break;
			case 4:
				$title = "Last page (". $this->getTotalPages() .")";
				break;
			default:
				$title = "";
				break;
		}

		$title = ($end > 0 && $title != "") ? 'title="' . $title . '"' : '';

		$style = $this->_styles["pageOff"];

		// Determine Style
		$style = ($this->_currentPage == $destinationPage && !$end) ? $this->_styles["pageOn"] : $this->_styles["pageOff"];

		// Determine Link URL/Text		
		$url = "";
		
		if($this->_currentPage != $destinationPage			// If current page is not same as destination page
			&& $destinationPage <= $this->getTotalPages()	// and destination page does not exceed last page
			&& $destinationPage >= 1)						// and destination page is not less than first page
		{
			$text = '<a href="'. $this->_getURL($destinationPage) .'">'. $text .'</a>'; // then make $text a link
		}

		if($style)
			$style = 'class="'. $style .'"';
		
		$onClick = ($url) ? "onclick=\"location.href='". $url ."'\"" : "";	// Determine if span element is clickable
		
		return '<span '. $style . $title .' '. $onClick .'>'. $text .'</span>';
	}
	
	
	
	/*******************************************************
	  DISPLAY FUNCTIONS
	*******************************************************/
	
	public function display()				// displays the pagination bar
	{	
		if($this->_isError())						// If error encountered, do not display, but display errors
			return $this->displayErrors();

		$firstPage = 1;
		$previousPage = $this->_currentPage - 1;
		$nextPage = $this->_currentPage + 1;
		$lastPage = $this->getTotalPages();
		
		$totalpages = $this->getTotalPages();
		
		$pagesPerSection = $this->getPagesPerSection();
		
		$sectionStart = $this->_currentPage - floor($pagesPerSection / 2);	// Section start is current page # minus half the # of pages per section
		
		if($sectionStart <= 0)										// Adjust section start to 1 (first page) if # pages between 1st page
			$sectionStart = 1;										// and current page is less than half the # of pages per section
		
		$sectionEnd = $sectionStart + $pagesPerSection - 1;			// Section end is # pages per section after section start,
																	// minus 1 (otherwise # of pages per section will exceed given amount by 1)

		if($sectionEnd > $lastPage)									// Adjust section end to last page if section end exceeds last page
			$sectionEnd = $lastPage;

		$sectionStart = $sectionEnd - $pagesPerSection + 1;			// Adjust section start to # of pages behind section end

		$output  = $this->_navBox("&lt;&lt;", $firstPage, 1);	// First page
		$output .= $this->_navBox("&lt;", $previousPage, 2);		// Previous page
		
		for($i = $sectionStart; $i <= $sectionEnd; ++$i)
			$output .= $this->_navBox($i, $i);					// Pagination
		
		$output .= $this->_navBox("&gt;", $nextPage, 3);			// Next Page
		$output .= $this->_navBox("&gt;&gt;", $lastPage, 4);		// Last Page
		
		return $output;
	}
	
	public function displayErrors()			// displays the errors encountered
	{
		if(!$this->_errors)
			return "No errors were encountered.";

		$words = (count($this->_errors) > 1) ? "errors were" : "error was";

		// Determine CSS styling for error reporting
		if($this->_styles["errors"])
			$css = 'class="'. $this->_styles["errors"] .'"';
		else
			$css = '';
		
		$output = '
		<div '. $css .'>
		The following '. $words .' encountered while using the '. get_class($this) .' class:<br/><br/>
		<ul>';

		foreach($this->_errors as $error)
			$output .= '<li>'. $error .'</li>';

		$output .= '
		</ul>
		</div>';

		return $output;
	}
	
	public function displaySelectInterface()		// displays the <select> interface for choosing display amount
	{
		if(count($this->_displayOptions) < 2)				// If only 1 or fewer options, do not display
			return;
			
		if($this->_isError())								// If error encountered, do not display
			return;
		
		static $count = 0;	// This counts how many times function is run.
							// This variable value is tacked on the end of the form name which
							// will enable multiple instances of the display interface form
		
		$paginationID = $this->getPaginationID();
		
		$formName = $paginationID. '_select_form';
		
		// Determine CSS styling for <select>
		if($this->_styles["select"])
			$css = 'class="'. $this->_styles["select"] .'"';
		else
			$css = "";

		$formNumber = ($count) ? $count : "";
		
		$output = '
		<form name="'. $formName . $formNumber .'" method="post" style="display:inline-block;" action="'. $this->_getURL($this->_currentPage) .'">
		Show: 
		<select '. $css .' name="'. $this->_getPOSTVarName() .'" OnChange ="'. $formName . $formNumber .'.submit()">';
		
		foreach($this->_displayOptions as $line)
		{		
			if($this->_entriesPerPage == $line || $this->_entriesPerPage == $this->_totalEntries)	// If option equals entries per page
				$selected = "selected";															// or entries per page equals total entries
			else																				// then select option, otherwise
				$selected = "";																	// leave unselected

			$output .= '<option value="'. $line .'" '. $selected .'>'. $line .'</option>';
		}
		
		$output .= '
		</select>
		<noscript><input type="submit" name="paginationDisplaySubmit" value="Display"/></noscript>
		</form>';
		
		++$count;	// Increase # of times this function has run
		
		return $output;
	}
	
	
	
	/*******************************************************
	  SET FUNCTIONS
	*******************************************************/
	
	public function setCurrentPage()				// sets the currently accessed page from GET value
	{
		$idVar = $this->_getIDGETVarName();
	
		if(isset($_GET[$idVar]))		// If GET set
			$page = $_GET[$idVar];		// Retrieve page from GET
		else
			$page = 1;								// Otherwise use first page
		
		if($page < 1 || !preg_match("/^\d+$/", $page))						// If page is less than 1 or page value not a digit
		{
			header("Location: ". $this->_getURL());		// No argument for _getURL() sets it to 1 (first page)
			exit;
		}

		if($page > $this->getTotalPages() && $this->getTotalPages() != 0)	// If page exceeds last page
		{																	// 2nd condition prevents infinite loop should it equal 0
			header("Location: ". $this->_getURL($this->getTotalPages()));
			exit;
		}

		$this->_currentPage = $page;
	}

	public function setDisplayOptions($input)		// sets the user-specified display amount
	{
		if(!$this->_validEntry($input))				// If invalid entry encountered, register error and exit function
		{
			if(is_array($input))					// If array
			{
				$argument = "";
				
				foreach($input as $key=>$element)
				{
					if($key > 0)
						$argument .= ", ";

					$argument .= $element;			// Save all elements in string
				}
			}
			else									// If not array
			{
				$argument = $input;
			}

			$this->_errors[] = "The value(s) set for <strong>display options</strong> is/are invalid: ". $argument;

			return;
		}

		if(!is_array($input) && strtolower($input) == "all")	// If Not Array and "All" selected
			$input = $this->_totalEntries;						// Set total entries value
		
		$this->_displayOptions = $input;
	}

	public function setEntriesPerPage()				// sets entries per page amount from POST or COOKIE values
	{
		if($this->_errors)										// If an error, quit
			return;		
		
		$varName = $this->_getPOSTVarName();
		
		if(count($this->_displayOptions) > 1)					// If more than 1 display option
		{
			$value = $this->_displayOptions[0];					// Default is first selection
			
			if(isset($_POST[$varName]))				// If POST is set
			{
				if(in_array($_POST[$varName], $this->_displayOptions))		// Check for valid post value
				{
					$value = $_POST[$varName];
					setcookie($varName, $value, 604800 + time(), "/");		// Set cookie
					$_COOKIE[$varName] = $value;
				}
				else 																		// If invalid post value
				{					
					$value = self::DEFAULT_ENTRIES_DISPLAY;									// Set to default if invalid
				}
			}
			elseif(isset($_COOKIE[$varName]))			// If POST not set, but COOKIE set
			{
				// Check for valid cookie value
				if(in_array($_COOKIE[$varName], $this->_displayOptions))	// Check for valid cookie value
				{
					$value = $_COOKIE[$varName];							// Set to value if valid
				}
				else
				{
					$value = self::DEFAULT_ENTRIES_DISPLAY;									// Set to default if invalid
					$this->deleteCookie();													// Delete invalid cookie
				}
			}
		}
		else			// If only one option, set either to default or displayOptions value
		{
			$value = ($this->_displayOptions) ? $this->_displayOptions : self::DEFAULT_ENTRIES_DISPLAY;
		}

		if(strtolower($value) == "all")				// If set to "All", use total entries
			$value = $this->_totalEntries;
			
		$this->_entriesPerPage = $value;
	}
	
	public function setPagesPerSection($input)		// sets # of pages per section
	{	
		if(!$this->_validEntry($input))
		{
			$this->_errors[] = "The value set for <strong>pages per section</strong> is invalid: ". $input;
			return;
		}
		
		$this->_pagesPerSection = $input;
	}	
	
	public function setPaginationID($input)
	{
		if($input)
		{
			if(preg_match("/^\d+$/",$input[0]))		// Check if first character is a digit
			{
				$this->_errors[] = "The first character of the <strong>pagination ID</strong> cannot be a number: ". $input;
				return;					// cannot be a digit because variable names cannot start with digits,
			}							// and this value will be used as a variable name

			$this->_paginationID = $input;
		}
	}
	
	public function setStyles($pageOff = "", $pageOn = "", $select = "", $errors = "")	// sets CSS style class names
	{			
		$this->_styles = array(
			"pageOff"  => $pageOff,
			"pageOn"   => $pageOn,
			"select"   => $select,
			"errors" => $errors
			);
	}

	public function setTotalEntries($input)			// sets total number of entries
	{
		if(!$this->_validEntry($input))
		{
			$this->_errors[] = "The value set for <strong>total entries</strong> is invalid: ". $input;
			return;
		}
		
		$this->_totalEntries = $input;
	}




	/*******************************************************
	  GET FUNCTIONS
	*******************************************************/

	private function _getIDGETVarName()					// returns GET variable name for pagination pages
	{
		return $this->getPaginationID() .'-'. self::PAGE_GETVAR_NAME;
	}
	
	private function _getPOSTVarName()					// returns POST variable name for select/cookie entities
	{
		return $this->getPaginationID().'Display';
	}	
	
	public function getCurrentPage()					// returns the currently accessed page number
	{
		if(!$this->_currentPage)		// If not set, return first page
			return 1;
		else
			return $this->_currentPage;
	}

	public function getPagesPerSection()				// returns the # of pages per section
	{
		if(!$this->_pagesPerSection)									// If not set, set error and return 0
		{
			$this->_errors[] = "The value for <strong>pages per section</strong> has not been set.";
			return 0;
		}
	
		if($this->_pagesPerSection > $this->getTotalPages())			// If per section is greater than total pages
			return $this->getTotalPages();							// Return total pages
		else
			return $this->_pagesPerSection;							// Otherwise return per section
	}

	public function getPaginationID()					// returns ID name for pagination object
	{
		return $this->_paginationID;
	}
	
	public function getTotalPages()						// returns total pages
	{
		if($this->_errors)											// If there is an error, return 0
			return 0;
			
		if($this->_entriesPerPage == 0)								// Prevent division by zero
			return 0;
	
		return ceil($this->_totalEntries / $this->_entriesPerPage);	// Total pages: total # of entries divided by total entries per page
	}

	public function getEntryStart()						// returns the start entry for the page
	{
		if($this->_isError())			// If error encountered
			return 0;
	
		return ($this->_currentPage - 1) * $this->_entriesPerPage;	// Entry start: 1 less than current page multiplied by total entries per page
	}

	public function getEntryEnd()						// returns the last entry for the page
	{
		if($this->_isError())			// If error encountered
			return 0;

		if($this->_currentPage == $this->getTotalPages())			// If current page is last page
			return $this->_totalEntries - $this->getEntryStart();	// then entry end is total entries minus start entry
		else
			return $this->_entriesPerPage;							// otherwise entry end is # of entries per page
	}
	
	public function getEntryEndFF()		// Flat-file version of the above getEntryEnd() function
	{
		if($this->_isError())			// If error encountered
			return 0;
		
		if($this->_currentPage == $this->getTotalPages())			// If current page is last page
			return $this->_totalEntries;								// then entry end is total entries minus start entry
		else
			return $this->getEntryStart() + $this->_entriesPerPage;	// otherwise entry end is # of entries per page after start
	}
}

endif;	// Prevent multiple class definitions
?>