			<div class="contact_form FeedBack">
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
			

<img  src = 'captcha.php' width="200" height="60" id='capcha-image' /><br>
<a href="javascript:void(0);" onclick="document.getElementById('capcha-image').src='captcha.php?' + Math.random();">Обновить</a><br>
<input type="text" id="capcha" name="capcha" class="inp_txt invalid" maxlength="64" size="32" onblur="vakidinvalidFeedBack(this);" placeholder="Введите текст с картинки"><br>

<label>Я агент<input type="checkbox" id="igentFeedBack" name="igent" value="0" onclick="iagentFeedBack();"  style="width: 0px;"></label><br>
<button onclick='goFeedBack();'>Отправить</button>
			</div>