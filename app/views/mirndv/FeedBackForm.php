<div class="contact_form FeedBack" style="display: none;">
	<h1>Форма обратной связи</h1>
	<p>У вас есть вопрос или предложение, напишите нам. С вами свяжутся.</p>
	<p class="error" ></p>
	<input type="text" id="nameFeedBack" name="name" class="invalid" placeholder="{name}" onblur="vakidinvalidFeedBack(this);"  />
	<br>

	<input type="text" name="siti" class="invalid" placeholder="{siti}" onblur="vakidinvalidFeedBack(this);"  />
	<br>

	<input type="text" name="email" class="invalid" placeholder="{Email}" onblur="vakidinvalidFeedBack(this);"  />
	<br>

	<select id="selectorat">{select}</select>
	<br>
	<textarea name="msg" placeholder="Сообщение" class="invalid" onblur="vakidinvalidFeedBack(this);" ></textarea>

	<br>	<br>

	<!-- <label for="captcha">Введите текст с картинки:</label><br> -->
	<a href="javascript:void(0);" tabindex="5" onclick="getCaptcha();">
	<img src = '' width="200" height="120" id='captcha-image'/>
	</a><br>


	<input type="text" id="captcha" name="captcha" class="inp_txt invalid" maxlength="64" size="32" onblur="vakidinvalidFeedBack(this);" style="padding-right: 0px;" placeholder="Введите текст с картинки"><br>

	<label>Я агент<input type="checkbox" id="igentFeedBack" name="igent" value="0" onclick="iagentFeedBack();"  style="width: 0px;"></label><br>
	<!-- <button onclick='goFeedBack();'>Отправить</button> -->
</div>