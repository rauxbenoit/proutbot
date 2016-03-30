<?php
class ProutBot {

	private $strUrlApi = 'https://api.telegram.org';
	private $strBotToken = '';
	private $strUrlApiToken = '';

	public function __construct() {
		$this->strBotToken = exec("more api_token");
		$this->strUrlApiToken = $this->strUrlApi . '/bot' . $this->strBotToken . '/';
	}

	public function parseApiUpdate(){
		$argUpdate = $this->returnApiUpdate();
		//$intLastUpdate = exec("more last_update_date");
		foreach($argUpdate->result as $objUpdate ){
			//if($objUpdate->message->date > $intLastUpdate){
				$arrText = @explode(' ',$objUpdate->message->text);
				$strCommand = str_replace(array('/','@ProutBot'),'',$arrText[0]);
				$this->execCommand($strCommand,$objUpdate);
				//print_r($objUpdate);
				//exec("echo '" . $objUpdate->message->date . "' > last_update_date");
				exec("echo '" . $objUpdate->update_id . "' > last_update_id");
			}
		//}
	}

	public function execCommand($strCommand,$objUpdate){
		switch($strCommand){
			case 'prout':
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => 'Et un prout de ' . (!empty($objUpdate->message->from->username)?$objUpdate->message->from->username:'l\'idiot qui n\'a pas set son username' . ' !')));
				$argRequest = $this->apiRequest("sendAudio", array('chat_id' => $objUpdate->message->chat->id, "audio" => 'BQADBAADDgADpUtcDIyJX-pPDti1Ag'));
			break;
			case 'help':
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => 'Démerdes toi sale prouteu !'));
			break;
			case 'caca':
			case 'beer':
			case 'pipi':
			case 'vomi':
				$argRequest = $this->apiRequest("sendPhoto", array('chat_id' => $objUpdate->message->chat->id, "photo" => $this->returnRandomPhoto($strCommand)));
			break;
			case 'fact':
				$strJson = file_get_contents('http://www.chucknorrisfacts.fr/api/get?data=tri:alea;nb:1;type:text');
				$arrJson = json_decode($strJson);
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => str_replace('&#039;','',html_entity_decode($arrJson[0]->fact))));
			break;
			case 'boobs':
				$strJson = file_get_contents('http://api.oboobs.ru/noise/1');
				$arrJson = json_decode($strJson);
				$strUrl = 'http://media.oboobs.ru/' . str_replace('_preview','',$arrJson[0]->preview);
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => $strUrl));
			break;
			case 'butts':
				$strJson = file_get_contents('http://api.obutts.ru/noise/1');
				$arrJson = json_decode($strJson);
				$strUrl = 'http://media.obutts.ru/' . str_replace('_preview','',$arrJson[0]->preview);
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => $strUrl));
			break;
			case 'bonjourmadame':
				$arrText = @explode(' ',$objUpdate->message->text);
				$intStart = 0;
				$intNum = 1;
				if(!empty($arrText[1])){
					$intStart = $arrText[1];
				}
				if(!empty($arrText[2])){
					$intNum = $arrText[2];
				}
				$objUpdate->message->text = 'tumblr ' . $intStart . ' ' . $intNum . ' bonjourmadame.fr';
				$this->execCommand('tumblr',$objUpdate);
			break;
			case 'tumblr':
				$arrText = @explode(' ',$objUpdate->message->text);
				$strTumblrUrl = 'sexygirlsandporn.tumblr.com';
				$intStart = 0;
				$intNum = 1;
				if(!empty($arrText[1])){
					$intStart = $arrText[1];
				}
				if(!empty($arrText[2])){
					$intNum = $arrText[2];
				}
				if($intNum > 20){
					$intNum = 20;
				}
				if(!empty($arrText[3])){
					$strTumblrUrl = $arrText[3];
				}

				$strTumblrPostUrl = 'http://' . $strTumblrUrl . '/api/read/?&start=' . $intStart . '&num=' . $intNum . '&type=photo';
				$arrXml = simplexml_load_file($strTumblrPostUrl);
				for ($i = 0; $i < count($arrXml->posts->post); $i++) {
						$strImgUrl = (string)$arrXml->posts->post[$i]->{'photo-url'}[0];
						if(!empty($strImgUrl)){
							$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => $strImgUrl));
							sleep(1);
						}
				}
			break;
			case 'hello':
				for($i=1;$i<=5;$i++){
					$this->execCommand('boobs',$objUpdate);
					$this->execCommand('butts',$objUpdate);
					sleep(1);
				}
				$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => (!empty($objUpdate->message->from->username)?$objUpdate->message->from->username:'l\'idiot qui n\'a pas set son username') . ' vous souhaite bien le bonjour ^^'));
			break;
			case 'blague':
				$str = '';
				$strHtml = file_get_contents('http://humour-blague.com/blagues-2/index.php');
				preg_match('/<p align="left" class="blague">(.*?)<\/p>/s', $strHtml, $arrMatches);
				$str = trim(html_entity_decode(strip_tags($arrMatches[1])));
				$str = str_replace("\t",'',$str);
				$str = str_replace('  ','' ,$str);
				print_r($str);
				if(!empty($str)){
					$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => $str));
				}
			break;
			default:
				//$argRequest = $this->apiRequest("sendMessage", array('chat_id' => $objUpdate->message->chat->id, "text" => 'Et un prout foiré de ' . $objUpdate->message->from->username . ' !'));
			break;
		}
	}

	public function returnApiUpdate(){
		$strApiMethod = 'getUpdates';
		$arrParam = array();
		$intLastUpdateId = exec("more last_update_id");
		if(is_numeric($intLastUpdateId)){
			$arrParam['offset'] = $intLastUpdateId + 1;
		}
		return $this->apiRequest($strApiMethod, $arrParam);
	}

	protected function apiRequest($strApiMethod, $arrParam = array()){
		$arg = null;
		$strUrl = $this->strUrlApiToken . $strApiMethod;
		if(!empty($arrParam)){
			foreach ($arrParam as $strKey => &$argVal) {
		    if (!is_numeric($argVal) && !is_string($argVal)) {
		      $argVal = json_encode($argVal);
		    }
		  }
			$strUrl.= '?'.http_build_query($arrParam);
		}
		$strApiResponse = file_get_contents($strUrl);
		if(!empty($strApiResponse)){
			$arg = json_decode($strApiResponse);
		}
		return $arg;
	}

	public function returnRandomPhoto($strCommand){
		$arr = $this->returArrPhoto($strCommand);
		return $arr[rand (0,(count($arr)-1))];
	}

	public function returArrPhoto($strCommand = ''){
		$arr = array(
			'caca'=>array(
				'AgADBAADtacxG6VLXAzfdKiypSscCaXzJRkABHypG_hQahWkCl0BAAEC',
				'AgADBAADw6cxG6VLXAwVLaW_k98qM3PxJRkABEsjJDkqOv_OH3oBAAEC',
				'AgADBAADwqcxG6VLXAykSiCqTUFQnAwYHRkABLa7-hm6KKXmBIwBAAEC',
				'AgADBAADwacxG6VLXAxeV8NiqfAIy4hkGxkABPSG-7LuVcIhVJEBAAEC',
			),
			'beer'=>array(
				'AgADBAADuKcxG6VLXAzv7oqfhU7dHnS2JRkABNGPC8RfePloLnsBAAEC',
				'AgADBAADx6cxG6VLXAz0mkh2TSAufnvcKBkABNNYYNTCZu6_0KAAAgI',
				'AgADBAADxacxG6VLXAzEzVLI_OTqdwN1JBkABI4p_ncqMpzHu3kBAAEC',
				'AgADBAADxKcxG6VLXAyL8SRI32TyjQJMKRkABDUeHF1gqXi5WaEAAgI',
				'AgADBAADyKcxG6VLXAw9RUYo1LmD2XYuGxkABGCV37GIRaLNPJ4BAAEC',
				'AgADBAADyacxG6VLXAysU5VYAliwNw8vGxkABLAszpnVtmq2iKABAAEC',
				'AgADBAADyqcxG6VLXAwhbstJSoEYnCMjHRkABKaT6dUlwcPGupEBAAEC',
				'AgADBAADy6cxG6VLXAwDTVKx2Ux_AZfmJRkABGM8oMkYuATHmngBAAEC',
				'AgADBAADzKcxG6VLXAxu7Sa6bMBLSdQwJBkABFO4IYlAjW-DSbQAAgI',
				'AgADBAADzacxG6VLXAzfwnBnK8s5bbK0JRkABAwrYS6x4_-uinwBAAEC',
				'AgADBAADzqcxG6VLXAxCBjc6Y6_5Xqc8JBkABK_0WXW7kShkhHwBAAEC',
				'AgADBAADz6cxG6VLXAwheba916sa6IFLGxkABGECP6eqlQneUqMBAAEC',
				'AgADBAAD0KcxG6VLXAy0rgwXLzKSOk31JRkABJZOuai3qTu8WH4BAAEC',
				'AgADBAAD0acxG6VLXAxEkpmUBg1ReJczGxkABDh7TZRtwEKr8aEBAAEC',
				'AgADBAAD0qcxG6VLXAyy928k9IMkkagvGxkABAn81VibKNVUQZ8BAAEC',
				'AgADBAAD06cxG6VLXAzR4asxkL2cFWt0JBkABGbnJmhXC81SL38BAAEC',
				'AgADBAAD1KcxG6VLXAztJgvNoQOb8CczJBkABM5qVbvNSfjKm7YAAgI',
				'AgADBAAD1acxG6VLXAx6Nzxwq_AtCi4FHRkABMRtSRF_k6glEGwCAAEC',
				'AgADBAAD1qcxG6VLXAz-5Alxea4Gom0THRkABKrnQ8KwTBP3D5MBAAEC',
				'AgADBAAD16cxG6VLXAzhUjZJYtffi11IGxkABKAklK4rJDc2jKEBAAEC',
				'AgADBAAD2KcxG6VLXAziP2P_ZSlFPdAHHRkABEipTn6UL31QLGECAAEC',
				'AgADBAAD2acxG6VLXAymdAwKJr0eXLY9JBkABPZDET30JUV9SHsBAAEC',
				'AgADBAAD2qcxG6VLXAxB4z37-Yg6h8X1KBkABBMHkA6b8L6_n6AAAgI',
				'AgADBAAD26cxG6VLXAzUFpniBIoQOsgBHRkABDag05h0FWQNxYwBAAEC',
				'AgADBAAD3KcxG6VLXAwcLsNKKyyetGXeKxkABP7PMR-ilSFt2aAAAgI',
				'AgADBAAD3acxG6VLXAyXszQTf9Ltpy5zJBkABMaKYZVoy2ea8n8BAAEC',
				'AgADBAAD3qcxG6VLXAz5BU7OMopaa91bGxkABCFGbDdzQqnNMZ8BAAEC',
				'AgADBAAD36cxG6VLXAxPoAjzWVOxqXEEGxkABNO_G36lX8JbUo0BAAEC',
				'AgADBAAD4KcxG6VLXAwy-F-loVWFjXr7HBkABObSC1mauy97MI4BAAEC',
				'AgADBAAD4acxG6VLXAzWaOsMJKEXtkZKKRkABK7MWNCTuLa-QqEAAgI',
				'AgADBAAD4qcxG6VLXAxP_tFH6VrrS-GDJBkABLa8JmYZWvyLG6AAAgI',
				'AgADBAAD46cxG6VLXAz0tr7--HFaBgrmKBkABNAIYrwAAdILBCufAAIC',
				'AgADBAAD5KcxG6VLXAyAu7b_YMyvGyU-JBkABMoihHShpRhHNnsBAAEC',
				'AgADBAAD5acxG6VLXAxnowuy4VQPEdTMHBkABIxL_DbUjtaPgLoBAAEC',
			),
			'vomi'=>array(
				'AgADBAADwKcxG6VLXAyTCCDcjPAYskhdGxkABH5S5E6dQDvCnqIBAAEC',
				'AgADBAADv6cxG6VLXAwo3MACzvqczfPuJRkABLjPY_rTKBpedYEBAAEC',
				'AgADBAADvqcxG6VLXAwmxYCXRYh6hM3UKxkABJW56pu62o2_yJ4AAgI',
				'AgADBAADvacxG6VLXAyFzPFmmTdXsmPxJRkABIg8WGnhiigKznwBAAEC',
			),
			'pipi'=>array(
				'AgADBAADuacxG6VLXAwyid53qbnSbsT_HBkABO8RU9-vkdm-VY0BAAEC',
				'AgADBAADuqcxG6VLXAxoVl42eNLVoDslGxkABIv5bBJK8jYMWo0BAAEC',
				'AgADBAADu6cxG6VLXAyuDAW6iluPrNYuGxkABPaZ-lGQ7ibOoZ0BAAEC',
				'AgADBAADvKcxG6VLXAyKeM3DfqTAfilNGxkABC-zC_4Xopi7xp4BAAEC',
			),
		);
		if(!empty($strCommand)){
			$arr = $arr[$strCommand];
		}
		return $arr;
	}


}

$objBot = new ProutBot();
$objBot->parseApiUpdate();
