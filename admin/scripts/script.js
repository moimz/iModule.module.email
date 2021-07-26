/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈 관리자패널 UI 이벤트를 처리한다.
 * 
 * @file /modules/email/admin/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2021. 6. 22.
 */
var Email = {
	list:{
		view:function(data) {
			new Ext.Window({
				id:"ModuleEmailViewWindow",
				title:data.subject,
				modal:true,
				width:800,
				height:600,
				border:false,
				layout:"fit",
				items:[
					new Ext.Panel({
						border:false,
						html:'<iframe name="ModuleEmailViewFrame" style="width:100%; height:100%; border:0px;" frameborder="0" scrolling="1"></iframe>'
					})
				],
				listeners:{
					show:function() {
						ModuleEmailViewFrame.location.replace(ENV.getModuleUrl("email","view",data.idx,false));
					}
				}
			}).show();
		},
		delete:function() {
			var selected = Ext.getCmp("ModuleEmailList").getSelectionModel().getSelection();
			if (selected.length == 0) {
				Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("UNSELECTED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
			var idxes = [];
			for (var i=0, loop=selected.length;i<loop;i++) {
				idxes.push(selected[i].get("idx"));
			}
			
			Ext.Msg.show({title:Admin.getText("alert/info"),msg:Email.getText("admin/list/delete_confirm"),buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "ok") {
					Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
					$.send(ENV.getProcessUrl("email","@deleteSends"),{idxes:idxes.join(",")},function(result) {
						if (result.success == true) {
							Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								Ext.getCmp("ModuleEmailList").getStore().reload();
							}});
						}
					});
				}
			}});
		}
	}
};