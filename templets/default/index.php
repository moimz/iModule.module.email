<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 이메일 본문 템플릿을 정의한다.
 *
 * @file /modules/email/templets/default/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2017. 11. 22.
 */
if (defined('__IM__') == false) exit;
?>
<table cellpadding="0" cellspacing="0" style="width:600px; margin:0 auto; font-family:sans-serif;">
<tr>
	<td style="padding:10px 0px; font-size:16px; text-align:center;">
		<img src="<?php echo $IM->getSiteEmblem(true); ?>" style="max-width:60px;">
	</td>
</tr>
<tr>
	<td style="background:#f4f4f4; border-top:2px solid #222; padding:15px; line-height:1.6; font-size:14px;">
		<?php echo $content; ?>
	</td>
</tr>
<tr>
	<td style="font-size:12px; color:#666; padding:10px 0px; text-align:center; line-height:1.6; word-break:break-all;">
		<?php echo nl2br($Templet->getConfig('footer')); ?>
	</td>
</tr>
</table>