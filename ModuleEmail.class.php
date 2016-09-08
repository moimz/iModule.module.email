<?php
class ModuleEmail {
	private $IM;
	private $Module;
	
	private $language = null;
	private $lang = null;
	private $table;
	private $domain = null;
	
	private $from = array();
	private $to = array();
	private $replyTo = array();
	private $cc = array();
	private $bcc = array();
	private $subject = null;
	private $content = null;
	private $templet = null;
	
	function __construct($IM,$Module) {
		$this->IM = $IM;
		$this->Module = $Module;
		
		$this->table = new stdClass();
		$this->table->send = 'email_send_table';
		$this->table->receiver = 'email_receiver_table';
	}
	
	function db() {
		return $this->IM->db($this->Module->getInstalled()->database);
	}
	
	/**
	 * Get language string from language code
	 *
	 * @param string $code language code (json key)
	 * @return string language string
	 */
	function getLanguage($code) {
		if ($this->lang == null) {
			if (file_exists($this->Module->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->Module->getPackage()->language) {
					$this->oLang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				}
			} else {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$temp = explode('/',$code);
		if (count($temp) == 1) {
			return isset($this->lang->$code) == true ? $this->lang->$code : ($this->oLang != null && isset($this->oLang->$code) == true ? $this->oLang->$code : '');
		} else {
			$string = $this->lang;
			for ($i=0, $loop=count($temp);$i<$loop;$i++) {
				if (isset($string->{$temp[$i]}) == true) $string = $string->{$temp[$i]};
				else $string = null;
			}
			
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) $string = $string->{$temp[$i]};
					else $string = null;
				}
			}
			return $string == null ? '' : $string;
		}
	}
	
	function getTable($table) {
		return empty($this->table->$table) == true ? null : $this->table->$table;
	}
	
	function getApi($api) {
		$data = new stdClass();
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
		
		return $data;
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
	
	function setTemplet($templet) {
		$this->templet = $templet;
		return $this;
	}
	
	function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}
	
	function setLanguage($language) {
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

	function makeTemplet() {
		if ($this->templet == null) return '<div>'.$this->content.'</div>';
		
		$templet = file_get_contents($this->Module->getPath().'/templets/'.$this->templet.'/index.html');
		$templet = str_replace('{$iModuleDir}',$this->IM->getHost(true),$templet);
		
		$site = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$this->domain == null ? $this->IM->domain : $this->domain)->where('language',$this->language == null ? $this->IM->language : $this->language)->getOne();
		
		if ($site != null) {
			$site->logo = json_decode($site->logo);
			$site->logo = isset($site->logo->default) == true ? '<img src="'.$this->IM->getHost(true,$this->domain).'/attachment/view/'.$site->logo->default.'/logo.png" style="max-width:200px;" alt="'.$site->title.'">' : $site->title;
			$site->emblem = $site->emblem == 0 ? null : $this->IM->getHost(true,$this->domain).'/attachment/view/'.$site->emblem.'/emblem.png';
			$site->favicon = $site->favicon == 0 ? null : $this->IM->getHost(true,$this->domain).'/attachment/view/'.$site->favicon.'/favicon.ico';
			$site->image = $site->image == 0 ? null : $this->IM->getHost(true,$this->domain).'/attachment/view/'.$site->image.'/preview.jpg';
			$site->description = $site->description ? $site->description : null;
			
			$templet = str_replace('{$siteLogo}',$site->logo,$templet);
		}
		
		$templet = str_replace('{$subject}',$this->subject,$templet);
		$templet = str_replace('{$content}',$this->content,$templet);
		
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

	public function send($isEach=false) {
		REQUIRE_ONCE $this->Module->getPath().'/classes/phpmailer/class.phpmailer.php';
		
		if (empty($this->to) == true || $this->subject == null || $this->content == null) return false;
		if (empty($this->from) == true) $this->from = array('arzz@arzz.com','알쯔닷컴');
		
		$phpMailer = new PHPMailer();
		$phpMailer->pluginDir = $this->Module->getPath().'/classes/phpmailer';
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
					$this->db()->update($this->table->receiver,array('status'=>'FAIL','result'=>$result))->where('idx',$receiverIdx)->execute();
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
}
?>