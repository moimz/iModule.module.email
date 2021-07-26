<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 발송기록을 가져온다.
 * 
 * @file /modules/email/process/@getSends.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2021. 6. 22.
 */
if (defined('__IM__') == false) exit;

$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir');
$start_date = Request('start_date') ? strtotime(Request('start_date')) : 0;
$end_date = Request('end_date') ? strtotime(Request('end_date')) : time();

$mMember = $this->IM->getModule('member');
$keycode = Request('keycode');
$keyword = Request('keyword');
$type = Request('type') ? Request('type') : 'all';

$lists = $this->db()->select($this->table->send,'idx, frommidx, tomidx, sender, receiver, subject, reg_date, readed, status, is_push')->where('reg_date',$start_date,'>=')->where('reg_date',$end_date,'<');
if ($type != 'all') $lists->where('is_push',$type == 'push' ? 'TRUE' : 'FALSE');
if ($keyword) {
	if ($keycode == 'sender') $lists->where('sender','%'.$keyword.'%','LIKE');
	if ($keycode == 'receiver') $lists->where('receiver','%'.$keyword.'%','LIKE');
	if ($keycode == 'subject') $lists->where('subject','%'.$keyword.'%','LIKE');
	if ($keycode == 'content') $this->IM->getModule('keyword')->getWhere($lists,array('subject','search'),$keyword);
}

$total = $lists->copy()->count();
if ($limit > 0) $lists->limit($start,$limit);
if ($sort == 'reg_date') {
	$lists->orderBy('reg_date',$dir);
} elseif ($sort == 'readed') {
	$lists->orderBy('readed',$dir);
} else {
	$lists->orderBy($sort,$dir);
}
$lists = $lists->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$lists[$i]->sender = GetString($lists[$i]->sender,'replace');
	$lists[$i]->sender_photo = $this->IM->getModule('member')->getMemberPhotoUrl($lists[$i]->frommidx);
	$lists[$i]->receiver = GetString($lists[$i]->receiver,'replace');
	$lists[$i]->receiver_photo = $this->IM->getModule('member')->getMemberPhotoUrl($lists[$i]->tomidx);
	$lists[$i]->subject = GetString($lists[$i]->subject,'replace');
	$lists[$i]->is_push = $lists[$i]->is_push == 'TRUE';
}

$results->success = true;
$results->lists = $lists;
$results->total = $total;