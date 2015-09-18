<?php
class FeedBackModel
{
	//
	static function getSelect()
	{
		Database::DB()->reset();
		Database::DB()->select('feedback_set')->where("status",1,"=")->exec();
		$res = Database::get();
		$resultat = '<option selected disabled >Тема письма</option>';
		foreach ( $res as $key => $value ) {
			$resultat .=	'<option value="'.$value["id"].'">'.$value["text"].'</option>';
		}
		$resultat .= '<option value="0">Другое</option>';

		return $resultat;
	}

	static function goFeedBack($p)
	{
		// { setid: "1", msg: "4", name: "111", siti: "2", email: "3", capcha: "5", igent: "0", request_type: 1 }

		$arr = array(
			"name" => $p['name'],
			"igent" => $p['igent'],
			"siti" => $p['siti'],
			"email" => $p['email'],
			"setid" => $p['setid'],
			"text" => $p['msg'],
			"pind" => self::pind()
		);

		if(Database::DB()->insert('feedback_obro',$arr) >= 0){
			///////////////  Нежно вставить маил ..............
			return $arr['pind'];

		}else{
			return null;
		}

	}
	static function pind($y=10)
	{
		$pind = null;
		$x=0;
		while ($x<$y)
		{
			$x++;
			$pind = self::rand_str(10);
			Database::DB()->reset();
			Database::DB()->select('feedback_obro')->where("pind",$pind,"=")->exec();
			$res = Database::get();
			if($fes == null && $fes != $pind){break;}
		}
		return $pind;
	}

	static function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		$chars_length = (strlen($chars) - 1);
		$string = $chars{rand(0, $chars_length)};
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1}) $string .=  $r;
		}
		return $string;
	}
}
?>