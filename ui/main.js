//
// This app will handle the listing, additions and deletions of landingpages.  These are associated business.
//
function ciniki_landingpages_main() {
	// 
	// Theme and Layouts also listed in web/ui/main
	//
	this.themesAvailable = {
		'default':'Simple - Black/White',
		'black':'Midnight - Blue/Black',
		'davinci':'Davinci - Brown/Beige',
		'orangebrick':'Orange Brick - Brown/Beige',
		'orangebrick2':'Brick - Brown/Beige',
		'stone1':'Stone - Brown/Orange',
		'stone2':'Stone - Black/White',
		'splatter':'Purple Splatter - Purple/White',
//		'field':'Field - Green/White',
		};
	if( M.userPerms&0x01 == 0x01 ) {
		this.themesAvailable['field'] = 'Field - Green/White';
		this.themesAvailable['redbrick'] = 'Red Brick';
		this.themesAvailable['private'] = 'Private';
//		this.themesAvailable['orangebrick'] = 'Orange Brick';
//		this.themesAvailable['splatter'] = 'Splatter';
	}
	this.layoutsAvailable = {
		'default':'Default',
		'private':'Private',
		};
	//
	// Panels
	//
	this.init = function() {
		//
		// landingpages panel
		//
		this.menu = new M.panel('Landing Pages',
			'ciniki_landingpages_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.landingpages.main.menu');
        this.menu.sections = {
			'_10':{'label':'Active pages', 'type':'simplegrid', 'num_cols':1,
				'noData':'No active pages',
				'addTxt':'Add Page',
				'addFn':'M.ciniki_landingpages_main.pageEdit(\'M.ciniki_landingpages_main.menuShow();\',0);',
				},
			'_0':{'label':'In Development', 'type':'simplegrid', 'num_cols':1,
				'visible':function() {return (M.ciniki_landingpages_main.menu.data.status[0]!=null?'yes':'no');}, 
//                'visible':'yes',
				'noData':'No pages',
				'addTxt':'Add Page',
				'addFn':'M.ciniki_landingpages_main.pageEdit(\'M.ciniki_landingpages_main.menuShow();\',0);',
				},
//			'_40':{'label':'Redirected Pages', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
//				'visible':function() {return (M.ciniki_landingpages_main.menu.data['0']!=null?'yes':'no');}, 
//				'noData':'No redirected pages',
//				},
			'_50':{'label':'Removed Pages', 'type':'simplegrid', 'num_cols':1,
				'visible':function() {return (M.ciniki_landingpages_main.menu.data['50']!=null?'yes':'no');}, 
				'noData':'No removed pages',
				},
			};
		this.menu.sectionData = function(s) { 
            var status = parseInt(s.replace(/_/,''));
            if( this.data.status[status] != null ) {
                return this.data.status[status].pages; 
            }
            return null;
		}
		this.menu.noData = function(s) { return this.sections[s].noData; }
		this.menu.cellValue = function(s, i, j, d) {
			return d.name;
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.ciniki_landingpages_main.pageEdit(\'M.ciniki_landingpages_main.menuShow();\',\'' + d.id + '\');';
		};
		this.menu.addButton('add', 'Add', 'M.ciniki_landingpages_main.pageEdit(\'M.ciniki_landingpages_main.menuShow();\',0);');
		this.menu.addClose('Back');

		//
		// The page panel 
		//
		this.page = new M.panel('Landing Page',
			'ciniki_landingpages_main', 'page',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.landingpages.main.page');
		this.page.page_id = 0;
		this.page.data = {};
		this.page.sections = {
			'info':{'label':'', 'aside':'yes', 'fields':{
				'name':{'label':'Name', 'type':'text'},
				'permalink':{'label':'Permalink', 'type':'text'},
				'title':{'label':'Title', 'type':'text'},
				'short_title':{'label':'Short Title', 'type':'text'},
				'status':{'label':'Status', 'type':'toggle', 'toggles':{'0':'In Development', '10':'Active', '50':'Removed'}},
//				'flags':{'label':'Options', 'type':'flags', 'toggles':{'1':{'name':''},}},
//				'redirect_url':{'label':'Redirect URL', 'visible':'no'},
				}},
			'theme':{'label':'Theme', 'aside':'yes', 'fields':{
				'page-theme':{'label':'Theme', 'type':'select', 'options':this.themesAvailable},
				'page-layout':{'label':'Layout', 'type':'select', 'options':this.layoutsAvailable},
				'page-privatetheme-id':{'label':'Private Theme', 'type':'select', 'options':{}},
				}},
			'header':{'label':'Header', 'fields':{
				'header-social-display':{'label':'Display Social Icons', 'type':'toggle', 'toggles':{'no':'No', 'yes':'Yes'}},
				'header-image-display':{'label':'Display Image', 'type':'toggle', 'toggles':{'no':'No', 'yes':'Yes'}},
				'header-menu-display':{'label':'Display Menu', 'type':'toggle', 'toggles':{'no':'No', 'yes':'Yes'}},
				}},
			'form':{'label':'Form', 'fields':{
				'page-form':{'label':'Form', 'type':'select', 'options':{'0':'None'}},
				'page-form-above':{'label':'Form Above Content', 'type':'toggle', 'toggles':{'no':'No', 'yes':'Yes'}},
				'page-form-below':{'label':'Form Below Content', 'type':'toggle', 'toggles':{'no':'No', 'yes':'Yes'}},
				}},
			'items':{'label':'Content', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Content',
				'addFn':'M.ciniki_landingpages_main.contentEdit(\'M.ciniki_landingpages_main.pageEdit();\',0);',
				},
			'_buttons':{'label':'Buttons', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_landingpages_main.pageSave();'},
				}},
		};
        this.page.sectionData = function(s) { return this.data[s]; }
		this.page.fieldValue = function(s, i, d) {
			return this.data[i];
		};
		this.page.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.landingpages.pageHistory', 'args':{'business_id':M.curBusinessID, 
				'page_id':this.page_id, 'field':i}};
		}
		this.page.cellValue = function(s, i, j, d) {
			return d.title;
		};
		this.page.rowFn = function(s, i, d) {
			return 'M.ciniki_landingpages_main.contentEdit(\'M.ciniki_landingpages_main.pageEdit();\',\'' + d.id + '\');';
		};
		this.page.addClose('Back');

		//
		// The panel for a site's menu
		//
		this.content = new M.panel('Content',
			'ciniki_landingpages_main', 'content',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.landingpages.main.content');
		this.content.data = null;
		this.content.item_id = 0;
		this.content.content_id = 0;
        this.content.sections = { 
			'_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'_captions':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_caption':{'label':'Caption', 'type':'text'},
				}},
			'_info':{'label':'', 'aside':'yes', 'fields':{
				'menu_title':{'label':'Menu Title', 'type':'text'},
				'sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'title':{'label':'Title', 'type':'text'},
				'content_type':{'label':'Type', 'type':'toggle', 'toggles':{'10':'Custom', '11':'Manual'}},
				}},
			'_content':{'label':'Content', 'fields':{
				'content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'xlarge'},
				}},
            };  
		this.content.fieldValue = function(s, i, d) { return this.data[i]; }
		this.content.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.landingpages.pageContentHistory', 'args':{'business_id':M.curBusinessID, 
				'content_id':this.content_id, 'field':i}};
		}
		this.content.addDropImage = function(iid) {
			M.ciniki_landingpages_main.content.setFieldValue('primary_image_id', iid, null, null);
			return true;
		};
		this.content.deleteImage = function(fid) {
			this.setFieldValue(fid, 0, null, null);
			return true;
		};
		this.content.addButton('save', 'Save', 'M.ciniki_landingpages_main.contentSave();');
		this.content.addClose('Cancel');
	}

	//
	// Arguments:
	// aG - The arguments to be parsed into args
	//
	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_landingpages_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.menuShow(cb);
	}

	this.menuShow = function(cb) {
		this.menu.data = {};
		M.api.getJSONCb('ciniki.landingpages.pageList', {'business_id':M.curBusinessID, 'org':'status'}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_landingpages_main.menu;
			p.data = rsp;
			p.refresh();
			p.show(cb);
		});
	};

	this.pageEdit = function(cb, pid) {
		if( pid != null ) { this.page.page_id = pid; }
		M.api.getJSONCb('ciniki.landingpages.pageGet', {'business_id':M.curBusinessID, 'page_id':this.page.page_id}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_landingpages_main.page;
			p.data = rsp.page;
            if( rsp.privatethemes != null ) {
                p.sections.theme.fields['page-privatetheme-id'].options = {'0':'None'};
                p.sections.theme.fields['page-privatetheme-id'].active = 'yes';
                for(var i in rsp.privatethemes) {
                    p.sections.theme.fields['page-privatetheme-id'].options[i] = rsp.privatethemes[i].name;
                }
            } else {
                p.sections.theme.fields['page-privatetheme-id'].options = {};
                p.sections.theme.fields['page-privatetheme-id'].active = 'no';
            }
			p.refresh();
			p.show(cb);
		});
	};

	this.pageSave = function() {
		if( this.page.page_id > 0 ) {
			var c = this.page.serializeForm('no');
			if( c != '' ) {
                console.log(c);
				M.api.postJSONCb('ciniki.landingpages.pageUpdate', {'business_id':M.curBusinessID, 'page_id':this.page.page_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_landingpages_main.page.close();
					});
			} else {
				this.page.close();
			}
		} else {
			var name = this.page.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the page first');
				return false;
			}
			var permalink = this.page.formValue('permalink');
			if( name == '' ) {
				alert('You must enter the permalink of the page first');
				return false;
			}
			var title = this.page.formValue('title');
			if( title == '' ) {
				alert('You must enter the title of the page first');
				return false;
			}
			var c = this.page.serializeForm('no');
			M.api.postJSONCb('ciniki.landingpages.pageAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_landingpages_main.page.close();
			});
		}
	};

	this.contentEdit = function(cb, cid) {
		// Create page if not already created
		if( this.page.page_id == 0 ) {
			var name = this.page.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the page first');
				return false;
			}
			var permalink = this.page.formValue('permalink');
			if( name == '' ) {
				alert('You must enter the permalink of the page first');
				return false;
			}
			var title = this.page.formValue('title');
			if( title == '' ) {
				alert('You must enter the title of the page first');
				return false;
			}
			var c = this.page.serializeForm('yes');
			M.api.postJSONCb('ciniki.landingpages.pageAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_landingpages_main.page.page_id = rsp.id;
				M.ciniki_landingpages_main.contentEdit(cb, cid);
			});
		} else {
			if( cid != null ) { this.content.content_id = cid; }
			M.api.getJSONCb('ciniki.landingpages.pageContentGet', {'business_id':M.curBusinessID, 
				'page_id':this.page.page_id, 'content_id':this.content.content_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_landingpages_main.content;
					p.data = rsp.content;
					p.refresh();
					p.show(cb);
				});
		}
	};

	this.contentSave = function() {
		if( this.content.content_id > 0 ) {
			var c = this.content.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.landingpages.pageContentUpdate', {'business_id':M.curBusinessID, 
					'page_id':this.page.page_id, 'content_id':this.content.content_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_landingpages_main.content.close();
					});
			} else {
				this.content.close();
			}
		} else {
			var c = this.content.serializeForm('yes');
			M.api.postJSONCb('ciniki.landingpages.pageContentAdd', {'business_id':M.curBusinessID, 'page_id':this.page.page_id}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_landingpages_main.content.close();
			});
		}
	};




	this.contentDelete = function() {
		if( confirm("Are you sure you want to remove '" + this.event.data.name + "' as an event ?") ) {
			var rsp = M.api.getJSONCb('ciniki.landingpages.eventDelete', 
				{'business_id':M.curBusinessID, 'event_id':M.ciniki_landingpages_main.event.event_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_landingpages_main.event.close();
				});
		}
	}
};
