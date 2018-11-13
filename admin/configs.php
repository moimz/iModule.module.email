<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈 설정을 위한 설정폼을 생성한다.
 * 
 * @file /modules/email/admin/configs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;
?>
<script>
var config = new Ext.form.Panel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:10,
	width:800,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
	items:[
		new Ext.form.FieldSet({
			title:Email.getText("admin/configs/form/default_setting"),
			items:[
				Admin.templetField(Email.getText("admin/configs/form/templet"),"templet","module","email",false),
				new Ext.form.FieldContainer({
					layout:"hbox",
					items:[
						new Ext.form.TextField({
							fieldLabel:Email.getText("admin/configs/form/default_email"),
							name:"default_email",
							flex:1
						}),
						new Ext.form.TextField({
							fieldLabel:Email.getText("admin/configs/form/default_name"),
							name:"default_name",
							flex:1
						})
					]
				})
			]
		}),
		new Ext.form.FieldSet({
			title:Email.getText("admin/configs/form/server_setting"),
			items:[
				new Ext.form.Checkbox({
					fieldLabel:Email.getText("admin/configs/form/use_sendmail"),
					name:"use_sendmail",
					boxLabel:Email.getText("admin/configs/form/use_sendmail_help"),
					uncheckedValue:"",
					checked:true,
					listeners:{
						change:function(form,checked) {
							form.getForm().findField("smtp_server").setDisabled(checked);
							form.getForm().findField("smtp_port").setDisabled(checked);
							form.getForm().findField("smtp_type").setDisabled(checked);
							form.getForm().findField("smtp_id").setDisabled(checked);
							form.getForm().findField("smtp_password").setDisabled(checked);
						}
					}
				}),
				new Ext.form.FieldContainer({
					fieldLabel:Email.getText("admin/configs/form/smtp_server"),
					layout:"hbox",
					items:[
						new Ext.form.TextField({
							name:"smtp_server",
							disabled:true,
							flex:1
						}),
						new Ext.form.NumberField({
							fieldLabel:Email.getText("admin/configs/form/smtp_port"),
							width:200,
							disabled:true,
							hideTrigger:true,
							name:"smtp_port"
						}),
						new Ext.form.ComboBox({
							fieldLabel:Email.getText("admin/configs/form/smtp_type"),
							name:"smtp_type",
							width:250,
							disabled:true,
							store:new Ext.data.ArrayStore({
								fields:["value"],
								data:[["TLS"],["SSL"]]
							}),
							displayField:"value",
							valueField:"value",
							value:"TLS"
						})
					],
					afterBodyEl:'<div class="x-form-help">'+Email.getText("admin/configs/form/smtp_server_help")+'</div>'
				}),
				new Ext.form.FieldContainer({
					layout:"hbox",
					items:[
						new Ext.form.TextField({
							fieldLabel:Email.getText("admin/configs/form/smtp_id"),
							name:"smtp_id",
							disabled:true,
							flex:1
						}),
						new Ext.form.TextField({
							fieldLabel:Email.getText("admin/configs/form/smtp_password"),
							name:"smtp_password",
							inputType:"password",
							disabled:true,
							flex:1
						})
					],
					afterBodyEl:'<div class="x-form-help" style="padding-left:105px;">'+Email.getText("admin/configs/form/smtp_id_help")+'</div>'
				})
			]
		})
	]
});
</script>