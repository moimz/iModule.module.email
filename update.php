<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈 업데이트를 처리한다.
 *
 * @file /modules/email/update.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2021. 6. 22.
 */
if (defined('__IM_INSTALLER__') == false) exit;

/**
 * 아이모듈 코어에 의하여 모듈에서 사용하는 데이터베이스 구조가 변경되기전에 실행된다.
 *
 * $previousVersion 현재 버전이 업데이트되기 직전에 설치된 버전
 * $currentVersion 현재 버전
 */
if (version_compare($previousVersion,'3.1.0','<') == true) {
	/**
	 * 직전 설치버전이 3.1.0 버전 이하일 때 데이터베이스 구조를 변경한다.
	 */
	$me->db()->startTransaction();
	
	// email_receiver_table 테이블의 백업본을 생성해둔다.
	$me->db()->backup('email_receiver_table');
	
	// email_receiver_table 테이블에 frommidx 컬럼을 idx 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'frommidx','type'=>'int','length'=>11,'default'=>0,'comment'=>'발송회원고유값'),'idx');
	
	// email_receiver_table 테이블에 tomidx 컬럼을 frommidx 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'tomidx','type'=>'int','length'=>11,'default'=>0,'comment'=>'수신회원고유값'),'frommidx');
	
	// email_receiver_table 테이블에 sender 컬럼을 tomidx 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'sender','type'=>'varchar','length'=>150,'is_null'=>true,'comment'=>'수신회원고유값'),'tomidx');
	
	// email_receiver_table 테이블에 to 컬럼을 receiver 로 변경한다.
	$me->db()->alter('email_receiver_table','CHANGE',array('name'=>'receiver','type'=>'varchar','length'=>150,'is_null'=>true,'comment'=>'수신회원고유값'),'to');
	
	// email_receiver_table 테이블에 subject 컬럼을 receiver 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'subject','type'=>'varchar','length'=>255,'is_null'=>true,'comment'=>'메일제목'),'receiver');
	
	// email_receiver_table 테이블에 content 컬럼을 subject 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'content','type'=>'longtext','is_null'=>true,'comment'=>'메일본문'),'subject');
	
	// email_receiver_table 테이블에 search 컬럼을 content 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'search','type'=>'text','is_null'=>true,'comment'=>'메일본문(검색)'),'content');
	
	// email_receiver_table 테이블에 is_push 컬럼을 search 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'is_push','type'=>'enum','length'=>"'TRUE','FALSE'",'default'=>'FALSE','comment'=>'알림메시지여부'),'search');
	
	// email_receiver_table 테이블에 reg_date 컬럼을 reg_date 로 변경한다.
	$me->db()->alter('email_receiver_table','CHANGE',array('name'=>'reg_date','type'=>'int','length'=>11,'comment'=>'발송일자'),'reg_date');
	
	// email_receiver_table 테이블에 readed 컬럼을 reg_date 뒤에 추가한다.
	$me->db()->alter('email_receiver_table','ADD',array('name'=>'readed','type'=>'int','length'=>11,'default'=>0,'comment'=>'확인일자'),'reg_date');
	
	// 테이블 구조를 변경하였다면, 기존 데이터를 마이그레이션한다.
	$items = $me->db()->select('email_receiver_table')->get();
	foreach ($items as $item) {
		$send = $me->db()->select($me->getTable('send'))->where('idx',$item->parent)->getOne();
		$me->db()->update('email_receiver_table',array('frommidx'=>0,'tomidx'=>0,'sender'=>$send->from,'subject'=>$send->subject,'content'=>$send->content,'search'=>$send->search,'is_push'=>$send->is_push))->where('idx',$item->idx)->execute();
	}
	
	// email_receiver_table 의 미사용 컬럼을 제거한다.
	$me->db()->alter('email_receiver_table','DROP','parent');
	$me->db()->alter('email_receiver_table','DROP','message');
	
	if ($me->db()->getLastError()) {
		$me->db()->rollback();
		return $me->db()->getLastError();
	}
	
	// 기존의 email_send_table 을 삭제하고, email_receiver_table 을 email_send_table 로 변경한다.
	$me->db()->drop('email_send_table');
	$me->db()->rename('email_receiver_table','email_send_table');
	$me->db()->commit();
}

/**
 * 업데이트가 성공적으로 되었다면 return true 를 반환한다.
 * true 가 반환되지 않을 경우, 아이모듈 코어에 의하여 모듈 업데이트가 중단되며, return 값이 출력된다.
 */
return true;
?>