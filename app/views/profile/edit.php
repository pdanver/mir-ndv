<div class="window" id="profile_edit-customer-window">
	<div class="contact_form tabs-shell" id="profile_edit-customer">
		<ul class="tabs">
			<li><a title="profile_edit-customer-tab-main_params" href="javascript:void(0);">Основные параметры</a></li>
			<li><a title="profile_edit-customer-tab-contacts" href="javascript:void(0);">Контактная информация</a></li>
			<li><a title="profile_edit-customer-tab-access" href="javascript:void(0);">Доступ к данным</a></li>
			<li><a title="profile_edit-customer-tab-location" href="javascript:void(0);">Местоположение</a></li>
		</ul>

		<div class="tabs-content">
			<div class="tab-content" id="profile_edit-customer-tab-main_params">
				<div id="user_photo-cnt">
					<div id="user_photo-preview-cnt"><img id="user_photo-preview" src="/img/user.png"></div>
					<div class="form_input_pos" input_name="user_photo"></div>
					<div class="form_input_pos" input_name="user_photo_data"></div>
				</div>

				<div id="FIO">
					<div class="form_input_pos" input_name="user_surname"></div>
					<div class="form_input_pos" input_name="user_name"></div>
					<div class="form_input_pos" input_name="user_old_name"></div>
				</div><br/>
				<div class="form_input_pos" input_name="user_sex"></div><br/>
				<div class="form_input_pos" input_name="user_birthday"></div>
			</div>

			<div class="tab-content" id="profile_edit-customer-tab-contacts">
				<div class="profile_edit-customer-phones-cnt" style="display: table-row;">
					<div class="form_input_pos" input_name="user_phones"></div>
				</div>
				<div class="form_input_pos" input_name="user_jabber"></div>
				<div class="form_input_pos" input_name="user_skype"></div>
			</div>
			<div class="tab-content" id="profile_edit-customer-tab-access">
				<div id="change_pwd">
					<div class="form_input_pos" input_name="user_old_pwd"></div>
					<div class="form_input_pos" input_name="user_pwd1"></div>
					<div class="form_input_pos" input_name="user_pwd2"></div>
				</div><br/>
			</div>
			<div class="tab-content" id="profile_edit-customer-tab-location">
				<div class="location">
					<div class="form_input_pos" input_name="user_country"></div>
					<div class="form_input_pos" input_name="user_region"></div>
					<div class="form_input_pos" input_name="user_city"></div>
					<div class="form_input_pos" input_name="user_street"></div>
				</div><br/>
			</div>
		</div>
	</div>
</div>

<div class="window" id="photo_cutter-window">
	<div id="photo_cutter">
		<p>Выберите фрагмент картинки</p>
		<p><img id="photo_cutter-photo" src="" alt="" title="" style="margin: 0 0 0 10px;" /></p>
	</div>
</div>

<script>
	$(document).ready(function() {
		//initProfileEdit();
	});
</script>


