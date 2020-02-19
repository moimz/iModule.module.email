<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 발송기록을 가져온다.
 * 
 * @file /modules/email/process/@getSends.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2020. 2. 19.
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
$is_push = Request('is_push');

$lists = $this->db()->select($this->table->send.' s','s.idx, s.from, s.subject, s.search, s.reg_date as send_date, r.to, r.status, r.reg_date as receive_date')->join($this->table->receiver.' r','r.parent=s.idx','LEFT')->where('s.reg_date',$start_date,'>=')->where('s.reg_date',$end_date,'<');
if ($is_push) $lists->where('s.is_push',$is_push);
if ($keyword) {
	if ($keycode == 'from') $lists->where('s.from','%'.$keyword.'%','LIKE');
	if ($keycode == 'to') $lists->where('r.to','%'.$keyword.'%','LIKE');
	if ($keycode == 'subject') $lists->where('s.subject','%'.$keyword.'%','LIKE');
	if ($keycode == 'message') $this->IM->getModule('keyword')->getWhere($lists,array('subject','search'),$keyword);
}

$total = $lists->copy()->count();
if ($limit > 0) $lists->limit($start,$limit);
if ($sort == 'send_date') {
	$lists->orderBy('s.reg_date',$dir);
} elseif ($sort == 'receive_date') {
	$lists->orderBy('s.receive_date',$dir);
} else {
	$lists->orderBy('s.'.$sort,$dir);
}
$lists = $lists->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$lists[$i]->to = GetString($lists[$i]->to,'replace');
	$lists[$i]->from = GetString($lists[$i]->from,'replace');
	$lists[$i]->subject = GetString($lists[$i]->subject,'replace');
}

$results->success = true;
$results->lists = $lists;
$results->total = $total;