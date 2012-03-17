<?php
/**
 * Display an error notice.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$code = $this->exception->getCode();
if (empty($this->title))
	$this->title = ($code !== 0 ? 'Error '.htmlspecialchars($code) : 'Unknown Error');
if (empty($this->note))
	$this->note = htmlspecialchars($this->exception->getMessage());

if (isset($this->custom_content)) {
	echo $this->custom_content;
	return;
}

if ($code == 400) { // Bad Request ?>
<p>
	Your browser (or proxy) sent a request that this server could not understand.
</p>
<?php } elseif ($code == 401) { // Unauthorized ?>
<p>
	This server could not verify that you are authorized to access the requested
	URL. You either supplied the wrong credentials (e.g., bad password), or your
	browser doesn't understand how to supply the credentials required.
</p>
<p>
	In case you are allowed to request the document, please check your user-id
	and password and try again.
</p>
<?php } elseif ($code == 402) { // Payment Required ?>
<p>
	Payment is required before the request can be processed.
</p>
<?php } elseif ($code == 403) { // Forbidden ?>
<p>
	You don't have permission to access the requested URL.
</p>
<?php } elseif ($code == 404) { // Not Found ?>
<p>
	The page you requested cannot be found on this server.
</p>
<?php if (!empty($_SERVER['HTTP_REFERER'])) { ?>
<p>
	The link on the
	<a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>">referring
		page</a> seems to be wrong or outdated. Please inform the author of
	<a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>">that
		page</a> about the error.
</p>
<?php } else { ?>
<div>
	Suggestions:
	<ul>
		<li>Check the spelling of the address you requested.</li>
		<li>If you are still having problems, please <a href="<?php echo htmlspecialchars(pines_url()); ?>">visit the homepage</a>.</li>
	</ul>
</div>
<?php } ?>
<?php } elseif ($code == 405) { // Method Not Allowed ?>
<p>
	The <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD']); ?> method is
	not allowed for the requested URL.
</p>
<?php } elseif ($code == 406) { // Not Acceptable ?>
<p>
	The requested resource is only capable of generating content not acceptable
	according to the Accept headers sent in the request.
</p>
<?php } elseif ($code == 407) { // Proxy Authentication Required ?>
<p>
	You must first authenticate yourself with the proxy.
</p>
<?php } elseif ($code == 408) { // Request Timeout ?>
<p>
	The server closed the network connection because the browser didn't finish
	the request within the specified time.
</p>
<?php } elseif ($code == 409) { // Conflict ?>
<p>
	The request could not be processed because of conflict in the request.
</p>
<?php } elseif ($code == 410) { // Gone ?>
<p>
	The requested URL is no longer available on this server and there is no
	forwarding address.
</p>
<?php } elseif ($code == 411) { // Length Required ?>
<p>
	A request with the <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD']); ?>
	method requires a valid <code>Content-Length</code> header.
</p>
<?php } elseif ($code == 412) { // Precondition Failed ?>
<p>
	The precondition on the request for the URL failed positive evaluation.
</p>
<?php } elseif ($code == 413) { // Request Entity Too Large ?>
<p>
	The method does not allow the data transmitted, or the data volume exceeds
	the capacity limit.
</p>
<?php } elseif ($code == 414) { // Request-URI Too Long ?>
<p>
	The length of the requested URL exceeds the capacity limit for this server.
	The request cannot be processed.
</p>
<?php } elseif ($code == 415) { // Unsupported Media Type ?>
<p>
	The server does not support the media type transmitted in the request.
</p>
<?php } elseif ($code == 416) { // Requested Range Not Satisfiable ?>
<p>
	The requested portion of the file cannot be supplied by the server.
</p>
<?php } elseif ($code == 417) { // Expectation Failed ?>
<p>
	The server cannot meet the requirements of the Expect request header.
</p>
<?php } elseif ($code == 418) { // I'm a teapot ?>
<p>
	The server is short and stout.
</p>
<?php } elseif ($code == 420) { // Enhance Your Calm ?>
<p>
	You have sent too many requests recently. Please wait a few moments.
</p>
<?php } elseif ($code == 422) { // Unprocessable Entity ?>
<p>
	The request was well-formed but was unable to be followed due to semantic
	errors.
</p>
<?php } elseif ($code == 423) { // Locked ?>
<p>
	The resource that is being accessed is locked.
</p>
<?php } elseif ($code == 424) { // Failed Dependency ?>
<p>
	The request failed due to failure of a previous request.
</p>
<?php } elseif ($code == 425) { // Unordered Collection ?>
<p>
	Collection was unordered.
</p>
<?php } elseif ($code == 426) { // Upgrade Required ?>
<p>
	The client should switch to a different protocol.
</p>
<?php } elseif ($code == 428) { // Precondition Required ?>
<p>
	The server requires the request to be conditional.
</p>
<?php } elseif ($code == 429) { // Too Many Requests ?>
<p>
	You have sent too many requests recently. Please wait a few moments.
</p>
<?php } elseif ($code == 431) { // Request Header Fields Too Large ?>
<p>
	The server is unwilling to process the request because either an individual
	header field, or all the header fields collectively, are too large.
</p>
<?php } elseif (is_a($this->exception, 'HttpClientException')) { // You Done Goofed ?>
<p>
	Consequences will never be the same.
</p>
<?php } elseif ($code == 500) { // Internal Server Error ?>
<p>
	The server encountered an internal error and was unable to complete your
	request.
</p>
<?php } elseif ($code == 501) { // Not Implemented ?>
<p>
	The server does not support the action requested by the browser.
</p>
<?php } elseif ($code == 502) { // Bad Gateway ?>
<p>
	The proxy server received an invalid response from an upstream server.
</p>
<?php } elseif ($code == 503) { // Service Unavailable ?>
<p>
	The server is temporarily unable to service your request due to maintenance
	downtime or capacity problems. Please try again later.
</p>
<?php } elseif ($code == 504) { // Gateway Timeout ?>
<p>
	The server was acting as a gateway or proxy and did not receive a timely
	response from the upstream server.
</p>
<?php } elseif ($code == 505) { // HTTP Version Not Supported ?>
<p>
	The server does not support the HTTP protocol version used in the request.
</p>
<?php } elseif ($code == 506) { // Variant Also Negotiates ?>
<p>
	A variant for the requested entity is itself a negotiable resource. Access
	not possible.
</p>
<?php } elseif ($code == 507) { // Insufficient Storage ?>
<p>
	The server is unable to store the representation needed to complete the
	request.
</p>
<?php } elseif ($code == 508) { // Loop Detected ?>
<p>
	The server detected an infinite loop while processing the request.
</p>
<?php } elseif ($code == 509) { // Bandwidth Limit Exceeded ?>
<p>
	The server's bandwidth limit has been exceeded. Please try again later.
</p>
<?php } elseif ($code == 510) { // Not Extended ?>
<p>
	Further extensions to the request are required for the server to fulfill it.
</p>
<?php } elseif ($code == 511) { // Network Authentication Required ?>
<p>
	You need to authenticate to gain network access.
</p>
<?php } elseif (is_a($this->exception, 'HttpServerException')) { // I Accidentally The Whole Server ?>
<p>
	Is this dangerous?
</p>
<?php } ?>