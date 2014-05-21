<?php
/*
 * Cache list
 * 
 * An array of option/action combinations 
 * for which to leverage PHP cached files.
 *
 * Use com_cache as a manager for caching particular parts of pines.
 * 
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author1 Angela Murrell <amasiell.g@gmail.com>
 * @author2 Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * 
 * 
 * The Cachelist is structured as such:
 *		option => array(
 *			action => array( 
 *				domain => array(cachetime (in seconds), cachequery (bool), cacheloggedin (bool), disabled (bool), exceptions => array(isset => array(name, name2), value => array(name => array(value, value2)) ),
 *				anotherdomain => array(cachetime (in seconds), cachequery (bool), cacheloggedin (bool), disabled (bool), exceptions => array() )
 *			),
 *			anotheraction => array(
 *				alldomains => array(cachetime (in seconds), cachequery (bool), cacheloggedin (bool), disabled (bool), exceptions => array()  )
 *		),
 *		anotheroption => array(
 *			action => array(cachetime (in seconds), cachequery (bool), cacheloggedin (bool), disabled (bool), exceptions => array()  ),
 *			anotheraction => array(cachetime (in seconds), cachequery (bool), cacheloggedin (bool), disabled (bool), exceptions => array()  ),
 *		),
 * 
 * Note: If using 'all' for the domain, there can be no other domain keys in that action. It's either all, or specific domains.
 * 
 * Cache Query Exceptions: The array is segmented into two keys, isset and value.
 * The variable names in the isset array will be checked for isset and caching
 * will be skipped if the variables are found in either post or get. The value
 * array represents that caching be skipped if the variable equals the value
 * specified in the exception. This is all only applicable if caching the query
 * is on for the directive. This is useful for situations like not caching
 * certain pages, products, widgets, etc.
 */

return array(
	'cache_on' => false,
	'parent_directory' => $_SERVER['DOCUMENT_ROOT'].'/pinescache/',
	'global_exceptions' => array('users' => array(), 'groups' => array()),
	'cachelist' => array(
		'' => array(
			'' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_calendar' => array(
			'editcalendar' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => true)
				)
		),
		'com_content' => array(
			'page' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'page/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'category/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_customer' => array(
			'customer/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'customer/list' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'company/list' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'company/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_dash' => array(
			'' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'dashboard/widget_json' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false, 
					'exceptions' => array(
						'isset' => array(),
						'value' => array('widgetname' => array('agenda', 'current_user', 'countdown', 'clockin'))
					)
				)
			)
		),
		'com_elfinder' => array(
			'finder' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_inventory' => array(
			'whsale_new' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'sale_new' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_loan' => array(
			'loan/list' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'loan/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_modules' => array(
			'module/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_replace' => array(
			'replacement/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
		),
		'com_reports' => array(
			'warboard' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
		),
		'com_sales' => array(
			'sale/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => true)
				),
			'return/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'countsheet/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'po/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'product/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'product/list' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'forms/dateselect' => array(
				'all' => array('time' => 3600, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'forms/locationselect' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'category/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_storefront' => array(
			'category/browse' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'product' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_su' => array(
			'loginpage' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_testimonials' => array(
			'testimonial/list' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'testimonial/list_reviews' => array(
				'all' => array('time' => 18000, 'cachequery' => true, 'cacheloggedin' => true, 'disabled' => false)
				),
			'testimonial/edit' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				),
			'help/help' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => true, 'disabled' => false)
				)
		),
		'com_timeoutnotice' => array(
			'loginpage' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => false, 'disabled' => false)
				)
		),
		'com_user' => array(
			'edituser' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => false, 'disabled' => false)
				),
			'editgroup' => array(
				'all' => array('time' => 18000, 'cachequery' => false, 'cacheloggedin' => false, 'disabled' => false)
				)
		),
	)
);
?>