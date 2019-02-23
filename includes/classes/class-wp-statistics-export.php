<?php

/**
 * Class WP_Statistics_Export
 */
class WP_Statistics_Export {

	/**
	 * Setup an Export Report Class
	 */
	public function __construct() {

		//Export Data
		add_action( 'admin_init', array( $this, 'export_data' ), 9 );

	}

	/**
	 * Set the headers to download the export file and then stop running WordPress.
	 */
	public function export_data() {
		if ( isset( $_POST['wps_export'] ) ) {
			global $WP_Statistics, $wpdb;

			//Set Time Limit Script Run
			set_time_limit( 0 );

			//Check Wp Nonce and Require Field
			if ( ! isset( $_POST['table-to-export'] ) || ! isset( $_POST['export-file-type'] ) || ! isset( $_POST['wps_export_file'] ) || ! wp_verify_nonce( $_POST['wps_export_file'], 'wp_statistics_export_nonce' ) ) {
				exit;
			}

			//Check Current User Capability
			$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );
			if ( current_user_can( $manage_cap ) ) {
				$table = $_POST['table-to-export'];
				$type  = $_POST['export-file-type'];

				// Validate the table name the user passed to us.
				$allow_tbl = array( "useronline", "visit", "visitor", "exclusions", "pages", "search" );
				if ( ! in_array( $table, $allow_tbl ) ) {
					$table = false;
				}

				// Validate the file type the user passed to us.
				if ( ! ( $type == "xml" || $type == "csv" || $type == "tsv" ) ) {
					$table = false;
				}

				if ( $table && $type ) {
					$file_name = 'wp-statistics' . '-' . $WP_Statistics->Current_Date( 'Y-m-d-H-i' );

					switch ( $type ) {
						case 'xml':
							$exporter = new ExportDataExcel( 'browser', "{$file_name}.xml" );
							break;
						case 'csv':
							$exporter = new ExportDataCSV( 'browser', "{$file_name}.csv" );
							break;
						case 'tsv':
							$exporter = new ExportDataTSV( 'browser', "{$file_name}.tsv" );
							break;
					}

					$exporter->initialize();

					// We need to limit the number of results we retrieve to ensure we don't run out of memory
					$query_base = "SELECT * FROM {$wpdb->prefix}statistics_{$table}";
					$query      = $query_base . ' LIMIT 0,1000';

					$i            = 1;
					$more_results = true;
					$result       = $wpdb->get_results( $query, ARRAY_A );

					// If we didn't get any rows, don't output anything.
					if ( count( $result ) < 1 ) {
						echo "No data in table!";
						exit;
					}

					if ( isset( $_POST['export-headers'] ) and $_POST['export-headers'] ) {
						foreach ( $result[0] as $key => $col ) {
							$columns[] = $key;
						}
						$exporter->addRow( $columns );
					}

					while ( $more_results ) {
						foreach ( $result as $row ) {
							$exporter->addRow( $row );

							// Make sure we've flushed the output buffer so we don't run out of memory on large exports.
							ob_flush();
							flush();
						}

						unset( $result );
						$wpdb->flush();

						$query  = $query_base . ' LIMIT ' . ( $i * 1000 ) . ',1000';
						$result = $wpdb->get_results( $query, ARRAY_A );

						if ( count( $result ) == 0 ) {
							$more_results = false;
						}

						$i ++;
					}

					$exporter->finalize();
					exit;
				}
			}
		}
	}

}

/**
 * ExportData is the base class for exporters to specific file formats. See other
 * classes below.
 */
abstract class ExportData {
	protected $exportTo; // Set in constructor to one of 'browser', 'file', 'string'
	protected $stringData; // stringData so far, used if export string mode
	protected $tempFile; // handle to temp file (for export file mode)
	protected $tempFilename; // temp file name and path (for export file mode)
	public $filename; // file mode: the output file name; browser mode: file name for download; string mode: not used

	public function __construct( $exportTo = "browser", $filename = "exportdata" ) {
		if ( ! in_array( $exportTo, array( 'browser', 'file', 'string' ) ) ) {
			throw new Exception( "$exportTo is not a valid ExportData export type" );
		}
		$this->exportTo = $exportTo;
		$this->filename = $filename;
	}

	public function initialize() {

		switch ( $this->exportTo ) {
			case 'browser':
				$this->sendHttpHeaders();
				break;
			case 'string':
				$this->stringData = '';
				break;
			case 'file':
				$this->tempFilename = tempnam( sys_get_temp_dir(), 'exportdata' );
				$this->tempFile     = fopen( $this->tempFilename, "w" );
				break;
		}

		$this->write( $this->generateHeader() );
	}

	public function addRow( $row ) {
		$this->write( $this->generateRow( $row ) );
	}

	public function finalize() {

		$this->write( $this->generateFooter() );

		switch ( $this->exportTo ) {
			case 'browser':
				flush();
				break;
			case 'string':
				// do nothing
				break;
			case 'file':
				// close temp file and move it to correct location
				fclose( $this->tempFile );
				rename( $this->tempFilename, $this->filename );
				break;
		}
	}

	public function getString() {
		return $this->stringData;
	}

	abstract public function sendHttpHeaders();

	protected function write( $data ) {
		switch ( $this->exportTo ) {
			case 'browser':
				echo $data;
				break;
			case 'string':
				$this->stringData .= $data;
				break;
			case 'file':
				fwrite( $this->tempFile, $data );
				break;
		}
	}

	protected function generateHeader() {
		// can be overridden by subclass to return any data that goes at the top of the exported file
	}

	protected function generateFooter() {
		// can be overridden by subclass to return any data that goes at the bottom of the exported file
	}

	// In subclasses generateRow will take $row array and return string of it formatted for export type
	abstract protected function generateRow( $row );

}

/**
 * ExportDataTSV - Exports to TSV (tab separated value) format.
 */
class ExportDataTSV extends ExportData {

	function generateRow( $row ) {
		foreach ( $row as $key => $value ) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[ $key ] = '"' . str_replace( '"', '\"', $value ) . '"';
		}
		return implode( "\t", $row ) . "\n";
	}

	function sendHttpHeaders() {
		header( "Content-type: text/tab-separated-values" );
		header( "Content-Disposition: attachment; filename=" . basename( $this->filename ) );
	}
}

/**
 * ExportDataCSV - Exports to CSV (comma separated value) format.
 */
class ExportDataCSV extends ExportData {

	function generateRow( $row ) {
		foreach ( $row as $key => $value ) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[ $key ] = '"' . str_replace( '"', '\"', $value ) . '"';
		}
		return implode( ",", $row ) . "\n";
	}

	function sendHttpHeaders() {
		header( "Content-type: text/csv" );
		header( "Content-Disposition: attachment; filename=" . basename( $this->filename ) );
	}
}

/**
 * Class ExportDataExcel
 */
class ExportDataExcel extends ExportData {

	const XmlHeader = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
	const XmlFooter = "</Workbook>";

	public $encoding = 'UTF-8'; // encoding type to specify in file.
	// Note that you're on your own for making sure your data is actually encoded to this encoding

	public $title = 'Sheet1'; // title for Worksheet

	function generateHeader() {

		// workbook header
		$output = stripslashes( sprintf( self::XmlHeader, $this->encoding ) ) . "\n";

		// Set up styles
		$output .= "<Styles>\n";
		$output .= "<Style ss:ID=\"sDT\"><NumberFormat ss:Format=\"Short Date\"/></Style>\n";
		$output .= "</Styles>\n";

		// worksheet header
		$output .= sprintf( "<Worksheet ss:Name=\"%s\">\n    <Table>\n", htmlentities( $this->title ) );

		return $output;
	}

	function generateFooter() {
		$output = '';

		// worksheet footer
		$output .= "    </Table>\n</Worksheet>\n";

		// workbook footer
		$output .= self::XmlFooter;

		return $output;
	}

	function generateRow( $row ) {
		$output = '';
		$output .= "        <Row>\n";
		foreach ( $row as $k => $v ) {
			$output .= $this->generateCell( $v );
		}
		$output .= "        </Row>\n";
		return $output;
	}

	private function generateCell( $item ) {
		$output = '';
		$style  = '';

		// Tell Excel to treat as a number. Note that Excel only stores roughly 15 digits, so keep
		// as text if number is longer than that.
		if ( preg_match( "/^-?\d+(?:[.,]\d+)?$/", $item ) && ( strlen( $item ) < 15 ) ) {
			$type = 'Number';
		}
		// Sniff for valid dates; should look something like 2010-07-14 or 7/14/2010 etc. Can
		// also have an optional time after the date.
		//
		// Note we want to be very strict in what we consider a date. There is the possibility
		// of really screwing up the data if we try to reformat a string that was not actually
		// intended to represent a date.
		elseif ( preg_match( "/^(\d{1,2}|\d{4})[\/\-]\d{1,2}[\/\-](\d{1,2}|\d{4})([^\d].+)?$/", $item ) &&
		         ( $timestamp = strtotime( $item ) ) &&
		         ( $timestamp > 0 ) &&
		         ( $timestamp < strtotime( '+500 years' ) ) ) {
			$type  = 'DateTime';
			$item  = strftime( "%Y-%m-%dT%H:%M:%S", $timestamp );
			$style = 'sDT'; // defined in header; tells excel to format date for display
		} else {
			$type = 'String';
		}

		$item   = str_replace( '&#039;', '&apos;', htmlspecialchars( $item, ENT_QUOTES ) );
		$output .= "            ";
		$output .= $style ? "<Cell ss:StyleID=\"$style\">" : "<Cell>";
		$output .= sprintf( "<Data ss:Type=\"%s\">%s</Data>", $type, $item );
		$output .= "</Cell>\n";

		return $output;
	}

	function sendHttpHeaders() {
		header( "Content-Type: application/vnd.ms-excel; charset=" . $this->encoding );
		header( "Content-Disposition: inline; filename=\"" . basename( $this->filename ) . "\"" );
	}
}

