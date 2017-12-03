<?php
/**
 * 이 파일은 iModule 이메일모듈 일부입니다. (https://www.imodule.kr)
 *
 * 이메일 발송/전송기록과 관련된 모든 기능을 제어한다.
 *
 * @file /modules/email/ModuleEmail.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2017. 11. 22.
 */
class ModuleEmail {
	/**
	 * iModule 및 Module 코어클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private object $DB DB접속객체
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
	private $DB;
	private $table;
	
	/**
	 * 언어셋을 정의한다.
	 *
	 * @private object $lang 현재 사이트주소에서 설정된 언어셋
	 * @private object $oLang package.json 에 의해 정의된 기본 언어셋
	 */
	private $lang = null;
	private $oLang = null;
	
	/**
	 * 메일발송에 필요한 변수
	 *
	 * @private string $domain 메일을 발송하는 도메인주소 (@see $this->IM->doamin)
	 * @private string $language 메일을 발송하는 사이트의 언어셋 (@see $this->IM->languge)
	 * @private string[] $from 보내는사람 [이메일주소, 이름]
	 * @private string[] $to 받는사람 [이메일주소, 이름]
	 * @private string[] $replyTo 답장받는사람 [이메일주소, 이름]
	 * @private string[] $cc 참조 [이메일주소, 이름]
	 * @private string[] $bcc 숨은참조 [이메일주소, 이름]
	 * @private string $subject 제목
	 * @private string $content 내용
	 * @private string $templet 이메일 내용 템플릿
	 */
	private $domain = null;
	private $language = null;
	private $from = array();
	private $to = array();
	private $replyTo = array();
	private $cc = array();
	private $bcc = array();
	private $subject = null;
	private $content = null;
	private $templet = 'default';
	
	/**
	 * class 선언
	 *
	 * @param iModule $IM iModule 코어클래스
	 * @param Module $Module Module 코어클래스
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
		/**
		 * iModule 및 Module 코어 선언
		 */
		$this->IM = $IM;
		$this->Module = $Module;
		
		/**
		 * 모듈에서 사용하는 DB 테이블 별칭 정의
		 * @see 모듈폴더의 package.json 의 databases 참고
		 */
		$this->table = new stdClass();
		$this->table->send = 'email_send_table';
		$this->table->receiver = 'email_receiver_table';
	}
	
	/**
	 * 모듈 코어 클래스를 반환한다.
	 * 현재 모듈의 각종 설정값이나 모듈의 package.json 설정값을 모듈 코어 클래스를 통해 확인할 수 있다.
	 *
	 * @return Module $Module
	 */
	function getModule() {
		return $this->Module;
	}
	
	/**
	 * 모듈 설치시 정의된 DB코드를 사용하여 모듈에서 사용할 전용 DB클래스를 반환한다.
	 *
	 * @return DB $DB
	 */
	function db() {
		if ($this->DB == null || $this->DB->ping() === false) $this->DB = $this->IM->db($this->getModule()->getInstalled()->database);
		return $this->DB;
	}

	/**
	 * 모듈에서 사용중인 DB테이블 별칭을 이용하여 실제 DB테이블 명을 반환한다.
	 *
	 * @param string $table DB테이블 별칭
	 * @return string $table 실제 DB테이블 명
	 */
	function getTable($table) {
		return empty($this->table->$table) == true ? null : $this->table->$table;
	}
	
	/**
	 * [코어] 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $protocol API 호출 프로토콜 (get, post, put, delete)
	 * @param string $api API명
	 * @param any $idx API 호출대상 고유값
	 * @param object $params API 호출시 전달된 파라메터
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($protocol,$api,$idx=null,$params=null) {
		$data = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeGetApi',$this->getModule()->getName(),$api,$values);
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php';
		}
		
		/**
		 * @todo 기존 API 복구필요
		if ($api == 'send') {
			$errors = array();
			$sender_name = Request('sender_name');
			$sender_email = CheckEmail(Request('sender_email')) == true ? Request('sender_email') : $errors['sender_email'] = $this->getLanguage('error/sender');
			$reply_name = Request('reply_name');
			$reply_email = CheckEmail(Request('reply_email')) == true ? Request('reply_email') : null;
			$bcc_name = Request('bcc_name');
			$bcc_email = CheckEmail(Request('bcc_email')) == true ? Request('bcc_email') : null;
			$receiver_name = Request('receiver_name');
			$receiver_email = CheckEmail(Request('receiver_email')) == true ? Request('receiver_email') : $errors['receiver_email'] = $this->getLanguage('error/receiver');
			$subject = Request('subject') ? Request('subject') : $errors['subject'] = $this->getLanguage('error/subject');
			$content = Request('content') ? Request('content') : $errors['content'] = $this->getLanguage('error/content');
			$isHtml = Request('is_html') == 'true';
			
			if (count($errors) == 0) {
				$this->setFrom($sender_email,$sender_name);
				$this->addTo($receiver_email,$receiver_name);
				$this->setSubject($subject);
				$this->setContent($content,$isHtml);
				
				if ($reply_email == null) {
					$reply_email = $sender_email;
					$reply_name = $sender_name;
				}
				$this->setReplyTo($reply_email,$reply_name);
				
				if ($bcc_email !== null) {
					$this->addBcc($bcc_email,$bcc_name);
				}
				
				$this->send();
				
				$data->success = true;
				$data->message = $this->getLanguage('success');
			} else {
				$data->success = false;
				$data->errors = $errors;
			}
		}
		**/
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterGetApi',$this->getModule()->getName(),$api,$values,$data);
		
		return $data;
	}
	
	/**
	 * [사이트관리자] 모듈 설정패널을 구성한다.
	 *
	 * @return string $panel 설정패널 HTML
	 */
	function getConfigPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Module = $this->getModule();
		
		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/configs.php';
		$panel = ob_get_contents();
		ob_end_clean();
		
		return $panel;
	}
	
	/**
	 * [사이트관리자] 모듈 관리자패널 구성한다.
	 *
	 * @return string $panel 관리자패널 HTML
	 */
	function getAdminPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Module = $this;

		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/index.php';
		$panel = ob_get_contents();
		ob_end_clean();

		return $panel;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @param string $replacement 일치하는 언어코드가 없을 경우 반환될 메세지 (기본값 : null, $code 반환)
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getText($code,$replacement=null) {
		if ($this->lang == null) {
			if (is_file($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->getModule()->getPackage()->language && is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
					$this->oLang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				}
			} elseif (is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}

		$returnString = null;
		$temp = explode('/',$code);

		$string = $this->lang;
		for ($i=0, $loop=count($temp);$i<$loop;$i++) {
			if (isset($string->{$temp[$i]}) == true) {
				$string = $string->{$temp[$i]};
			} else {
				$string = null;
				break;
			}
		}

		if ($string != null) {
			$returnString = $string;
		} elseif ($this->oLang != null) {
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) {
						$string = $string->{$temp[$i]};
					} else {
						$string = null;
						break;
					}
				}
			}

			if ($string != null) $returnString = $string;
		}
		
		$this->IM->fireEvent('afterGetText',$this->getModule()->getName(),$code,$returnString);
		
		/**
		 * 언어셋 텍스트가 없는경우 iModule 코어에서 불러온다.
		 */
		if ($returnString != null) return $returnString;
		elseif (in_array(reset($temp),array('text','button','action')) == true) return $this->IM->getText($code,$replacement);
		else return $replacement == null ? $code : $replacement;
	}

	/**
	 * 상황에 맞게 에러코드를 반환한다.
	 *
	 * @param string $code 에러코드
	 * @param object $value(옵션) 에러와 관련된 데이터
	 * @param boolean $isRawData(옵션) RAW 데이터 반환여부
	 * @return string $message 에러 메세지
	 */
	function getErrorText($code,$value=null,$isRawData=false) {
		$message = $this->getText('error/'.$code,$code);
		if ($message == $code) return $this->IM->getErrorText($code,$value,null,$isRawData);

		$description = null;
		switch ($code) {
			case 'NOT_ALLOWED_SIGNUP' :
				if ($value != null && is_object($value) == true) {
					$description = $value->title;
				}
				break;

			case 'DISABLED_LOGIN' :
				if ($value != null && is_numeric($value) == true) {
					$description = str_replace('{SECOND}',$value,$this->getText('text/remain_time_second'));
				}
				break;

			default :
				if (is_object($value) == false && $value) $description = $value;
		}

		$error = new stdClass();
		$error->message = $message;
		$error->description = $description;
		$error->type = 'BACK';

		if ($isRawData === true) return $error;
		else return $this->IM->getErrorText($error);
	}
	/*
	public $table = array();

	protected $PHPMailer;
	protected $templet;
	protected $from;
	protected $to;
	protected $toList = array();
	protected $subject;
	protected $body;

	public $userfile;
	public $thumbnail;
	
	public function __construct($isSMTP=true) {
		$this->table['email'] = $_ENV['code'].'_email_table';
		$this->table['file'] = $_ENV['code'].'_email_file_table';
		$this->table['send'] = $_ENV['code'].'_email_send_table';
		$this->table['temp'] = $_ENV['code'].'_email_temp_table';
		$this->templet = '';

		parent::__construct('email');

		$phpMailer = new PHPMailer();
		$phpMailer->PluginDir = $this->modulePath.'/class/';

		if ($isSMTP == true && $this->module['smtp_server']) {
			$phpMailer->IsSMTP();
			$phpMailer->SMTPSecure = $this->module['smtp_secure'];
			$phpMailer->Host = $this->module['smtp_server'];
			$phpMailer->Port = $this->module['smtp_port'];

			if ($this->module['smtp_user'] && $this->module['smtp_password']) {
				$phpMailer->SMTPAuth = true;
				$phpMailer->Username = $this->module['smtp_user'];
				$phpMailer->Password = $this->module['smtp_password'];
			}
		}

		$phpMailer->IsHTML(true);
		$phpMailer->Encoding = 'base64';
		$phpMailer->CharSet = 'UTF-8';

		$phpMailer->SetFrom($this->module['email'], '=?UTF-8?b?'.base64_encode($this->module['name']).'?=');
		$this->from = array($this->module['name'],$this->module['email']);
		
		$this->userfile = '/email';
		$this->thumbnail = '/email/thumbnail';
	}

	public function SetTemplet($templet) {

	}

	public function GetTemplet() {
		if ($this->templet) {

		} else {
			$templet = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style type="text/css">BODY, TH, TD, DIV, SPAN, P, INPUT {font-size:12px; line-height:17px;} BODY, DIV {text-align:justify;}</style></head><body>{$content}</body></html>';
		}

		return $templet;
	}
	*/
	
	/**
	 * 메일발송에 사용된 변수를 초기화한다.
	 */
	function reset() {
		$this->from = array();
		$this->to = array();
		$this->replyTo = array();
		$this->bcc = array();
		$this->cc = array();
		$this->subject = null;
		$this->content = null;
		$this->templet = null;
		$this->domain = null;
		$this->language = null;
	}
	
	/**
	 * 메일을 발송하는 사이트를 지정한다.
	 * 지정하지 않을 경우 현재 접속중인 사이트로 설정한다.
	 *
	 * @param string $domain 도메인명
	 * @param string $language 언어셋명
	 * @return ModuleEmail $this
	 */
	function setDomain($domain,$language=null) {
		$this->domain = $domain;
		$this->language = $language;
		return $this;
	}
	
	function setFrom($email,$name=null) {
		$this->from = array($email,$name);
		/*
		if ($email != null && $name != null && $email) {
			$phpMailer->SetFrom($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			if ($name) $this->from = array($name,$email);
			else $this->from = array('',$email);
		} elseif ($email != null && $email) {
			$phpMailer->SetFrom($email, '=?UTF-8?b?'.base64_encode($this->module['name']).'?=');
			$this->from = array($this->module['name'],$email);
		} elseif ($name != null) {
			$phpMailer->SetFrom($this->module['email'], '=?UTF-8?b?'.base64_encode($name).'?=');
			if ($name) $this->from = array($name,$this->module['email']);
			else $this->from = array('',$this->module['email']);
		}
		*/
		return $this;
	}
	
	function setReplyTo($email,$name=null) {
		$this->replyTo = array($email,$name);
		
		return $this;
	}
	
	function setSubject($subject) {
		$this->subject = $subject;
		
		return $this;
	}

	public function setContent($content,$isHtml=false) {
		if ($isHtml == false) {
			$this->content = nl2br($content);
		} else {
			$this->content = $content;
		}
		
		return $this;
	}

	public function addTo($email,$name='') {
		$this->to[] = array($email,$name);
		/*
		if ($name) {
			$phpMailer->AddAddress($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->to = array($name,$email);
		} else {
			$phpMailer->AddAddress($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->to = array('',$email);
		}
		*/
		return $this;
	}
	
	public function addBcc($email,$name) {
		$this->bcc[] = array($email,$name);
		
		return $this;
	}
/*
	public function AddAttach($filename,$filepath) {
		if (file_exists($filepath) == false) $filepath = $_ENV['path'].$filepath;

		if (file_exists($filepath) == true && filesize($filepath) < 10*1024*1024) {
			$phpMailer->AddAttachment($filepath,$filename);
		}
	}
*/
	
	
	/**
	 * 메일본문에 사용할 템플릿을 지정한다.
	 *
	 * @param string $templet 템플릿명
	 * @return ModuleEmail $this
	 */
	function setTemplet($templet) {
		$this->templet = $templet;
		return $this;
	}

	function makeTemplet() {
		if ($this->templet == null) return '<div>'.$this->content.'</div>';
		
		$templet = file_get_contents($this->getModule()->getPath().'/templets/'.$this->templet.'/index.html');
		$templet = str_replace('{DIR}',$this->IM->getHost(true),$templet);
		
		$templet = str_replace('{SITETITLE}',$this->IM->getSiteTitle(),$templet);
		$templet = str_replace('{SITELOGO}',$this->IM->getSiteLogo('default',true) != null ? $this->IM->getSiteLogo('default',true) : $this->IM->getHost(true).'/images/logo/default.png',$templet);
		
		$templet = str_replace('{SUBJECT}',$this->subject,$templet);
		$templet = str_replace('{CONTENT}',$this->content,$templet);
		
		return $templet;
	}

	public function preview() {
		ob_start();
		
		if ($this->templet == null) {
			echo '<h1>'.$this->subject.'</h1>';
			echo $this->content;
		} else {
			echo $this->makeTemplet();
		}
		
		$preview = ob_get_contents();
		ob_end_clean();
		
		echo $preview;
	}
	
	/**
	 * 설정된 변수를 이용하여 메일을 발송한다.
	 *
	 * @param boolean $isEach 여러명에게 메일을 보낼때 각자 보낼지 설정한다. (false 일 경우 모든 받는사람은 BCC로 지정되어 발송된다.)
	 * @return boolean $success
	 */
	public function send($isEach=false) {
		REQUIRE_ONCE $this->getModule()->getPath().'/classes/phpmailer/class.phpmailer.php';
		
		if (empty($this->to) == true || $this->subject == null || $this->content == null) return false;
		if (empty($this->from) == true) $this->from = array('arzz@arzz.com','알쯔닷컴');
		
		$phpMailer = new PHPMailer();
		$phpMailer->pluginDir = $this->getModule()->getPath().'/classes/phpmailer';
		$phpMailer->isHTML(true);
		$phpMailer->Encoding = 'base64';
		$phpMailer->CharSet = 'UTF-8';
		
		if (count($this->from) == 2) $phpMailer->setFrom($this->from[0],'=?UTF-8?b?'.base64_encode($this->from[1]).'?=');
		else $phpMailer->setFrom($this->from[0]);
		
		if (count($this->replyTo) == 1) $phpMailer->addReplyTo($this->replyTo[0]);
		elseif (count($this->replyTo) == 2) $phpMailer->addReplyTo($this->replyTo[0],'=?UTF-8?b?'.base64_encode($this->replyTo[1]).'?=');
		
		if (count($this->cc) > 0) {
			for ($i=0, $loop=count($this->cc);$i<$loop;$i++) {
				if (count($this->cc[$i]) == 2) $phpMailer->addBcc($this->cc[$i][0],'=?UTF-8?b?'.base64_encode($this->cc[$i][1]).'?=');
				else $phpMailer->addBcc($this->cc[$i][0]);
			}
		}
		
		if (count($this->bcc) > 0) {
			for ($i=0, $loop=count($this->bcc);$i<$loop;$i++) {
				if (count($this->bcc[$i]) == 2) $phpMailer->addBcc($this->bcc[$i][0],'=?UTF-8?b?'.base64_encode($this->bcc[$i][1]).'?=');
				else $phpMailer->addBcc($this->bcc[$i][0]);
			}
		}
		
		$phpMailer->Subject = '=?UTF-8?b?'.base64_encode($this->subject).'?=';
		
		$idx = $this->db()->insert($this->table->send,array('from'=>(empty($this->from[1]) == true ? $this->from[0] : $this->from[1].' <'.$this->from[0].'>'),'subject'=>$this->subject,'content'=>$this->content,'search'=>GetString($this->content,'index'),'receiver'=>count($this->to),'reg_date'=>time()))->execute();;
		
		if ($isEach == true || count($this->to) == 1) {
			for ($i=0, $loop=count($this->to);$i<$loop;$i++) {
				$receiverIdx = $this->db()->insert($this->table->receiver,array('parent'=>$idx,'to'=>empty($this->to[$i][1]) == true ? $this->to[$i][0] : $this->to[$i][1].' <'.$this->to[$i][0].'>','reg_date'=>time()))->execute();
				
				$phpMailer->clearAddresses();
				
				if (count($this->to[$i]) == 2) $phpMailer->addAddress($this->to[$i][0],'=?UTF-8?b?'.base64_encode($this->to[$i][1]).'?=');
				else $phpMailer->addAddress($this->to[$i][0]);
				
				$phpMailer->Body = $this->makeTemplet().PHP_EOL.'<img src="'.$this->IM->getHost().$this->IM->getProcessUrl('email','check',array('receiver'=>$receiverIdx)).'" style="width:1px; height:1px;" />';
				$result = $phpMailer->send();
				
				if ($result == true) {
					$this->db()->update($this->table->receiver,array('status'=>'SUCCESS'))->where('idx',$receiverIdx)->execute();
				} else {
					$this->db()->update($this->table->receiver,array('status'=>'FAIL','message'=>$result))->where('idx',$receiverIdx)->execute();
				}
			}
		} else {
			if (count($this->from) == 2) $phpMailer->addAddress($this->from[0],'=?UTF-8?b?'.base64_encode($this->from[1]).'?=');
			else $phpMailer->addAddress($this->from[0]);
			
			for ($i=0, $loop=count($this->to);$i<$loop;$i++) {
				if (count($this->to[$i]) == 2) $phpMailer->addBCC($this->to[$i][0],'=?UTF-8?b?'.base64_encode($this->to[$i][1]).'?=');
				else $phpMailer->addBCC($this->to[$i][0]);
			}
		}
		
		$this->reset();
		return;
	}
	
	/**
	 * 현재 모듈에서 처리해야하는 요청이 들어왔을 경우 처리하여 결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 * 작업코드가 '@' 로 시작할 경우 사이트관리자를 위한 작업으로 최고관리자 권한이 필요하다.
	 *
	 * @param string $action 작업코드
	 * @return object $results 수행결과
	 * @see /process/index.php
	 */
	function doProcess($action) {
		$results = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeDoProcess',$this->getModule()->getName(),$action,$values);
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/process/'.$action.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterDoProcess',$this->getModule()->getName(),$action,$values,$results);
		
		return $results;
	}
}
?>