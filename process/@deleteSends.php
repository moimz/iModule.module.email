<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 테스트메일을 전송한다.
 * 
 * @file /modules/email/process/@deleteSends.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2021. 6. 28.
 */
if (defined('__IM__') == false) exit;

$idxes = Request('idxes') ? explode(',',Request('idxes')) : array();
if (count($idxes) > 0) {
	$this->db()->delete($this->table->send)->where('idx',$idxes,'IN')->execute();
}

$results->success = true;
?>