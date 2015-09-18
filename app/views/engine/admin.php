<div class="adminList">
    <table>
        <tr><td colspan="6"><label class="title">Основные настройки</label></td></tr>
        <tr>
            <td>
                <label>site on/off</label><br>
                <input type="checkbox" id="site"{SITE}>
            </td>
            <td>
                <label>Мультиязычность на сайте</label><br>
                <input type="checkbox" id="multiLang"{MULTI_LANG}>
            </td>
            <td>
                <label>Язык по умолчанию</label><br>
                <select id="lang">{LANGUAGES}</select>
            </td>
            <td>
                <label>Использовать JS интерфейс</label><br>
                <input type="checkbox" id="useJS"{USE_JS}>
            </td>
            <td>
                <label>Шаблон по умолчанию</label><br>
                <select id="template">
                    {TEMPLATES}
                </select>
            </td>
        </tr>
        <tr><td colspan="6"><hr></td></tr>
        <tr>
            <td colspan="6">
                <div>
                    <label>Проверять IP</label>
                    <select id="checkIP">
                        <option value="0"{IPN}>не проверять</option>
                        <option value="2"{IPR}>разрешать</option>
                        <option value="1"{IPF}>запрещать</option>
                    </select><br>
                    <textarea id="ipList" rows="10" cols="30">{IP_LIST}</textarea><br>
                </div>
                <div>
                    <label>Проверять запрещенные устройства</label>
                    <select id="checkStuff">
                        <option value="0"{SN}>не проверять</option>
                        <option value="2"{SR}>разрешать</option>
                        <option value="1"{SF}>запрещать</option>
                    </select><br>
                    <textarea id="stuffList" rows="10" cols="30">{F_STUF}</textarea><br>
                </div>
            </td>
        </tr>
        <tr><td colspan="6"><hr></td></tr>
        <tr><td colspan="6"><label>Controllers redact</label></td></tr>
        <tr>
            <td colspan="6">
                <div>
                    <table>
                        <tr><th>Название</th><th>alias</th><th>img</th><th>date</th><th>action</th></tr>
                        {CONTROLLERS}
                    </table>
                </div>
            </td>
        </tr>
        <tr><td colspan="6"><hr></td></tr>
        <tr>
            <td colspan="6">
                <label>Использовать маску ввода</label><br>
                <input type="checkbox" id="maskURI"{MASK_URI}>
                {MASK_TABLE}
            </td>
        </tr>
    </table>
</div>