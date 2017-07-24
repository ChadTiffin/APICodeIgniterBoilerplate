<div style="max-width: 600px;margin:auto;font-size: 1.4em;padding:20px;border:1px solid #f4f4f4;">
	<p>Hello, you have been registered for the <?=APP_NAME?> app. These are your login details:</p>

	<table>
		<tr>
			<th>Login URL</th>
			<td><a href="https://<?=FRONT_END_DOMAIN?>/login">https://<?=FRONT_END_DOMAIN?>/login</a></td>
		</tr>
		<tr>
			<th>Username</th>
			<td><?=$username?></td>
		</tr>
		<tr>
			<th>Password</th>
			<td><?=$password?></td>
		</tr>
	</table>

	<p>After you navigate to the App, you can go to the browser menu and click "Add to Homescreen". The web app will now behave like a native web app, with offline access.</p>

	<p>It would be a good idea to change your password after logging in. Happy budgeting!</p>
</div>