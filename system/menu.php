<?php
/**
 * Pines' menu XML.
 *
 * This is not used yet.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<menus>
	<menu>
		<name>Main Menu</name>
		<position>main_menu</position>
		<depends>
			<ability />
		</depends>
		<entry>
			<name>About</name>
			<href>/about</href>
			<depends>
				<ability>com_about/view</ability>
			</depends>
		</entry>
		<entry>
			<name>Configuration</name>
			<depends>
				<ability>com_configure/edit|com_configure/view</ability>
			</depends>
			<entry>
				<name>Components</name>
				<href>/configure/list</href>
			</entry>
		</entry>
		<entry>
			<name>Log Manager</name>
			<depends>
				<ability>com_logger/view|com_logger/clear</ability>
			</depends>
			<entry>
				<name>View</name>
				<href>/logger/view</href>
				<depends>
					<ability>com_logger/view</ability>
				</depends>
			</entry>
			<entry>
				<name>Clear</name>
				<href>/logger/clear</href>
				<depends>
					<ability>com_logger/clear</ability>
				</depends>
			</entry>
		</entry>
		<entry>
			<name>Newsletter</name>
			<depends>
				<ability>com_newsletter/managemails|com_newsletter/send</ability>
			</depends>
			<entry>
				<name>Mails Index</name>
				<href>/newsletter/list</href>
			</entry>
			<entry>
				<name>New Mail</name>
				<href>/newsletter/new</href>
			</entry>
		</entry>
		<entry>
			<name>User Manager</name>
			<depends>
				<ability>com_user/new|com_user/manage|com_user/newg|com_user/manageg</ability>
			</depends>
			<entry>
				<name>Users</name>
				<href>/user/manageusers</href>
				<depends>
					<ability>com_user/manage</ability>
				</depends>
			</entry>
			<entry>
				<name>New User</name>
				<href>/user/edituser</href>
				<depends>
					<ability>com_user/new</ability>
				</depends>
			</entry>
			<entry>
				<name>Groups</name>
				<href>/user/managegroups</href>
				<depends>
					<ability>com_user/manageg</ability>
				</depends>
			</entry>
			<entry>
				<name>New Group</name>
				<href>/user/editgroup</href>
				<depends>
					<ability>com_user/newg</ability>
				</depends>
			</entry>
		</entry>
		<entry>
			<name>My Account</name>
			<href>/user/editself</href>
			<depends>
				<ability>com_user/self</ability>
			</depends>
		</entry>
		<entry>
			<name>Logout</name>
			<href>/user/logout</href>
			<depends>
				<ability />
			</depends>
		</entry>
	</menu>
	<menu>
		<name>Users</name>
		<position>left</position>
		<depends>
			<option>com_user</option>
		</depends>
		<entry>
			<name>New User</name>
			<href>/user/edituser</href>
			<depends>
				<ability>com_user/newuser</ability>
			</depends>
		</entry>
		<entry>
			<name>New Group</name>
			<href>/user/editgroup</href>
			<depends>
				<ability>com_user/newgroup</ability>
			</depends>
		</entry>
	</menu>
</menus>