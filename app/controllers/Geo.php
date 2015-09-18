<?php
/**
 * User: DanVer
 * Date: 27.05.15
 * Time: 11:57
 */

//include_once 'core/helpers/utils.php';
include_once 'app/models/GeoModel.php';

class Geo extends Controller {
    function __construct() {
        
    }

    function index($args = null) {
        //
    }

	//функция возвращает список стран (для POST запросов)
	//ответ: массив
	//	при положительном ответе: $answer["success"] - ассоциативный массив:
	// 		"hand_enter" - возможность указание значения вручную вкл/выкл (не выбирая из списка)
	// 		"res" - массив регионов (поля массива code,name)
	//	при отрицательном ответе: $answer["error"] - текст ошибки
	function get_countries($args = null) {
		//return GeoCountry::findByField();
		$countries = GeoCountry::findByEnable(array("value" => "1"));
		$res = array();
		foreach($countries as $country)
			$res[] = array("code" => $country->COUNTRYCODE,"name" => $country->NAME);

		return array("success" => array("hand_enter" => 0,"res" => $res));
	}

	//функция возвращает список регионов (для POST запросов)
	//параметры в POST:
	//	code - идентификатор страны
	//ответ: массив
	//	при положительном ответе: $answer["success"] - ассоциативный массив:
	// 		"hand_enter" - возможность указание значения вручную вкл/выкл (не выбирая из списка)
	// 		"res" - массив регионов (поля массива code,name)
	//	при отрицательном ответе: $answer["error"] - текст ошибки
	function get_regions($args = null) {
		if(empty($_POST["code"])) return array("error" => "Не указан идентификатор страны");

		if($_POST["code"] != 643) return array("success" => array("hand_enter" => 1,"res" => array()));
		$regions = GeoRegion::findByFormalName(array("order" => "FORMALNAME"));
		$res = array();
		foreach($regions as $region)
			$res[] = array("code" => $region->REGIONCODE,"name" => $region->FORMALNAME." ".$region->SHORTNAME.".");

		return array("success" => array("hand_enter" => 0,"res" => $res));
	}

	//функция возвращает список районов и городов (для POST запросов)
	//параметры в POST:
	//	code - код выбранного региона
	//ответ: массив
	//	при положительном ответе: $answer["success"] - ассоциативный массив:
	// 		"hand_enter" - возможность указание значения вручную вкл/выкл (не выбирая из списка)
	// 		"res" - массив регионов (поля массива code,name)
	//	при отрицательном ответе: $answer["error"] - текст ошибки
	function get_cities($args = null) {
		if(empty($_POST["code"])) return array("error" => "Не указан код региона");
		if(!is_numeric($_POST["code"])) return array("error" => "Код региона некорректен");

		$rows = GeoCity::findByRegionCode(array(
			"fields" => array("AOLEVEL","REGIONCODE","AREACODE","CODE","CITYCODE","FORMALNAME","SHORTNAME"),
			"where" => "`AOLEVEL` IN (3,4)",
			"order" => array("FORMALNAME"),
			"value" => intval($_POST["code"])));

		$res = array();
		foreach($rows as $row) {
			$res[] = array(
				"code" => $row->CODE,
				"name" => $row->FORMALNAME." ".$row->SHORTNAME.".");
		}
		return array("success" => array("hand_enter" => 0,"res" => $res));
	}

	//функция возвращает список районов и городов (для POST запросов)
	//параметры в POST:
	//	code - код выбранного района или города
	//ответ: массив
	//	при положительном ответе: $answer["success"] - ассоциативный массив:
	// 		"hand_enter" - возможность указание значения вручную вкл/выкл (не выбирая из списка)
	// 		"res" - массив регионов (поля массива code,name)
	//	при отрицательном ответе: $answer["error"] - текст ошибки
	function get_places($args = null) {
		//проверяем данные
		if(empty($_POST["code"])) return array("error" => "Не указан код города или района");
		$code = $_POST["code"];

		//загружаем информацию о таблице для данного региона
		$regions = GeoRegion::findByRegionCode(array("fields" => "STREETTABLE", "value" => intval(substr($code,0,2))));
		if(empty($regions)) return array("error" => "Не удалось определить регион");

		$args = array(
			"fields" => array("CODE","FORMALNAME","SHORTNAME"),
			"where" => "`AOLEVEL` = 6",
			"order" => array("FORMALNAME"),
			"value" => substr($code,0,8)."%");
		if($regions[0]->STREETTABLE != null) {
			GeoStreet::setTableName("_".$regions[0]->STREETTABLE);
			$rows = GeoStreet::findByCODE($args);
		} else
			$rows = GeoPlace::findByCODE($args);

		$res = array();
		if(substr($code,5,3) !== "000") {
			$res[] = array(
				"code" => $code,
				"name" => "[Не указывать]",
				"no_select" => 1);
		}
		foreach($rows as $row) {
			$res[] = array(
				"code" => $row->CODE,
				"name" => $row->FORMALNAME." ".$row->SHORTNAME.".");
		}
		return array("success" => array("hand_enter" => 0,"res" => $res));
	}

	//функция возвращает список улиц (для POST запросов)
	//параметры в POST:
	//	code - код города или населенного пункта
	//ответ: массив
	//	при положительном ответе: $answer["success"] - ассоциативный массив:
	// 		"hand_enter" - возможность указание значения вручную вкл/выкл (не выбирая из списка)
	// 		"res" - массив регионов (поля массива code,name)
	//	при отрицательном ответе: $answer["error"] - текст ошибки
	function get_streets($args = null) {
		//проверяем данные
		if(empty($_POST["code"])) return array("error" => "Не указан код города или населенного пункта");
		$code = $_POST["code"];

		//загружаем информацию о таблице для данного региона
		$regions = GeoRegion::findByREGIONCODE(array("fields" => "STREETTABLE", "value" => intval(substr($code,0,2))));
		if(empty($regions)) return array("error" => "Не удалось определить регион");

		$rows = array();
		$hand_enter = 1;
		if($regions[0]->STREETTABLE != null) {
			GeoStreet::setTableName("_".$regions[0]->STREETTABLE);
			$args = array(
				"fields" => array("CODE","FORMALNAME","SHORTNAME"),
				"where" => "`AOLEVEL` = 7",
				"order" => array("FORMALNAME"),
				"value" => substr($code,0,11)."%");
			$rows = GeoStreet::findByCODE($args);
			$hand_enter = 0;
		}

		$res = array();
		foreach($rows as $row) {
			$res[] = array(
				"code" => $row->CODE,
				"name" => $row->FORMALNAME." ".$row->SHORTNAME.".");
		}
		return array("success" => array("hand_enter" => $hand_enter,"res" => $res));
	}

	function admin() {
		//
    }

    function statistic() {
        //
    }
}
