<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈 관리자패널을 구성한다.
 * 
 * @file /modules/email/admin/index.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2021. 6. 22.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.TabPanel({
		id:"ModuleEmail",
		border:false,
		tabPosition:"bottom",
		items:[
			new Ext.grid.Panel({
				id:"ModuleEmailSendList",
				iconCls:"fa fa-bars",
				title:Email.getText("admin/list/title"),
				border:false,
				tbar:[
					new Ext.Button({
						text:Email.getText("admin/list/all_period"),
						iconCls:"fa fa fa-check-square-o",
						pressed:true,
						enableToggle:true,
						handler:function(button) {
							if (button.pressed === true) {
								button.setIconCls("fa fa fa-check-square-o");
								Ext.getCmp("ModuleEmailSendListStartDate").disable();
								Ext.getCmp("ModuleEmailSendListEndDate").disable();
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("start_date","");
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("end_date","");
								Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
							} else {
								button.setIconCls("fa fa fa-square-o");
								Ext.getCmp("ModuleEmailSendListStartDate").enable();
								Ext.getCmp("ModuleEmailSendListEndDate").enable();
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("start_date",moment(Ext.getCmp("ModuleEmailSendListStartDate").getValue()).format("YYYY-MM-DD"));
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("end_date",moment(Ext.getCmp("ModuleEmailSendListEndDate").getValue()).format("YYYY-MM-DD"));
								Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
							}
						}
					}),
					new Ext.form.DateField({
						id:"ModuleEmailSendListStartDate",
						width:120,
						value:moment().format("YYYY-MM-01"),
						format:"Y-m-d",
						disabled:true,
						listeners:{
							change:function(form,value) {
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("start_date",moment(value).format("YYYY-MM-DD"));
								Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
							}
						}
					}),
					new Ext.form.DisplayField({
						value:"~"
					}),
					new Ext.form.DateField({
						id:"ModuleEmailSendListEndDate",
						width:120,
						value:moment().add(1,"month").date(0).format("YYYY-MM-DD"),
						format:"Y-m-d",
						disabled:true,
						listeners:{
							change:function(form,value) {
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("end_date",moment(value).format("YYYY-MM-DD"));
								Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
							}
						}
					}),
					"-",
					new Ext.form.ComboBox({
						id:"ModuleEmailSendListKeycode",
						store:new Ext.data.ArrayStore({
							fields:["display","value"],
							data:(function() {
								var datas = [];
								for (var field in Email.getText("admin/list/keycodes")) {
									datas.push([Email.getText("admin/list/keycodes/"+field),field]);
								}
								return datas;
							})()
						}),
						width:100,
						editable:false,
						displayField:"display",
						valueField:"value",
						value:"subject"
					}),
					Admin.searchField("ModuleEmailSendListKeyword",180,Email.getText("admin/list/keyword"),function(keyword) {
						Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("keycode",Ext.getCmp("ModuleEmailSendListKeycode").getValue());
						Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("ModuleEmailSendListKeyword").getValue());
						Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
					}),
					"-",
					new Ext.Button({
						text:Email.getText("admin/list/write"),
						iconCls:"mi mi-plus",
						handler:function() {
							Email.write();
						}
					}),
					new Ext.Button({
						text:Email.getText("admin/list/delete"),
						iconCls:"mi mi-trash",
						handler:function() {
							Email.list.delete();
						}
					}),
					"->",
					new Ext.button.Segmented({
						allowMultiple:false,
						items:[
							new Ext.Button({
								text:"전체",
								is_push:"",
								pressed:true,
								iconCls:"fa fa-check-square-o"
							}),
							new Ext.Button({
								text:"알림발송",
								is_push:"TRUE",
								iconCls:"fa fa-square-o"
							}),
							new Ext.Button({
								text:"직접발송",
								is_push:"FALSE",
								iconCls:"fa fa-square-o"
							})
						],
						listeners:{
							toggle:function(segmented,button,pressed) {
								for (var i=0, loop=segmented.items.items.length;i<loop;i++) {
									segmented.items.items[i].setIconCls("fa fa-square-o");
								}
								
								Ext.getCmp("ModuleEmailSendList").getStore().getProxy().setExtraParam("is_push",button.is_push);
								Ext.getCmp("ModuleEmailSendList").getStore().loadPage(1);
								button.setIconCls("fa fa-check-square-o");
							}
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("email","@getSends"),
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[{property:"reg_date",direction:"DESC"}],
					autoLoad:true,
					pageSize:50,
					fields:["idx","sender","sender_photo","receiver","receiver_photo","subject","reg_date","status"],
					listeners:{
						load:function(store,records,success,e) {
							if (success == false) {
								if (e.getError()) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						}
					}
				}),
				columns:[{
					text:Email.getText("admin/list/columns/sender"),
					width:200,
					sortable:true,
					dataIndex:"sender",
					renderer:function(value,p,record) {
						return '<i style="width:24px; height:24px; float:left; display:block; background:url('+record.data.sender_photo+'); background-size:cover; background-repeat:no-repeat; border:1px solid #ccc; border-radius:50%; margin:-3px 5px -3px -5px;"></i>' + value;
					}
				},{
					text:Email.getText("admin/list/columns/receiver"),
					width:200,
					sortable:true,
					dataIndex:"receiver",
					renderer:function(value,p,record) {
						return '<i style="width:24px; height:24px; float:left; display:block; background:url('+record.data.receiver_photo+'); background-size:cover; background-repeat:no-repeat; border:1px solid #ccc; border-radius:50%; margin:-3px 5px -3px -5px;"></i>' + value;
					}
				},{
					text:Email.getText("admin/list/columns/subject"),
					minWidth:200,
					flex:1,
					sortable:true,
					dataIndex:"subject",
					renderer:function(value,p,record) {
						var sHTML = "";
						if (record.data.is_push == "TRUE") sHTML+= '<i class="icon fa fa-bell-o"></i>';
						sHTML+= value;
						return sHTML;
					}
				},{
					text:Email.getText("admin/list/columns/reg_date"),
					width:145,
					align:"center",
					dataIndex:"reg_date",
					sortable:true,
					renderer:function(value) {
						return value > 0 ? moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm") : "";
					}
				},{
					text:Email.getText("admin/list/columns/readed"),
					width:145,
					align:"center",
					dataIndex:"readed",
					sortable:true,
					renderer:function(value) {
						return value > 0 ? moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm") : "";
					}
				},{
					text:Email.getText("admin/list/columns/status"),
					width:80,
					align:"center",
					dataIndex:"status",
					sortable:true,
					renderer:function(value,p) {
						if (value == "FAIL") p.style = "color:red;";
						else p.style = "color:blue;";
						
						return Email.getText("status/"+value);
					}
				}],
				selModel:new Ext.selection.CheckboxModel(),
				bbar:new Ext.PagingToolbar({
					store:null,
					displayInfo:false,
					items:[
						"->",
						{xtype:"tbtext",text:Admin.getText("text/grid_help")}
					],
					listeners:{
						beforerender:function(tool) {
							tool.bindStore(Ext.getCmp("ModuleEmailSendList").getStore());
						}
					}
				}),
				listeners:{
					itemdblclick:function(grid,record) {
						Email.list.view(record.data);
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(record.data.receiver_name+"("+record.data.receiver+")");
						
						menu.add({
							iconCls:"xi xi-form",
							text:"전송기록보기",
							handler:function() {
								Email.list.view(record.data);
							}
						});
						
						menu.add({
							iconCls:"mi mi-trash",
							text:"전송기록삭제",
							handler:function() {
								Email.list.delete();
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			})
		]
	})
); });
</script>