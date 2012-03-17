<?php
/**
 * Exception classes.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * HTTP Client Error Exception
 * 
 * Used to send a HTTP client error to the user.
 *
 * @package Pines
 */
class HttpClientException extends Exception {
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		if ($message === null) {
			switch ($code) {
				case 400:
					$message = 'Bad Request';
					break;
				case 401:
					$message = 'Unauthorized';
					break;
				case 402:
					$message = 'Payment Required';
					break;
				case 403:
					$message = 'Forbidden';
					break;
				case 404:
					$message = 'Not Found';
					break;
				case 405:
					$message = 'Method Not Allowed';
					break;
				case 406:
					$message = 'Not Acceptable';
					break;
				case 407:
					$message = 'Proxy Authentication Required';
					break;
				case 408:
					$message = 'Request Timeout';
					break;
				case 409:
					$message = 'Conflict';
					break;
				case 410:
					$message = 'Gone';
					break;
				case 411:
					$message = 'Length Required';
					break;
				case 412:
					$message = 'Precondition Failed';
					break;
				case 413:
					$message = 'Request Entity Too Large';
					break;
				case 414:
					$message = 'Request-URI Too Long';
					break;
				case 415:
					$message = 'Unsupported Media Type';
					break;
				case 416:
					$message = 'Requested Range Not Satisfiable';
					break;
				case 417:
					$message = 'Expectation Failed';
					break;
				case 418:
					$message = 'I\'m a teapot';
					break;
				case 420:
					$message = 'Enhance Your Calm';
					break;
				case 422:
					$message = 'Unprocessable Entity';
					break;
				case 423:
					$message = 'Locked';
					break;
				case 424:
					$message = 'Failed Dependency';
					break;
				case 425:
					$message = 'Unordered Collection';
					break;
				case 426:
					$message = 'Upgrade Required';
					break;
				case 428:
					$message = 'Precondition Required';
					break;
				case 429:
					$message = 'Too Many Requests';
					break;
				case 431:
					$message = 'Request Header Fields Too Large';
					break;
				case 444:
					$message = 'No Response';
					global $pines;
					$pines->page->override = true;
					break;
				default:
					$message = 'You Done Goofed';
					break;
			}
		}
		if (isset($code) && $code !== 0)
			header('HTTP/1.1 '.substr(str_replace("\n", '', (string) $code), 0, 3).' '.substr(str_replace("\n", '', (string) $message), 0, 100));
		else
			header('HTTP/1.1 497 '.substr(str_replace("\n", '', (string) $message), 0, 100));
		parent::__construct($message, $code, $previous);
	}
}

/**
 * HTTP Server Error Exception
 * 
 * Used to send a HTTP server error to the user.
 *
 * @package Pines
 */
class HttpServerException extends Exception {
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		if ($message === null) {
			switch ($code) {
				case 500:
					$message = 'Internal Server Error';
					break;
				case 501:
					$message = 'Not Implemented';
					break;
				case 502:
					$message = 'Bad Gateway';
					break;
				case 503:
					$message = 'Service Unavailable';
					break;
				case 504:
					$message = 'Gateway Timeout';
					break;
				case 505:
					$message = 'HTTP Version Not Supported';
					break;
				case 506:
					$message = 'Variant Also Negotiates';
					break;
				case 507:
					$message = 'Insufficient Storage';
					break;
				case 508:
					$message = 'Loop Detected';
					break;
				case 509:
					$message = 'Bandwidth Limit Exceeded';
					break;
				case 510:
					$message = 'Not Extended';
					break;
				case 511:
					$message = 'Network Authentication Required';
					break;
				default:
					$message = 'I Accidentally The Whole Server';
					break;
			}
		}
		if (isset($code) && $code !== 0)
			header('HTTP/1.1 '.substr(str_replace("\n", '', (string) $code), 0, 3).' '.substr(str_replace("\n", '', (string) $message), 0, 100));
		else
			header('HTTP/1.1 597 '.substr(str_replace("\n", '', (string) $message), 0, 100));
		parent::__construct($message, $code, $previous);
	}
}

?>