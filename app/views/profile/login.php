<div id="login">
	<!--<button id="show_window">Настройка профиля</button>-->
	<div id="login-user">
		<!--<span id="login-user-text"></span>
		<span id="login-button_out">Выйти</span>-->

	</div>
	<div id="login-buttons">
		<span id="login-button_auth">Войти</span>
		<span id="login-button_reg">Зарегистрироваться</span>
	</div>
</div>

<!--<div class="window" id="tmp" >
	Бла бла бла бла
</div>-->

<div class="window" id="window-login-reg">
	<div id="login-reg">
		<div class="login-title">Создайте аккаунт на MIR-NDV</div>

		<div class="form_input_pos" input_name="login"></div>
		<div class="form_input_pos" input_name="pwd"></div>
		<div class="form_input_pos" input_name="pwd2"></div>
		<div class="form_input_pos" input_name="company"></div>
		<div class="form_input_pos" input_name="city"></div>

		<div class="form_input_pos" input_name="agent"></div>
		<a id="auth" href="javascript:void(0);">Войти</a>

		<label for="captcha">Введите текст с картинки:</label><br>
		<img class="hint" hint="Обновить" src = '' width="200" height="120" id='captcha-image' onclick='$("#window-reg #captcha-image").getCaptcha();'/><br>
		<a href="javascript:void(0);" tabindex="5" onclick='$("#window-reg #captcha-image").getCaptcha();'>Обновить</a><br>
		<div class="form_input_pos" input_name="captcha"></div>
	</div>
</div>

<div class="window" id="window-login-auth">
	<div id="login-auth">
		<div class="login-title">
			Войти через аккаунт MIR-NDV
		</div>

		<div class="form_input_pos" input_name="login"></div>
		<div class="form_input_pos" input_name="pwd"></div><br/>

		<button class="window-button" id="window-auth-submit">Войти</button>
		<div id="addition-ref">
			<a id="window-auth-reg" href="javascript:void(0)">Зарегистрироваться</a><br/>
			<a id="window-auth-restore_pwd" href="javascript:void(0)">Восстановить пароль</a>
		</div>
	</div>
</div>

<div class="window" id="window-select_city"></div>
