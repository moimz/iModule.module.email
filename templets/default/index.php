<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 이메일 본문 템플릿을 정의한다.
 *
 * @file /modules/email/templets/default/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 1.0.0
 * @modified 2021. 6. 22.
 */
if (defined('__IM__') == false) exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<style type="text/css">
		/* GENERAL STYLE RESETS */
		body, #bodyTable {width:100% !important; height:100% !important; margin:0; padding:0;}
		#bodyTable {padding:20px 0 30px 0; background-color:#ffffff;}
		img, a img {border:0; outline:none; text-decoration:none;}
		.imageFix {display:block;}
		table, td {border-collapse:collapse;}
		
		/* CLIENT-SPECIFIC RESETS */
		/* Outlook.com(Hotmail)의 전체 너비 및 적절한 줄 높이를 허용 */
		.ReadMsgBody{width:100%;}
		.ExternalClass{width:100%;}
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
		/* Outlook 2007 이상에서 Outlook이 추가하는 테이블 주위의 간격을 제거 */
		table, td {mso-table-lspace:0pt; mso-table-rspace:0pt;}
		/* Internet Explorer에서 크기가 조정된 이미지를 렌더링하는 방식을 수정 */
		img {-ms-interpolation-mode:bicubic;}
		/* Webkit 및 Windows 기반 클라이언트가 텍스트 크기를 자동으로 조정하지 않도록 수정 */
		body, table, td, p, a, li, blockquote {-ms-text-size-adjust:100%; -webkit-text-size-adjust:100%;}
	</style>
</head>
<body style="width:100% !important; height:100% !important; margin:0; padding:0; background:#f4f4f4; font-family:'Apple SD Gothic Neo', 'malgun gothic', Helvetica, Georgia, Arial, sans-serif !important;">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100% !important; height:100% !important; margin:0; padding:0; background:#f4f4f4;">
		<tr>
			<td align="center">
				<table cellpadding="0" cellspacing="0" width="540" style="background:#fff;">
					<tr>
						<td style="background:<?php echo $Templet->getConfig('header_color'); ?>; padding:30px 0px; font-size:0; text-align:center;">
							<?php if ($Templet->getConfig('logo') == 'EMBLEM') { ?>
							<img src="<?php echo $IM->getSiteEmblem(true); ?>" width="50">
							<?php } else { ?>
							<img src="<?php echo $IM->getSiteLogo(strtolower($Templet->getConfig('logo')),true); ?>" height="50" />
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td style="padding:20px; line-height:1.6; font-size:14px;">
							<?php echo $content; ?>
						</td>
					</tr>
					<tr>
						<td style="background:#e5e5e5; font-size:12px; color:#666; padding:20px; line-height:1.6; word-break:break-all;">
							<?php echo nl2br($Templet->getConfig('footer')); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>