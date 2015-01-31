/**
 * Author: Lars Ebert
 * 2015/01/31
 */
Changelog
=========

<table>
	<thead>
		<tr>
			<th>Version</th>
			<th>Changes</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>0.0.6</td>
			<td>
				<ul class="changelog">
					<li class="added">Added an install tool to set up the database connection.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>0.0.5</td>
			<td>
				<ul class="changelog">
					<li class="changed">Changed placeholder and plugin syntax to use xml tags. This way, parameters can now be a json object.</li>
					<li class="changed">Improved the styles of forms and errors in the frontend.</li>
					<li class="added">Added a plugin for google maps.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>0.0.4</td>
			<td>
				<ul class="changelog">
					<li class="changed">Enhanced the preview function in the backend. The preview link will now only be displayed, if the page is accessable.</li>
					<li class="changed">Changed the backend and frontend layout to allow for a bigger sidebar and multipe navigation layers.</li>
					<li class="changed">Session::printMessage() was renamed to Session::getMessage() and now returns the message instead of displaying it.</li>
					<li class="added">Added form and error styles for the frontend layout.</li>
					<li class="added">Added a contact form plugin.</li>
					<li class="added">Added the method Router::here() to return the current pages url</li>
					<li class="added">Added a proper display of the 404 error</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>0.0.3</td>
			<td>
				<ul class="changelog">
					<li class="changed">Found some labels still in German and translated to English</li>
					<li class="changed">Extended the Validator class to support custom validation rules and rules from the ValidationRules class</li>
					<li class="changed">Changed/Fixed backend design (form elements, buttons, form errors, default table layout)</li>
					<li class="changed">Form::input() now renders superfluous options as html attributes</li>
					<li class="added">Added ValidationRules class</li>
					<li class="added">Added user management to the backend</li>
					<li class="added">Added methods count() and delete() to DB class</li>
					<li class="added">Added methods create(), update() and delete() to User class</li>
					<li class="added">Added method Router::redirect()</li>
					<li class="added">Added method Form::value()</li>
					<li class="added">Added default backend user (admin)</li>
					<li class="added">Added links that need confirmation (like deleting)</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>0.0.2</td>
			<td>
				<ul class="changelog">
					<li class="changed">Fixed default template</li>
					<li class="changed">Fixed the header extraction for files with BOM</li>
					<li class="removed">Removed the MEDIA_URL constant. The folder is non existend and the constant not used. Maybe this will be added back in if I write a media plugin or something.</li>
					<li class="removed">Removed the PLUGIN_URL constant.</li>
					<li class="added">Added CONTENT_URL constant.</li>
					<li class="added">Added a gallery plugin.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>0.0.1</td>
			<td>First version of Cmd! Many things are missing, but the basic content management works.</td>
		</tr>
	</tbody>
</table>

Content MarkDown is in Alpha right now. If you are missing a feature or found a bug, mail me to <info@advitum.de>. Thank you!