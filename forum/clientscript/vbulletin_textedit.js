/*!======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

custom_editor_events = {
	'editor_switch' : new YAHOO.util.CustomEvent('editor_switch'),
	'editor_resize' : new YAHOO.util.CustomEvent('editor_resize')
};

// #############################################################################
// vB_Text_Editor

/**
* vBulletin Editor Class
*
* Activates any HTML controls for an editor
*
* @package	vBulletin
* @version	$Revision: 35418 $
* @date		$Date: 2010-02-14 13:12:24 -0600 (Sun, 14 Feb 2010) $
* @author	Kier Darby, Scott MacVicar, Mike Sullivan, Freddie Bingham vBulletin Development Team
*
* @param	string	Unique key for this editor
* @param	boolean	Initialise to WYSIWYG mode?
* @param	string	Forumid / Calendar etc.
* @param	boolean	Parse smilies?
* @param	string	(Optional) Initial text for the editor
* @param	string	(Optional) Extra arguments to pass when switching editor modes
*/
function vB_Text_Editor(editorid, mode, parsetype, parsesmilies, initial_text, ajax_extra)
{
	this._construct(editorid, mode, parsetype, parsesmilies, initial_text, ajax_extra);
	this.init(initial_text);
}

/**
* Primarily the constructor, setting up the object for use.
* Abstracted out of the real constructor as that initializes the editor as well.
*
* @param	string	Unique key for this editor
* @param	boolean	Initialise to WYSIWYG mode?
* @param	string	Forumid / Calendar etc.
* @param	boolean	Parse smilies?
* @param	string	(Optional) Initial text for the editor
* @param	string	(Optional) Extra arguments to pass when switching editor modes
*/
vB_Text_Editor.prototype._construct = function(editorid, mode, parsetype, parsesmilies, initial_text, ajax_extra)
{
	/**
	* Miscellaneous variables
	*
	* @var	string	Unique Editor ID
	* @var	boolean	WYSIWYG mode
	* @var	boolean	Have we initialized the editor?
	* @var	mixed	Passed parsetype (corresponds to bbcodeparse forumid)
	* @var	boolean	Passed parsesmilies option
	* @var	boolean	Can we use vBmenu popups?
	* @var	object	The element containing controls
	* @var	object	The textarea object containing the initial text
	* @var	array	Array containing all button objects
	* @var	array	Array containing all popup objects
	* @var	object	Current prompt() emulation popup
	* @var	string	State of the font context control
	* @var	string	State of the size context control
	* @var	string	State of the color context control
	* @var	string	String to contain the fake 'clipboard'
	* @var	boolean	Is the editor 'disabled'? (quick reply use)
	* @var	vB_History	History manager for undo/redo systems
	* @var  integer Is the editor mode trying to be changed?
	* @var	boolean	Are we allowed to use basic (B/I/U) BB code? - Set up with allowbasicbbcode in editor_clientscript template
	*/
	this.editorid = editorid;
	this.wysiwyg_mode = parseInt(mode, 10) ? 1 : 0;
	this.initialized = false;
	this.parsetype = (typeof parsetype == 'undefined' ? 'nonforum' : parsetype);
	this.ajax_extra = (typeof ajax_extra == 'undefined' ? '' : ajax_extra);
	this.parsesmilies = (typeof parsesmilies == 'undefined' ? 1 : parsesmilies);
	this.popupmode = (true);
	this.controlbar = fetch_object(this.editorid + '_controls');
	this.textobj = fetch_object(this.editorid + '_textarea');
	this.buttons = new Array();
	this.popups = new Array();
	this.prompt_popup = null;
	this.fontstate = null;
	this.sizestate = null;
	this.colorstate = null;
	this.clipboard = '';
	this.disabled = false;
	this.history = new vB_History();
	this.influx = 0;
	this.allowbasicbbcode = ((typeof allowbasicbbcode != "undefined" && allowbasicbbcode) ? true : false);
	this.ltr = ((typeof ltr != "undefined" && ltr == "right") ? 'right' : 'left');
	this.activeimg = null;
}

/**
* Add Range (Mozilla)
*/
vB_Text_Editor.prototype.add_range = function(node)
{
	this.check_focus();
	var sel = this.editwin.getSelection();
	var range = this.editdoc.createRange();
	range.selectNodeContents(node);
	sel.removeAllRanges();
	sel.addRange(range);
};

/**
* Apply formatting
*/
vB_Text_Editor.prototype.apply_format = function(cmd, dialog, argument)
{
	if (this.wysiwyg_mode)
	{
		if (is_moz)
		{
			this.editdoc.execCommand('useCSS', false, true);
		}

		this.editdoc.execCommand(cmd, (typeof dialog == 'undefined' ? false : dialog), (typeof argument == 'undefined' ? true : argument));

		return false;
	}
	else
	{
		// undo & redo array pops
		switch (cmd)
		{
			case 'bold':
			case 'italic':
			case 'underline':
			{
				this.wrap_tags(cmd.substr(0, 1), false);
				return;
			}

			case 'justifyleft':
			case 'justifycenter':
			case 'justifyright':
			{
				this.wrap_tags(cmd.substr(7), false);
				return;
			}

			case 'indent':
			{
				this.wrap_tags(cmd, false);
				return;
			}

			case 'fontname':
			{
				this.wrap_tags('font', argument);
				return;
			}

			case 'fontsize':
			{
				this.wrap_tags('size', argument);
				return;
			}

			case 'forecolor':
			{
				this.wrap_tags('color', argument);
				return;
			}

			case 'createlink':
			{
				var sel = this.get_selection();
				if (sel)
				{
					this.wrap_tags('url', argument);
				}
				else
				{
					this.wrap_tags('url', argument, argument);
				}
				return;
			}

			case 'insertimage':
			{
				this.wrap_tags('img', false, argument);
				return;
			}

			case 'removeformat':
			return;
		}

		//alert('cmd :: ' + cmd + '\ndialog :: ' + dialog + '\nargument :: ' + argument);
	}
};

/**
* Build Attachments Popup
*
* @param	object	The menu container object
*/
vB_Text_Editor.prototype.build_attachments_popup = function(obj)
{
	var id, div, attach_count = 0;

	var listobj = YAHOO.util.Dom.get(vB_Attachments.listobjid);
	if (listobj)
	{
		var li = listobj.getElementsByTagName("li");
		var sibling = li[1].nextSibling;
		while (sibling)
		{
			sibling.editorid = this.editorid;
			if (!YAHOO.util.Event.getListeners(sibling, "mouseover"))
			{
				YAHOO.util.Event.on(sibling, "mouseover", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(sibling, "mouseout", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(sibling, "mouseup", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(sibling, "mousedown", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(sibling, "click", vB_Text_Editor_Events.prototype.attachoption_onclick);
			}
			sibling = sibling.nextSibling;
			attach_count++;
		}
	}
	else
	{
		return;
	}

	if (attach_count > 1)
	{
		div = document.createElement('div');
		div.editorid = this.editorid
		div.controlkey = obj.id;
		div.className = 'osmilie';
		div.style.fontWeight = 'bold';
		div.style.paddingLeft = '25px';
		div.style.whiteSpace = 'nowrap';
		div.innerHTML = vbphrase['insert_all'];
		div.onmouseover = div.onmouseout = div.onmousedown = div.onmouseup = vB_Text_Editor_Events.prototype.menuoption_onmouseevent;
		div.onclick = vB_Text_Editor_Events.prototype.attachinsertall_onclick;
	}

}

/**
* Build Font Name Popup Contents
*
* @param	object	The control object for the menu
*/
vB_Text_Editor.prototype.build_fontname_popup = function(obj)
{
	if (YAHOO.util.Dom.get(this.editorid + "_fontfield"))
	{
		this.fontoptions = {'' : YAHOO.util.Dom.get(this.editorid + "_fontfield").innerHTML};
	}

	if (!YAHOO.util.Event.getListeners(obj, "mouseover"))
	{
		YAHOO.util.Event.on(obj, "mouseover", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseout", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseup", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mousedown", vB_Text_Editor.prototype.menu_context, obj, this);
		var fonts = YAHOO.util.Dom.getElementsByClassName('fontname', '', obj);
		for (i = 0; i < fonts.length; i++)
		{
			fonts[i].cmd = obj.cmd;
			fonts[i].controlkey = obj.id;
			fonts[i].editorid = this.editorid;
			YAHOO.util.Event.on(fonts[i], "mouseover", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mouseout", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mouseup", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mousedown", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "click", vB_Text_Editor_Events.prototype.formatting_option_onclick_font);
			var fontoption = fonts[i].firstChild.innerHTML;
			this.fontoptions[fontoption] = fontoption;
		}
	}
}

/**
* Build Font Size Popup Contents
*
* @param	object	The control object for the menu
*/
vB_Text_Editor.prototype.build_fontsize_popup = function(obj)
{
	if (YAHOO.util.Dom.get(this.editorid + "_sizefield"))
	{
		this.sizeoptions = {"" : YAHOO.util.Dom.get(this.editorid + "_sizefield").innerHTML};
	}

	if (!YAHOO.util.Event.getListeners(obj, "mouseover"))
	{
		YAHOO.util.Event.on(obj, "mouseover", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseout", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseup", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mousedown", vB_Text_Editor.prototype.menu_context, obj, this);
		var fonts = YAHOO.util.Dom.getElementsByClassName('fontsize', '', obj);
		for (i = 0; i < fonts.length; i++)
		{
			fonts[i].cmd = obj.cmd;
			fonts[i].controlkey = obj.id;
			fonts[i].editorid = this.editorid;
			YAHOO.util.Event.on(fonts[i], "mouseover", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mouseout", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mouseup", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "mousedown", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
			YAHOO.util.Event.on(fonts[i], "click", vB_Text_Editor_Events.prototype.formatting_option_onclick_size);
			var sizeoption = fonts[i].firstChild.firstChild.innerHTML;
			this.sizeoptions[sizeoption] = sizeoption;
		}
	}
}

/**
* Build ForeColor Popup Contents
*
* @param	object	The control object for the menu
*/
vB_Text_Editor.prototype.build_forecolor_popup = function(obj)
{
	if (!YAHOO.util.Event.getListeners(obj, "mouseover"))
	{
		YAHOO.util.Event.on(obj, "mouseover", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseout", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseup", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mousedown", vB_Text_Editor.prototype.menu_context, obj, this);
		//YAHOO.util.Event.on(obj, "click", vB_Text_Editor_Events.prototype.colorout_onclick);
		var colors = YAHOO.util.Dom.getElementsByClassName('colorbutton', '', obj);
		if (colors.length)
		{
			for (var x = 0; x < colors.length; x++)
			{
				colors[x].cmd = obj.cmd;
				colors[x].editorid = this.editorid;
				colors[x].controlkey = obj.id;
				colors[x].colorname = YAHOO.util.Dom.getStyle(colors[x].firstChild, "background-color");
				colors[x].id = this.editorid + '_color_' + this.translate_color_commandvalue(colors[x].colorname);
				YAHOO.util.Event.on(colors[x], "mouseover", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(colors[x], "mouseout", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(colors[x], "mouseup", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(colors[x], "mousedown", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
				YAHOO.util.Event.on(colors[x], "click", vB_Text_Editor_Events.prototype.coloroption_onclick);
			}
		}
	}
}

/**
* Build Smilie Popup Contents
*
* @param	object	The control object for the menu
*/
vB_Text_Editor.prototype.build_smilie_popup = function(obj)
{
	if (!YAHOO.util.Event.getListeners(obj, "mouseover"))
	{
		YAHOO.util.Event.on(obj, "mouseover", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseout", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mouseup", vB_Text_Editor.prototype.menu_context, obj, this);
		YAHOO.util.Event.on(obj, "mousedown", vB_Text_Editor.prototype.menu_context, obj, this);
		var smilies = YAHOO.util.Dom.getElementsByClassName('smilie', '', obj);
		if (smilies.length)
		{
			for (var x = 0; x < smilies.length; x++)
			{
					var moresmilies = YAHOO.util.Dom.get("moresmilies");
					if (moresmilies)
					{
						YAHOO.util.Dom.setStyle(moresmilies, "cursor", pointer_cursor);
						moresmilies.editorid = this.editorid;
						moresmilies.controlkey = obj.id;
						YAHOO.util.Event.on(moresmilies, "click", vB_Text_Editor_Events.prototype.smiliemore_onclick);
					}

					smilies[x].editorid = this.editorid;
					smilies[x].controlkey = obj.id;
					smilies[x].smilietext = smilies[x].firstChild.firstChild.alt;
					var match = smilies[x].id.match(/^smilie_dropdown_([0-9]+)$/);
					smilies[x].smilieid = match[1];

					YAHOO.util.Event.on(smilies[x], "mouseover", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
					YAHOO.util.Event.on(smilies[x], "mouseout", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
					YAHOO.util.Event.on(smilies[x], "mouseup", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
					YAHOO.util.Event.on(smilies[x], "mousedown", vB_Text_Editor_Events.prototype.menuoption_onmouseevent);
					YAHOO.util.Event.on(smilies[x], "click", vB_Text_Editor_Events.prototype.smilieoption_onclick);
			}
		}
	}
}

/**
* Replace the popup controls with <select> menus for rubbish browsers
*
* @param	object	The popup control element
*/
vB_Text_Editor.prototype.build_select = function(obj)
{
	var sel = document.createElement('select');
	sel.id = this.editorid + '_select_' + obj.cmd;
	sel.editorid = this.editorid;
	sel.cmd = obj.cmd;

	var opt = document.createElement('option');
	opt.value = '';
	opt.text = obj.title;
	sel.add(opt, is_ie ? sel.options.length : null);

	opt = document.createElement('option');
	opt.value = '';
	opt.text = ' ';
	sel.add(opt, is_ie ? sel.options.length : null);

	var i;

	switch (obj.cmd)
	{
		case 'fontname':
		{
			for (i = 0; i < fontoptions.length; i++)
			{
				opt = document.createElement('option');
				opt.value = fontoptions[i];
				opt.text = (fontoptions[i].length > 10 ? (fontoptions[i].substr(0, 10) + '...') : fontoptions[i]);
				sel.add(opt, is_ie ? sel.options.length : null);
			}

			sel.onchange = vB_Text_Editor_Events.prototype.formatting_select_onchange;
			break;
		}

		case 'fontsize':
		{
			for (i = 0; i < sizeoptions.length; i++)
			{
				opt = document.createElement('option');
				opt.value = sizeoptions[i];
				opt.text = sizeoptions[i];
				sel.add(opt, is_ie ? sel.options.length : null);
			}

			sel.onchange = vB_Text_Editor_Events.prototype.formatting_select_onchange;
			break;
		}

		case 'forecolor':
		{
			for (i in coloroptions)
			{
				if (YAHOO.lang.hasOwnProperty(coloroptions, i))
				{
					opt = document.createElement('option');
					opt.value = coloroptions[i];
					opt.text = PHP.trim((coloroptions[i].length > 5 ? (coloroptions[i].substr(0, 5) + '...') : coloroptions[i]).replace(new RegExp('([A-Z])', 'g'), ' $1'));
					opt.style.backgroundColor = i;
					sel.add(opt, is_ie ? sel.options.length : null);
				}
			}

			sel.onchange = vB_Text_Editor_Events.prototype.formatting_select_onchange;
			break;
		}

		case 'smilie':
		{
			for (var cat in smilieoptions)
			{
				if (!YAHOO.lang.hasOwnProperty(smilieoptions, cat))
				{
					continue;
				}

				for (var smilieid in smilieoptions[cat])
				{
					if (!YAHOO.lang.hasOwnProperty(smilieoptions[cat], smilieid))
					{
						continue;
					}

					if (smilieid != 'more')
					{
						opt = document.createElement('option');
						opt.value = smilieoptions[cat][smilieid][1];
						opt.text = smilieoptions[cat][smilieid][1];
						opt.smilieid = smilieid;
						opt.smiliepath = smilieoptions[cat][smilieid][0];
						opt.smilietitle = smilieoptions[cat][smilieid][2];
						sel.add(opt, is_ie ? sel.options.length : null);
					}
				}
			}

			sel.onchange = vB_Text_Editor_Events.prototype.smilieselect_onchange;
			break;
		}

		case 'attach':
		{
			sel.onmouseover = vB_Text_Editor_Events.prototype.attachselect_onmouseover;
			sel.onchange = vB_Text_Editor_Events.prototype.attachselect_onchange;
			break;
		}
	}

	while (obj.hasChildNodes())
	{
		obj.removeChild(obj.firstChild);
	}

	this.buttons[obj.cmd] = obj.appendChild(sel);
}

/**
* Button Context
*
* @param	object	The button object
* @param	string	Incoming event type
* @param	string	Control type - 'button' or 'menu'
*/
vB_Text_Editor.prototype.button_context = function(obj, state, controltype)
{
	if (this.disabled)
	{
		return;
	}

	if (typeof controltype == 'undefined')
	{
		controltype = 'button';
	}

	if (YAHOO.util.Dom.hasClass(obj, "imagebutton_disabled"))
	{
		return;
	}

	switch (obj.state)
	{
		case true: // selected button
		{
			switch (state)
			{
				case 'mouseover':
				case 'mousedown':
				case 'mouseup':
				{
					this.set_control_style(obj, controltype, 'down');
					break;
				}
				case 'mouseout':
				{
					this.set_control_style(obj, controltype, 'selected');
					break;
				}
			}
			break;
		}

		default: // not selected
		{
			switch (state)
			{
				case 'mouseover':
				case 'mouseup':
				{
					this.set_control_style(obj, controltype, 'hover');
					break;
				}
				case 'mousedown':
				{
					this.set_control_style(obj, controltype, 'down');
					break;
				}
				case 'mouseout':
				{
					this.set_control_style(obj, controltype, 'normal');
					break;
				}
			}
			break;
		}
	}
};

/* The set and restore code below is not perfect. One could hope that it does as advertised but the restore point isn't always the same as was set. */

vB_Text_Editor.prototype.setbookmark = function()
{
	var selection = this.wysiwyg_mode ? this.editdoc.selection : document.selection;
	if (is_ie && (selection.type == "Text" || selection.type == "None"))
	{
		var range = selection.createRange();
		//alert(range.offsetTop + " " + range.parentElement().nodeName + " " + range.parentElement().innerHTML);
		this.bookmark = range.getBookmark();
	}
}

vB_Text_Editor.prototype.restorebookmark = function()
{
	if (is_ie && this.bookmark)
	{
		var doc = this.wysiwyg_mode ? this.editdoc : document;
		var range = doc.body.createTextRange();
		range.moveToBookmark(this.bookmark);
		if (!this.wysiwyg_mode && range.parentElement().id != this.editorid + "_textarea")
		{
			// Move cursor to end!
			var range2 = document.selection.createRange();
			var range3 = range2.duplicate();
			range3.moveToElementText(this.editdoc);
			if (range3.text.length > 0)
			{
				var length = range3.text.length;
				var match = range3.text.match(/\r/g);
				if (match)
				{
					length = length - match.length;
				}
				range3.moveStart("character", length);
				range3.collapse();
				range3.select()
			}
		}
		else
		{
			range.select();
		}
		//alert(range.offsetTop + " " + range.parentElement().nodeName + " " + range.parentElement().innerHTML);
		this.bookmark = null;
	}
}

/**
* Check if we need to refocus the editor window
*/
vB_Text_Editor.prototype.check_focus = function()
{
	if (!this.editwin.hasfocus || (is_moz && is_mac)) // TODO: Firefox 3 Beta 5 Mac seems to miss a focus or blur event - this is a quick and dirty fix that will need further attention.
	{
		this.editwin.focus();
		this.restorebookmark();
		if (is_opera)
		{
			// see http://www.vbulletin.com/forum/bugs35.php?do=view&bugid=687
			this.editwin.focus();
		}
	}
};

/**
* Collapse the current selection and place the cursor where the end of the
* selection was.
*/
vB_Text_Editor.prototype.collapse_selection_end = function()
{
	var range;
	if (this.editdoc.selection)
	{
		range = this.editdoc.selection.createRange();
		// fix for horribly confusing IE bug where it randomly makes text white for funsies
		// see 3.6 bug #279 about the eval stuff
		eval("range." + "move" + "('character', -1);");
		range.collapse(false);
		range.select();
	}
	else if (document.selection && document.selection.createRange)
	{
		range = document.selection.createRange();
		range.collapse(false);
		range.select();
	}
	else if (typeof(this.editdoc.selectionStart) != 'undefined')
	{
		var sel_text = this.editdoc.value.substr(this.editdoc.selectionStart, this.editdoc.selectionEnd - this.editdoc.selectionStart);

		this.editdoc.selectionStart = this.editdoc.selectionStart + sel_text.vBlength();
	}
	else if (window.getSelection)
	{
		// don't think I can do anything for this
	}
}

/**
* Insert Link (base for WYSIWYG)
*/
vB_Text_Editor.prototype.createlink_wysiwyg = function(e, url)
{
	return this.apply_format('createlink', is_ie, (typeof url == 'undefined' ? true : url));
}

/**
* Insert Link
*/
vB_Text_Editor.prototype.createlink = function(e, url)
{
	if (this.wysiwyg_mode)
	{
		if (is_moz || is_opera)
		{
			if (typeof url == 'undefined')
			{
				url = this.show_prompt(vbphrase['enter_link_url'], 'http://', true);
			}
			if ((url = this.verify_prompt(url)) !== false)
			{
				if (this.get_selection())
				{
					this.apply_format('unlink');
					this.createlink_wysiwyg(e, url);
				}
				else
				{
					this.insert_text('<a href="' + url + '">' + url + '</a>');
				}
			}
			return true;
		}
		else
		{
			return this.createlink_wysiwyg(e, url);
		}
	}
	else
	{
		this.prompt_link('url', url, vbphrase['enter_link_url'], 'http://');
	}
};

/**
* Destroy Editor
*/
vB_Text_Editor.prototype.destroy = function()
{
	var i;
	// reset all buttons to default state
	for (i in this.buttons)
	{
		if (YAHOO.lang.hasOwnProperty(this.buttons, i))
		{
			this.set_control_style(this.buttons[i], 'button', 'normal');
		}
	}

	YAHOO.util.Event.removeListener(this.editdoc, "mousemove", vB_Text_Editor_Events.prototype.editdoc_onmousemove);
	YAHOO.util.Event.removeListener(this.editdoc, "click", vB_Text_Editor_Events.prototype.editdoc_onclick);
	YAHOO.util.Event.removeListener(this.editdoc, "mousedown", vB_Text_Editor_Events.prototype.editdoc_onmousedown);
	YAHOO.vBulletin.vBPopupMenu.close_all();
	this.remove_editor_dialog();
};

/**
* Disables the editor
*
* @param	string	Initial text for the editor
*/
vB_Text_Editor.prototype.disable_editor = function(text)
{
	if (this.wysiwyg_mode)
	{
		if (!this.disabled)
		{
			this.disabled = true;

			var hider = fetch_object(this.editorid + '_hider');
			if (hider)
			{
				hider.parentNode.removeChild(hider);
			}

			var div = document.createElement('div');
			div.id = this.editorid + '_hider';
			div.className = 'wysiwyg textbox hider';
			div.style.width = this.editbox.style.width;
			div.style.height = this.editbox.style.height;
			var childdiv = document.createElement('div');
			childdiv.style.padding = '8px';
			childdiv.innerHTML = text;
			div.appendChild(childdiv);
			this.editbox.parentNode.appendChild(div);

			// and hide the real editor
			this.editbox.style.display = 'none';
		}
	}
	else
	{
		if (!this.disabled)
		{
			this.disabled = true;

			if (typeof text != 'undefined')
			{
				this.editbox.value = text;
			}
			this.editbox.disabled = true;
		}
	}
};

/**
* Insert Email Link
*/
vB_Text_Editor.prototype.email = function(e, email)
{
	if (this.wysiwyg_mode)
	{
		if (typeof email == 'undefined')
		{
			email = this.show_prompt(vbphrase['enter_email_link'], '', true);
		}

		email = this.verify_prompt(email);

		if (email === false)
		{
			return this.apply_format('unlink');
		}
		else
		{
			var selection = this.get_selection();
			return this.insert_text('<a href="mailto:' + email + '">' + (selection ? selection : email) + '</a>', (selection ? true : false));
		}
	}
	else
	{
		this.prompt_link('email', email, vbphrase['enter_email_link'], '');
	}
};

/**
* Enables the editor
*
* @param	string	Initial text for the editor
*/
vB_Text_Editor.prototype.enable_editor = function(text)
{
	if (this.wysiwyg_mode)
	{
		if (typeof text != 'undefined')
		{
			this.set_editor_contents(text);
		}

		this.editbox.style.display = '';

		var hider = fetch_object(this.editorid + '_hider');
		if (hider)
		{
			hider.parentNode.removeChild(hider);
		}

		this.disabled = false;
	}
	else
	{
		if (typeof text != 'undefined')
		{
			this.editbox.value = text;
		}
		this.editbox.disabled = false;

		this.disabled = false;
	}
};

/**
* Format text
*
* @param	event	Event object
* @param	string	Formatting command
* @param	string	Optional argument to the formatting command
*
* @return	boolean
*/
vB_Text_Editor.prototype.format = function(e, cmd, arg)
{
	e = do_an_e(e);

	if (this.disabled)
	{
		return false;
	}

	if (cmd != 'redo')
	{
		this.history.add_snapshot(this.get_editor_contents());
	}

	if (cmd == 'switchmode')
	{
		switch_editor_mode(this.editorid);
		return;
	}
	else if (cmd.substr(0, 6) == 'resize')
	{
		// command format is resize_<direction>_<change> like resize_0_99 and resize_1_99
		var size_change = parseInt(cmd.substr(9), 10);
		var change_direction = parseInt(cmd.substr(7, 1), 10) == '1' ? 1 : -1;
		this.resize_editor(size_change * change_direction);
		return;
	}

	this.check_focus();
	var ret;
	if (cmd.substr(0, 4) == 'wrap')
	{
		ret = this.wrap_tags(cmd.substr(6), (cmd.substr(4, 1) == '1' ? true : false));
	}
	else if (this[cmd])
	{
		if (arg === false)
		{
			ret = this[cmd](e);
		}
		else
		{
			ret = this[cmd](e, arg);
		}
	}
	else
	{
		try
		{
			ret = this.apply_format(cmd, false, (typeof arg == 'undefined' ? true : arg));
		}
		catch(e)
		{
			this.handle_error(cmd, e);
			ret = false;
		}
	}

	if (cmd != 'undo')
	{
		this.history.add_snapshot(this.get_editor_contents());
	}

	this.set_context(cmd);

	this.check_focus();

	return ret;
};

/**
* Get Editor Contents
*/
vB_Text_Editor.prototype.get_editor_contents = function()
{
	if (this.wysiwyg_mode)
	{
		return this.editdoc.body.innerHTML;
	}
	else
	{
		return this.editdoc.value;
	}
};

/**
* Get Selected Text
*/
vB_Text_Editor.prototype.get_selection = function()
{
	if (this.wysiwyg_mode)
	{
		if (is_moz)
		{
			selection = this.editwin.getSelection();
			this.check_focus();
			var range = selection ? selection.getRangeAt(0) : this.editdoc.createRange();
			return this.read_nodes(range.cloneContents(), false);
		}
		else if (is_opera)
		{
			selection = this.editwin.getSelection();
			this.check_focus();
			range = selection ? selection.getRangeAt(0) : this.editdoc.createRange();
			var lsserializer = document.implementation.createLSSerializer();
			return lsserializer.writeToString(range.cloneContents());
		}
		else
		{
			var range = this.editdoc.selection.createRange();
			if (range.htmlText && range.text)
			{
				return range.htmlText;
			}
			else
			{
				var do_not_steal_this_code_html = '';
				for (var i = 0; i < range.length; i++)
				{
					do_not_steal_this_code_html += range.item(i).outerHTML;
				}
				return do_not_steal_this_code_html;
			}
		}
	}
	else
	{
		if (typeof(this.editdoc.selectionStart) != 'undefined')
		{
			return this.editdoc.value.substr(this.editdoc.selectionStart, this.editdoc.selectionEnd - this.editdoc.selectionStart);
		}
		else if (document.selection && document.selection.createRange)
		{
			return document.selection.createRange().text;
		}
		else if (window.getSelection)
		{
			return window.getSelection() + '';
		}
		else
		{
			return false;
		}
	}
};

/**
* Handle Error
*
* @param	string	Command name
* @param	event	Event object
*/
vB_Text_Editor.prototype.handle_error = function(cmd, e)
{
};

/**
* Editor initialization wrapper
*
* @param	string	Initial text to populate with
*/
vB_Text_Editor.prototype.init = function(initial_text)
{
	if (this.initialized)
	{
		return;
	}

	this.textobj.disabled = false;

	if (this.tempiframe)
	{
		this.tempiframe.parentNode.removeChild(this.tempiframe);
	}

	this.set_editor_contents(initial_text);

	this.set_editor_functions();

	this.init_controls();

	this.init_smilies(fetch_object(this.editorid + '_smiliebox'));

	if (typeof smilie_window != 'undefined' && !smilie_window.closed)
	{
		this.init_smilies(smilie_window.document.getElementById('smilietable'));
	}

	this.captcha = document.getElementById("imagestamp");
	if (this.captcha != null)
	{
		this.captcha.setAttribute("tabIndex", 1);
	}

	this.initialized = true;
};

/**
* Init command button (b, i, u etc.)
*
* @param	object	Current HTML button node
*/
vB_Text_Editor.prototype.init_command_button = function(obj)
{
	obj.cmd = obj.id.substr(obj.id.indexOf('_cmd_') + 5);
	obj.editorid = this.editorid;
	this.buttons[obj.cmd] = obj;

	if (obj.cmd == 'switchmode')
	{
		if (AJAX_Compatible)
		{
			obj.state = this.wysiwyg_mode ? true : false;
			this.set_control_style(obj, 'button', this.wysiwyg_mode ? 'selected' : 'normal');
		}
		else
		{
			obj.parentNode.removeChild(obj);
		}
	}
	else
	{
		obj.state = false;
		obj.mode = 'normal';
		if (obj.cmd == 'bold' || obj.cmd == 'italic' || obj.cmd == 'underline')
		{
			this.allowbasicbbcode = true;
		}
	}

	// event handlers
	obj.onclick = obj.onmousedown = obj.onmouseover = obj.onmouseout = vB_Text_Editor_Events.prototype.command_button_onmouseevent;
};

/**
* Init button controls for the editor
*/
vB_Text_Editor.prototype.init_controls = function()
{
	var controls = new Array();
	var i, j, buttons, imgs, control;

	if (this.controlbar == null)
	{
		return;
	}

	var buttons = YAHOO.util.Dom.getElementsByClassName('imagebutton', '', this.controlbar);
	for (i = 0; i < buttons.length; i++)
	{
		if (YAHOO.util.Dom.hasClass(buttons[i], "imagebutton") && buttons[i].id)
		{
			controls[controls.length] = buttons[i].id;

			// take 'title' from button image and apply to child images (IE only)
			if (is_ie)
			{
				imgs = buttons[i].getElementsByTagName("img");
				for (j = 0; j < imgs.length; j++)
				{
					if (imgs[j].alt == "")
					{
						imgs[j].title = buttons[i].title;
					}
				}
			}
		}
	}
	var menus = YAHOO.util.Dom.getElementsByClassName("menubutton", "", this.controlbar);
	for (i = 0; i < menus.length; i++)
	{
		if (YAHOO.util.Dom.hasClass(menus[i], "menubutton") && menus[i].id)
		{
			controls[controls.length] = menus[i].id;

			// take 'title' from button image and apply to child images (IE only)
			if (is_ie)
			{
				imgs = buttons[i].getElementsByTagName("img");
				for (j = 0; j < imgs.length; j++)
				{
					if (imgs[j].alt == "")
					{
						imgs[j].title = buttons[i].title;
					}
				}
			}
		}
	}

	for (i = 0; i < controls.length; i++)
	{
		control = fetch_object(controls[i]);

		if (control.id.indexOf(this.editorid + '_cmd_') != -1)
		{
			this.init_command_button(control);
		}
		else if (control.id.indexOf(this.editorid + '_popup_') != -1)
		{
			this.init_popup_menu(control);
		}
	}

	set_unselectable(this.controlbar);
};

// todo : delete this block
/**
* Init Menu Container DIV
*
* @param	string	Command string (forecolor, fontname etc.)
* @param	string	CSS width for the menu
* @param	string	CSS height for the menu
* @param	string	CSS overflow for the menu
*
* @return	object	Newly created menu element
*/
vB_Text_Editor.prototype.init_menu_container = function(cmd, width, height, overflow)
{
	var menu = document.createElement('div');

	menu.id = this.editorid + '_popup_' + cmd + '_menu';
	menu.className = 'vbmenu_popup';
	menu.style.display = 'none';
	menu.style.cursor = 'default';
	menu.style.padding = '3px';
	menu.style.width = width;
	menu.style.height = height;
	menu.style.overflow = overflow;

	return menu;
}


/**
* Init menu controls for the editor
*
* @param	object	HTML node
*
* @return	boolean	Whether to continue opening the popup (see popup_button_show)
*/
vB_Text_Editor.prototype.init_popup_menu = function(obj)
{
	if (this.disabled)
	{
		return false;
	}

	obj.cmd = obj.id.substr(obj.id.indexOf('_popup_') + 7);
	obj.editorid = this.editorid;
	this.buttons[obj.cmd] = obj;

	switch (obj.cmd)
	{
		case 'fontname':
		{
			this.build_fontname_popup(obj);
			break;
		}
		case 'fontsize':
		{
			this.build_fontsize_popup(obj);
			break;
		}
		case 'forecolor':
		{
			this.build_forecolor_popup(obj);
			break;
		}
		case 'smilie':
		{
			this.build_smilie_popup(obj);
			break;
		}
		case 'attach':
		{
			var children = YAHOO.util.Dom.getElementsByClassName('popupctrl', 'div', obj);
			if (!YAHOO.util.Event.getListeners(obj, "mouseover"))
			{
				YAHOO.util.Event.on(obj, "mouseover", vB_Text_Editor.prototype.menu_context, obj, this);
				YAHOO.util.Event.on(obj, "mouseout", vB_Text_Editor.prototype.menu_context, obj, this);
				YAHOO.util.Event.on(obj, "mouseup", vB_Text_Editor.prototype.menu_context, obj, this);
				YAHOO.util.Event.on(obj, "mousedown", vB_Text_Editor.prototype.menu_context, obj, this);
				YAHOO.util.Event.on(children[0], "click", vB_Text_Editor.prototype.attachpopup);
				YAHOO.util.Event.on("manageattach", "click", vB_Text_Editor_Events.prototype.attachmanage_onclick);

				this.popups['attach'] = true;
				if (typeof vB_Attachments != 'undefined' && vB_Attachments.has_attachments())
				{
					this.build_attachments_popup(obj);
				}
				else
				{
					// no popup to display
					if (typeof(vB_Attachments) != "undefined")
					{
						// this is causing problems but is needed
						//vB_Attachments.attachmanage();
					}
					return false;
				}
			}
		}
	}

	return true;
};

vB_Text_Editor.prototype.attachpopup = function(e, obj)
{
	if (typeof(vB_Attachments) != "undefined" && !vB_Attachments.has_attachments())
	{
		vB_Attachments.attachmanage();
	}
}

/**
* Init Smilies
*/
vB_Text_Editor.prototype.init_smilies = function(smilie_container)
{
	if (smilie_container != null)
	{
		var smilies = fetch_tags(smilie_container, 'img');
		for (var i = 0; i < smilies.length; i++)
		{
			if (smilies[i].id && smilies[i].id.indexOf('_smilie_') != false)
			{
				smilies[i].style.cursor = pointer_cursor;
				smilies[i].editorid = this.editorid;
				smilies[i].onclick = vB_Text_Editor_Events.prototype.smilie_onclick;
				smilies[i].unselectable = 'on';
			}
		}
	}
};

/**
* Insert Node at Selection (Mozilla)
*/
vB_Text_Editor.prototype.insert_node_at_selection = function(text)
{
	this.check_focus();

	var sel = this.editwin.getSelection();
	var range = sel ? sel.getRangeAt(0) : this.editdoc.createRange();
	sel.removeAllRanges();
	range.deleteContents();

	var node = range.startContainer;
	var pos = range.startOffset;

	switch (node.nodeType)
	{
		case Node.ELEMENT_NODE:
		{
			if (text.nodeType == Node.DOCUMENT_FRAGMENT_NODE)
			{
				selNode = text.firstChild;
			}
			else
			{
				selNode = text;
			}
			node.insertBefore(text, node.childNodes[pos]);
			this.add_range(selNode);
		}
		break;

		case Node.TEXT_NODE:
		{
			if (text.nodeType == Node.TEXT_NODE)
			{
				var text_length = pos + text.length;
				node.insertData(pos, text.data);
				range = this.editdoc.createRange();
				range.setEnd(node, text_length);
				range.setStart(node, text_length);
				sel.addRange(range);
			}
			else
			{
				node = node.splitText(pos);
				var selNode;
				if (text.nodeType == Node.DOCUMENT_FRAGMENT_NODE)
				{
					selNode = text.firstChild;
				}
				else
				{
					selNode = text;
				}
				node.parentNode.insertBefore(text, node);
				this.add_range(selNode);
			}
		}
		break;
	}
};


/**
* Insert Smilie
*/
vB_Text_Editor.prototype.insert_smilie = function(e, smilietext, smiliepath, smilieid)
{
	if (this.wysiwyg_mode)
	{
		if (is_moz || is_opera)
		{
			this.check_focus();

			try
			{
				this.apply_format('InsertImage', false, smiliepath);
				var smilies = fetch_tags(this.editdoc.body, 'img');
				for (var i = 0; i < smilies.length; i++)
				{
					if (smilies[i].src == smiliepath)
					{
						smilies[i].className = "inlineimg";

						if (smilies[i].getAttribute('smilieid') < 1)
						{
							smilies[i].setAttribute('smilieid', smilieid);
							smilies[i].setAttribute('border', "0");
						}
					}
				}
			}
			catch(e)
			{
				// failed... probably due to inserting a smilie over a smilie in mozilla
			}
		}
		else
		{
			this.check_focus();

			return this.insert_text('<img src="' + smiliepath + '" border="0" class="inlineimg" alt="0" smilieid="' + smilieid + '" />', false);
		}
	}
	else
	{
		this.check_focus();

		return this.insert_text(smilietext, smilietext.length, 0);
	}
};

/**
* Paste HTML
*/
vB_Text_Editor.prototype.insert_text = function(text, movestart, moveend)
{
	if (this.wysiwyg_mode)
	{
		if (is_moz || is_opera)
		{
			this.editdoc.execCommand('insertHTML', false, text);
		}
		else
		{
			this.check_focus();

			if (typeof(this.editdoc.selection) != 'undefined' && this.editdoc.selection.type != 'Text' && this.editdoc.selection.type != 'None')
			{
				movestart = false;
				this.editdoc.selection.clear();
			}

			var sel = this.editdoc.selection.createRange();
			sel.pasteHTML(text);

			if (text.indexOf('\n') == -1)
			{
				if (movestart === false)
				{
					// do nothing
				}
				else if (typeof movestart != 'undefined')
				{
					sel.moveStart('character', -text.vBlength() +movestart);
					sel.moveEnd('character', -moveend);
				}
				else
				{
					sel.moveStart('character', -text.vBlength());
				}
				sel.select();
			}
		}
	}
	else
	{
		var selection_changed = false;

		this.check_focus();

		if (typeof(this.editdoc.selectionStart) != 'undefined')
		{
			var opn = this.editdoc.selectionStart + 0;
			var scrollpos = this.editdoc.scrollTop;

			this.editdoc.value = this.editdoc.value.substr(0, this.editdoc.selectionStart) + text + this.editdoc.value.substr(this.editdoc.selectionEnd);

			if (movestart === false)
			{
				// do nothing
			}
			else if (typeof movestart != 'undefined')
			{
				this.editdoc.selectionStart = opn + movestart;
				this.editdoc.selectionEnd = opn + text.vBlength() - moveend;
			}
			else
			{
				this.editdoc.selectionStart = opn;
				this.editdoc.selectionEnd = opn + text.vBlength();
			}
			this.editdoc.scrollTop = scrollpos;
		}
		else if (document.selection && document.selection.createRange)
		{
			var sel = document.selection.createRange();
			sel.text = text.replace(/\r?\n/g, '\r\n');

			if (movestart === false)
			{
				// do nothing
			}
			else if (typeof movestart != 'undefined')
			{
				if ((movestart - text.vBlength()) != 0)
				{
					sel.moveStart('character', movestart - text.vBlength());
					selection_changed = true;
				}
				if (moveend != 0)
				{
					sel.moveEnd('character', -moveend);
					selection_changed = true;
				}
			}
			else
			{
				sel.moveStart('character', -text.vBlength());
				selection_changed = true;
			}

			if (selection_changed)
			{
				sel.select();
			}
		}
		else
		{
			// failed - just stuff it at the end of the message
			this.editdoc.value += text;
		}
	}
};

/**
* Insert Video
*
* @param	event	Event object
*
* @return	boolean
*/
vB_Text_Editor.prototype.insertvideo = function(e)
{
	this.create_editor_dialog('<img src="' + IMGDIR_MISC + '/lightbox_progress.gif" alt="" />', this.insertvideo_confirm);

	YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=fetchhtml", {
		success: this.insertvideo_ajax,
		failure: this.remove_editor_dialog,
		timeout: vB_Default_Timeout,
		argument: [this.editorid],
		scope: this
	}, SESSIONURL
		+ "&securitytoken="
		+ SECURITYTOKEN
		+ "&ajax=1"
		+ "&do=fetchhtml"
		+ "&template=editor_video_overlay"
	);
}

vB_Text_Editor.prototype.insertvideo_ajax = function(ajax)
{
	if (ajax.responseXML)
	{
		var html = ajax.responseXML.getElementsByTagName("html");

		if (html.length)
		{
			this.create_editor_dialog(html[0].firstChild.nodeValue, this.insertvideo_confirm, true);
			YAHOO.util.Dom.get("videourl").focus();
			YAHOO.util.Event.on("videourl", "keypress", this.dialog_submit_event, this, true);
			return;
		}
	}

	this.remove_editor_dialog();
}

vB_Text_Editor.prototype.insertvideo_confirm = function()
{
	var result = this.dialog.elements['videourl'].value;
	var doinsert = false;

	if (result = this.verify_prompt(result))
	{
		this.insert_text("[video]" + result + "[/video]");
	}

	this.remove_editor_dialog();
}

vB_Text_Editor.prototype.insertimagesettings_ajax = function(ajax)
{
	if (ajax.responseXML)
	{
		var html = ajax.responseXML.getElementsByTagName("html");

		if (html.length)
		{
			this.create_editor_dialog(html[0].firstChild.nodeValue, this.insertimagesettings_confirm, true);
			YAHOO.util.Dom.get("imageconfigtarget").src = this.activeimg.src;
			YAHOO.util.Event.on("vb_alignment_none", "click", vB_Text_Editor.prototype.insertimagesettings_alignment, this);
			YAHOO.util.Event.on("vb_alignment_right", "click", vB_Text_Editor.prototype.insertimagesettings_alignment, this);
			YAHOO.util.Event.on("vb_alignment_left", "click", vB_Text_Editor.prototype.insertimagesettings_alignment, this);
			YAHOO.util.Event.on("vb_alignment_center", "click", vB_Text_Editor.prototype.insertimagesettings_alignment, this);
			YAHOO.util.Event.on("vb_link_none", "click", vB_Text_Editor.prototype.insertimagesettings_link, this);
			YAHOO.util.Event.on("vb_link_content", "click", vB_Text_Editor.prototype.insertimagesettings_link, this);
			YAHOO.util.Event.on("vb_link_image", "click", vB_Text_Editor.prototype.insertimagesettings_link, this);
			this.remove_activeimg();
			return;
		}
	}

	this.remove_activeimg();
	this.remove_editor_dialog();
}

vB_Text_Editor.prototype.insertimagesettings_confirm = function()
{
	var targetimage = YAHOO.util.Dom.get("imageconfigtarget");
	var match = targetimage.src.match(/attachmentid=(\d+)/i);
	if (match)
	{
		var hidden_form = new vB_Hidden_Form(null);
		hidden_form.add_variables_from_object(YAHOO.util.Dom.get(this.editorid + "_dialog"));
		hidden_form.add_variable("attachmentid", match[1]);

		YAHOO.util.Dom.setStyle("imageoverlay_progress", "display", "inline");
		YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=saveimageconfig",
		{
			success: this.remove_editor_dialog,
			failure: this.insertimagesettings_failure,
			timeout: vB_Default_Timeout,
			scope: this
		}, SESSIONURL + 'securitytoken=' + SECURITYTOKEN + "&do=saveimageconfig&ajax=1&" + hidden_form.build_query_string());

	}
	else
	{
		alert(this.phrase["unable_to_parse_attachmentid_from_image"]);
		this.remove_editor_dialog();

	}
}

vB_Text_Editor.prototype.insertimagesettings_failure = function()
{
	YAHOO.util.Dom.setStyle("imageoverlay_progress", "display", "none");
	alert(vbphrase['saving_of_settings_failed']);
}

vB_Text_Editor.prototype.insertimagesettings_link = function(e)
{
	var target = YAHOO.util.Event.getTarget(e);
	var linkurl = YAHOO.util.Dom.get("linkurl");
	switch(target.id)
	{
		case "vb_link_none":
			linkurl.setAttribute("value", "");
			break;
		case "vb_link_content":
			linkurl.setAttribute("value", "What goes here?");
			break;
		case "vb_link_image":
			linkurl.setAttribute("value", "[image]");
			break;
	}
}

vB_Text_Editor.prototype.insertimagesettings_alignment = function(e)
{
	var target = YAHOO.util.Event.getTarget(e);
	YAHOO.util.Dom.removeClass("imageconfigtarget", "left");
	YAHOO.util.Dom.removeClass("imageconfigtarget", "right");
	YAHOO.util.Dom.removeClass("imageconfigtarget", "center");
	switch(target.id)
	{
		case "vb_alignment_left":
			YAHOO.util.Dom.addClass("imageconfigtarget", "left");
			break;
		case "vb_alignment_right":
			YAHOO.util.Dom.addClass("imageconfigtarget", "right");
			break;
		case "vb_alignment_center":
			YAHOO.util.Dom.addClass("imageconfigtarget", "center");
			break;
	}
}

vB_Text_Editor.prototype.insertimagesettings_failure = function(ajax)
{
	this.remove_activeimg();
	this.remove_editor_dialog();
}

/**
* Insert Image
*
* @param	event	Event object
*
* @return	boolean
*/
vB_Text_Editor.prototype.insertimage = function(e, forceoldway)
{
	if (this.wysiwyg_mode && typeof(vBulletin.attachinfo) != "undefined" && typeof(vBulletin.attachinfo.contenttypeid) != "undefined" && vBulletin.attachinfo.contenttypeid != 0 && typeof(forceoldway) == "undefined")
	{
		this.show_editor_progress();

		YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=fetchhtml", {
			success: this.insertimage_ajax,
			failure: this.remove_editor_dialog,
			timeout: vB_Default_Timeout,
			argument: [this.editorid],
			scope: this
		}, SESSIONURL
			+ "&securitytoken="
			+ SECURITYTOKEN
			+ "&ajax=1"
			+ "&do=fetchhtml"
			+ "&template=editor_upload_overlay"
		);
	}
	else
	{
		img = this.show_prompt(vbphrase['enter_image_url'], 'http://', true);
		if (img = this.verify_prompt(img))
		{
			return this.apply_format('insertimage', false, img);
		}
		else
		{
			return false;
		}
	}
};

vB_Text_Editor.prototype.insertimage_ajax = function(ajax)
{
	if (ajax.responseXML)
	{
		var html = ajax.responseXML.getElementsByTagName("html");
		if (html.length)
		{
			this.create_editor_dialog(html[0].firstChild.nodeValue, this.insertimage_confirm, true);
			if (typeof(TabsLightJS) == "undefined")
			{
				var me = this;
				YAHOO.vBulletin.LoadCss("css.php?sheet=yuiupload.css");
				YAHOO.vBulletin.LoadScript("clientscript/vbulletin-tabslight.js?v=" + SIMPLEVERSION, function() { init_tabslight() });
				YAHOO.vBulletin.LoadScript("clientscript/yui/combo/imageupload.js?v=" + SIMPLEVERSION, function() {
					YAHOO.vBulletin.LoadScript("clientscript/vbulletin_yuiupload.js?v=" + SIMPLEVERSION, function() {
						YAHOO.vBulletin.LoadScript("clientscript/vbulletin_imageup.js?v=" + SIMPLEVERSION, function() { me.insertimage_ready(); });
					});
				});
			}
			else
			{
				init_tabslight();
				this.insertimage_ready();
			}
			return;
		}
	}

	this.remove_editor_dialog();
}

vB_Text_Editor.prototype.insertimage_ready = function(e)
{
	this.hide_editor_progress();
	YAHOO.util.Dom.removeClass("editor_upload_overlay", "hidden");
	this.position_dialog(this.dialog);
	var imageuploadobj = new vB_ImageUpload(this.editorid + "_dialog");
//	if (imageuploadobj.ready)
//	{
		imageuploadobj.events.complete.subscribe(this.insertimage_complete, this);
		imageuploadobj.events.uploaddone.subscribe(this.insertimage_uploaddone, this);
//	}
//	else
//	{
//		this.remove_editor_dialog();
//		this.insertimage(null, true)
//	}
}

vB_Text_Editor.prototype.insertimage_confirm = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	if (!YAHOO.util.Dom.get("urlretrieve").checked)
	{
		if (img = this.verify_prompt(YAHOO.util.Dom.get("urlupload").value))
		{
			this.check_focus();
			this.apply_format('insertimage', false, img);
		}
		this.remove_editor_dialog();
		return;
	}

	var url = YAHOO.util.Dom.get("urlupload").value;
	if (!url)
	{
		this.remove_editor_dialog();
	}
	else
	{
		this.show_editor_progress();
		var callback =
		{
			upload  : this.insertimage_uploadurl,
			failure : function(ajax)
			{
				vBulletin_AJAX_Error_Handler(ajax)
				this.hide_editor_progress();
			},
			scope   : this
		};

		var formel = YAHOO.util.Dom.get(this.editorid + "_dialog");

		formel.action = "newattachment.php";
		formel.posthash.value =  vBulletin.attachinfo.posthash;
		formel.poststarttime.value =  vBulletin.attachinfo.poststarttime;
		formel.contenttypeid.value =  vBulletin.attachinfo.contenttypeid;

		for (var i in vBulletin.attachinfo.values)
		{
			var input = document.createElement("input");
			input.name = "values[" + i + "]";
			input.value = vBulletin.attachinfo.values[i];
			input.type = "hidden";
			formel.appendChild(input);
		}

		YAHOO.util.Connect.setForm(formel, true, true);
		YAHOO.util.Connect.asyncRequest("POST", "newattachment.php", callback, "ajax=1");

		// this is for Safari
		return false;
	}
}

vB_Text_Editor.prototype.insertimage_uploadurl = function(ajax)
{
	var attachmatch = ajax.responseText.match(/^ok - ([\d]+) - ([01])/);
	if (attachmatch)
	{
		this.insert_attachment(attachmatch[1], vBulletin.attachinfo.contenttypeid, attachmatch[2]);
	}
	else
	{
		var match = ajax.responseText.match(/^error: (.*)$/);
		alert(match ? match[1] : vbphrase['upload_failed']);
	}
	this.remove_editor_dialog();
}

vB_Text_Editor.prototype.insertimage_uploaddone = function(event, args, me)
{
	me.insert_attachment(args[0], args[1], args[2]);
}

vB_Text_Editor.prototype.insertimage_complete = function(event, args, me)
{
	me.remove_editor_dialog();
}

/**
* Insert Attachment
*/
vB_Text_Editor.prototype.insert_attachment = function(attachmentid, contenttypeid, thumbnail)
{
	if (thumbnail != 0 && this.wysiwyg_mode)
	{
		if (is_moz)
		{
			this.insert_text('<img src="' + 'attachment.php?' + (SESSIONURL != "" ? SESSIONURL + "amp;" : "") + 'attachmentid=' + attachmentid + '&amp;cid=' + contenttypeid + '&amp;stc=1" alt="" class="previewthumb" />');
		}
		else
		{
			this.insert_text('<img src="' + BBURL + '/attachment.php?' + (SESSIONURL != "" ? SESSIONURL + "amp;" : "") + 'attachmentid=' + attachmentid + '&amp;cid=' + contenttypeid + '&amp;stc=1" alt="" class="previewthumb" />');
		}
	}
	else
	{
		this.insert_text("[ATTACH]" + attachmentid + "[/ATTACH]");
	}
	this.collapse_selection_end();
}

/**
* Insert List
*/
vB_Text_Editor.prototype.insertlist = function(phrase, listtype)
{
	var opentag = '[LIST' + (listtype ? ('=' + listtype) : '') + ']\n';
	var closetag = '[/LIST]';
	var txt;

	if (txt = this.get_selection())
	{
		var regex = new RegExp('([\r\n]+|^[\r\n]*)(?!\\[\\*\\]|\\[\\/?list)(?=[^\r\n])', 'gi');
		txt = opentag + PHP.trim(txt).replace(regex, '$1[*]') + '\n' + closetag;
		this.insert_text(txt, txt.vBlength(), 0);
	}
	else
	{
		this.insert_text(opentag + closetag, opentag.length, closetag.length);

		if (YAHOO.env.ua.ie >= 7)
		{
			// respect a <base> tag (see #23097)
			var base_tag = fetch_tags(document, 'base');
			var modal_prefix;
			if (base_tag && base_tag[0] && base_tag[0].href)
			{
				modal_prefix = base_tag[0].href;
			}
			else
			{
				modal_prefix = '';
			}
			var listvalue = window.showModalDialog(modal_prefix + "clientscript/ieprompt.html?", { 'value': '', 'label': vbphrase['enter_list_item'], 'dir': document.dir, 'title': document.title, 'listtype': listtype }, "dialogWidth:320px; dialogHeight:232px; dialogTop:" + (parseInt(window.screenTop) + parseInt(window.event.clientY) + parseInt(document.body.scrollTop) - 100) + "px; dialogLeft:" + (parseInt(window.screenLeft) + parseInt(window.event.clientX) + parseInt(document.body.scrollLeft) - 160) + "px; resizable: No;");
			if (this.verify_prompt(listvalue))
			{
				this.insert_text(listvalue, listvalue.vBlength(), 0);
			}
		}
		else
		{
			while (listvalue = this.show_prompt(vbphrase['enter_list_item'], '', false))
			{
				listvalue = '[*]' + listvalue + '\n';
				this.insert_text(listvalue, listvalue.vBlength(), 0);
			}
		}
	}
}

/**
* Insert Ordered List
*/
vB_Text_Editor.prototype.insertorderedlist = function(e)
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('insertorderedlist', false, true);
	}
	else
	{
		this.insertlist(vbphrase['insert_ordered_list'], '1');
	}
};

/**
* Insert Unordered List
*/
vB_Text_Editor.prototype.insertunorderedlist = function(e)
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('insertunorderedlist', false, true);
	}
	else
	{
		this.insertlist(vbphrase['insert_unordered_list'], '');
	}
};

/**
* Menu Context
*
* @param	object	The menu container object
* @param	string	The state of the control
*/
vB_Text_Editor.prototype.menu_context = function(e, obj)
{
	if (this.disabled)
	{
		return;
	}

//		YAHOO.util.Dom.removeClass(obj, "imagebutton_selected");
//		YAHOO.util.Dom.removeClass(obj, "imagebutton_hover");
//		YAHOO.util.Dom.removeClass(obj, "imagebutton_down");

	var children = YAHOO.util.Dom.getElementsByClassName('popupctrl', 'div', obj);

	switch (e.type)
	{
		case 'mouseout':
		{
			if (!YAHOO.util.Dom.hasClass(children[0], "imagebutton_down"))
			{
				this.set_control_style(children[0], 'button', 'normal');
			}
			break;
		}
		case 'mousedown':
		{
			if (YAHOO.util.Dom.hasClass(children[0], "imagebutton_down"))
			{
				this.set_control_style(children[0], 'button', 'hover');
			}
			else
			{
				this.set_control_style(children[0], 'popup', 'down');
			}
			break;
		}
		case 'mouseup':
		case 'mouseover':
		{
			this.set_control_style(children[0], 'button', 'hover');
			break;
		}
	}
};

/**
* Open Smilie Window
*
* @param	integer	Window width
* @param	integer	Window height
*/
vB_Text_Editor.prototype.open_smilie_window = function(width, height)
{
	smilie_window = openWindow('misc.php?' + SESSIONURL + 'do=getsmilies&editorid=' + this.editorid, width, height, 'smilie_window');

	window.onunload = vB_Text_Editor_Events.prototype.smiliewindow_onunload;
}

/**
* Outdent
*/
vB_Text_Editor.prototype.outdent = function(e)
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('outdent', false, true);
	}
	else
	{
		var sel = this.get_selection();
		sel = this.strip_simple('indent', sel, 1);
		this.insert_text(sel);
	}
};

/**
* Prepare Form For Submit
*/
vB_Text_Editor.prototype.prepare_submit = function(subjecttext, minchars)
{
	var returnValue;

	if (this.wysiwyg_mode)
	{
		this.textobj.value = this.get_editor_contents();
		returnvalue = validatemessage(stripcode(this.textobj.value, true), subjecttext, minchars);
	}
	else
	{
		returnvalue = validatemessage(this.textobj.value, subjecttext, minchars);
	}

	if (returnvalue)
	{
		return returnvalue;
	}
	else if (this.captcha != null && this.captcha.failed)
	{
		return returnvalue;
	}
	else
	{
		this.check_focus();
		return false;
	}
}

/**
* Wrapper for Link / Email Link insert
*/
vB_Text_Editor.prototype.prompt_link = function(tagname, value, phrase, iprompt)
{
	if (typeof value == 'undefined')
	{
		value = this.show_prompt(phrase, iprompt, true);
	}
	if ((value = this.verify_prompt(value)) !== false)
	{
		if (this.get_selection())
		{
			this.apply_format('unlink');
			this.wrap_tags(tagname, value);
		}
		else
		{
			this.wrap_tags(tagname, value, value);
		}
	}
	return true;
};

/**
* Read Nodes (Mozilla)
*/
vB_Text_Editor.prototype.read_nodes = function(root, toptag)
{
	var html = "";
	var moz_check = /_moz/i;

	switch (root.nodeType)
	{
		case Node.ELEMENT_NODE:
		case Node.DOCUMENT_FRAGMENT_NODE:
		{
			var closed;
			var i;
			if (toptag)
			{
				closed = !root.hasChildNodes();
				html = '<' + root.tagName.toLowerCase();
				var attr = root.attributes;
				for (i = 0; i < attr.length; ++i)
				{
					var a = attr.item(i);
					if (!a.specified || a.name.match(moz_check) || a.value.match(moz_check))
					{
						continue;
					}

					html += " " + a.name.toLowerCase() + '="' + a.value + '"';
				}
				html += closed ? " />" : ">";
			}
			for (i = root.firstChild; i; i = i.nextSibling)
			{
				html += this.read_nodes(i, true);
			}
			if (toptag && !closed)
			{
				html += "</" + root.tagName.toLowerCase() + ">";
			}
		}
		break;

		case Node.TEXT_NODE:
		{
			//html = htmlspecialchars(root.data);
			html = PHP.htmlspecialchars(root.data);
		}
		break;
	}

	return html;
};

/**
* Creates a new text editor object of the same type as the current one.
* Arguments are passed to the object constructor.
*/
vB_Text_Editor.prototype.recreate_editor = function(editorid, mode, parsetype, parsesmilies, initial_text, ajax_extra)
{
	return new vB_Text_Editor(editorid, mode, parsetype, parsesmilies, initial_text, ajax_extra);
}

/**
* Redo using vB's history system
*/
vB_Text_Editor.prototype.redo = function()
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('redo', false, true);
	}
	else
	{
		this.history.move_cursor(1);
		var str;
		if ((str = this.history.get_snapshot()) !== false)
		{
			this.editdoc.value = str;
		}
	}
};

/**
* Remove Formatting
*/
vB_Text_Editor.prototype.removeformat = function(e)
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('removeformat', false, true);
	}
	else
	{
		var simplestrip = new Array('b', 'i', 'u');
		var complexstrip = new Array('font', 'color', 'size');

		var str = this.get_selection();
		if (str === false)
		{
			return;
		}

		var tag;
		// simple stripper
		for (tag in simplestrip)
		{
			if (YAHOO.lang.hasOwnProperty(simplestrip, tag))
			{
				str = this.strip_simple(simplestrip[tag], str);
			}
		}

		// complex stripper
		for (tag in complexstrip)
		{
			if (YAHOO.lang.hasOwnProperty(complexstrip, tag))
			{
				str = this.strip_complex(complexstrip[tag], str);
			}
		}

		this.insert_text(str);
	}
};

/**
* Resize Editor
*
* @param	integer	Number of pixels by which to resize the editor
*/
vB_Text_Editor.prototype.resize_editor = function(change)
{
	var newheight = parseInt(YAHOO.util.Dom.getStyle(this.editbox, "height"), 10) + change;

	if (newheight >= 60)
	{
		YAHOO.util.Dom.setStyle(this.editbox, "height", newheight + "px");
		if (is_ie)
		{
			YAHOO.util.Dom.setStyle(this.editdoc.body, "height", newheight - 6 + "px");
		}
		// remember the change if we're not shifting by a multiple of 99.
		// 99 is used with small editors (like QR) where we don't want the height to be saved.
		if (change % 99 != 0)
		{
			set_cookie('editor_height', newheight);
		}

		custom_editor_events['editor_switch'].fire(this);
	}

};

/**
* Set Color Context
*/
vB_Text_Editor.prototype.set_color_context = function(colorstate)
{
	if (this.buttons['forecolor'])
	{
		if (typeof colorstate == 'undefined')
		{
			colorstate = this.editdoc.queryCommandValue('forecolor');
		}
		if (colorstate != this.colorstate)
		{
			if (this.popupmode)
			{
				var obj = fetch_object(this.editorid + '_color_' + this.translate_color_commandvalue(this.colorstate));
				if (obj != null)
				{
					obj.state = false;
					this.button_context(obj, 'mouseout', 'menu');
				}

				this.colorstate = colorstate;

				elmid = this.editorid + '_color_' + this.translate_color_commandvalue(colorstate);
				obj = fetch_object(elmid);
				if (obj != null)
				{
					obj.state = true;
					this.button_context(obj, 'mouseout', 'menu');
				}
			}
			else
			{
				this.colorstate = colorstate;

				colorstate = this.translate_color_commandvalue(this.colorstate);

				for (var i = 0; i < this.buttons['forecolor'].options.length; i++)
				{
					if (this.buttons['forecolor'].options[i].value == colorstate)
					{
						this.buttons['forecolor'].selectedIndex = i;
						break;
					}
				}
			}
		}
	}
};

/**
* Set Context
*/
vB_Text_Editor.prototype.set_context = function(cmd)
{
	if (!this.wysiwyg_mode)
	{
		return;
	}

	for (var i in contextcontrols)
	{
		if (!YAHOO.lang.hasOwnProperty(contextcontrols, i))
		{
			continue;
		}

		var obj = fetch_object(this.editorid + '_cmd_' + contextcontrols[i]);
		if (obj != null)
		{
			var state = this.editdoc.queryCommandState(contextcontrols[i]);
			//alert (contextcontrols[i] + ' ' + state + ' ' + obj.state);
			if (obj.state != state)
			{
				obj.state = state;
				this.button_context(obj, (obj.cmd == cmd ? 'mouseover' : 'mouseout'));
			}
		}
	}

	this.set_font_context();
	this.set_size_context();
	this.set_color_context();
};

/**
* Set Control Style
*
* @param	object	The object to be styled
* @param	string	Control type - 'button' or 'menu'
* @param	string	The mode to use, corresponding to the istyles array
*/
vB_Text_Editor.prototype.set_control_style = function(obj, controltype, mode)
{
	if (obj.mode != mode)
	{
		obj.mode = mode;

		YAHOO.util.Dom.removeClass(obj, "imagebutton_selected");
		YAHOO.util.Dom.removeClass(obj, "imagebutton_hover");
		YAHOO.util.Dom.removeClass(obj, "imagebutton_down");

		switch(obj.mode)
		{
			case "down":
				YAHOO.util.Dom.addClass(obj, "imagebutton_down");
				break;
			case "selected":
				YAHOO.util.Dom.addClass(obj, "imagebutton_selected");
				break;
			case "hover":
				YAHOO.util.Dom.addClass(obj, "imagebutton_hover");
				break;
			case "normal":
				break;
		}

		return;
	}
};

/**
* Sets the text direction for the editor
*/
vB_Text_Editor.prototype.set_direction = function()
{
	this.editdoc.dir = this.textobj.dir;
};

/**
* Put the text into the editor
*/
vB_Text_Editor.prototype.set_editor_contents = function(initial_text)
{
	if (this.wysiwyg_mode)
	{
		if (fetch_object(this.editorid + '_iframe'))
		{
			this.editbox = fetch_object(this.editorid + '_iframe');
			YAHOO.util.Dom.setStyle(this.editbox, "display", "");
		}
		else
		{
			var iframe = document.createElement('iframe');
			if (is_ie && window.location.protocol == 'https:')
			{
				// workaround for IE throwing insecure page warnings
				iframe.src = 'clientscript/index.html';
			}
			if (is_ie)
			{
				YAHOO.util.Dom.setAttribute(iframe, "frameBorder", "0");	// camelCase Required!
			}
			this.editbox = this.textobj.parentNode.appendChild(iframe);
			this.editbox.id = this.editorid + '_iframe';
			this.editbox.tabIndex = 1;
			YAHOO.util.Dom.addClass(this.editbox, "textbox");
		}

		this.textobj.style.display = 'none';

		this.editwin = this.editbox.contentWindow;
		this.editdoc = this.editwin.document;

		this.write_editor_contents((typeof initial_text == 'undefined' ?  this.textobj.value : initial_text), true);

		if (this.editdoc.dir == 'rtl')
		{
		//	this.editdoc.execCommand('justifyright', false, null);
		}
		this.spellobj = this.editdoc.body;

		this.editdoc.editorid = this.editorid;
		this.editwin.editorid = this.editorid;

		if (is_moz)
		{
			this.editdoc.addEventListener('keypress', vB_Text_Editor_Events.prototype.editdoc_onkeypress, true);
		}
		else
		{
			YAHOO.util.Dom.setStyle(this.editdoc.body, "height", parseInt(YAHOO.util.Dom.getStyle(this.editbox, "height"), 10) - 6 + "px");
			YAHOO.util.Event.on(this.editwin, "scroll", vB_Text_Editor.prototype.resize_ie_body, this, true);
		}
	}
	else
	{
		var iframe = this.textobj.parentNode.getElementsByTagName('iframe')[0];
		if (iframe)
		{
			this.textobj.style.display = '';
			this.textobj.style.width = iframe.style.width;
			this.textobj.style.height = iframe.style.height;

			YAHOO.util.Dom.setStyle(iframe, "display", "none");
		}

		this.editwin = this.textobj;
		this.editdoc = this.textobj;
		this.editbox = this.textobj;
		this.spellobj = this.textobj;

		this.set_editor_width(this.textobj.style.width);

		if (typeof initial_text != 'undefined')
		{
			this.write_editor_contents(initial_text);
		}

		this.editdoc.editorid = this.editorid;
		this.editwin.editorid = this.editorid;

		this.history.add_snapshot(this.get_editor_contents());
	}

	if (typeof(vB_Attachments) != "undefined")
	{
		vB_Attachments.editor = this;
	}
};

/**
* Init Editor Functions
*/
vB_Text_Editor.prototype.set_editor_functions = function()
{
	if (this.wysiwyg_mode)
	{
		if (!YAHOO.util.Event.getListeners(this.editdoc, "mousemove"))
		{
			YAHOO.util.Event.on(this.editdoc, "mousemove", vB_Text_Editor_Events.prototype.editdoc_onmousemove, this, true);
			YAHOO.util.Event.on(this.editdoc, "click", vB_Text_Editor_Events.prototype.editdoc_onclick, this, true);
			YAHOO.util.Event.on(this.editdoc, "mousedown", vB_Text_Editor_Events.prototype.editdoc_onmousedown, this, true);
			if (is_moz)
			{
				YAHOO.util.Event.on(this.editdoc, "dragend", vB_Text_Editor_Events.prototype.editdoc_ondragend, this, true);
				this.editdoc.addEventListener('mouseup', vB_Text_Editor_Events.prototype.editdoc_onmouseup, true);
				this.editdoc.addEventListener('keyup', vB_Text_Editor_Events.prototype.editdoc_onkeyup, true);
				this.editwin.addEventListener('focus', vB_Text_Editor_Events.prototype.editwin_onfocus, true);
				this.editwin.addEventListener('blur', vB_Text_Editor_Events.prototype.editwin_onblur, true);
			}
			else
			{
				this.editdoc.onmouseup = vB_Text_Editor_Events.prototype.editdoc_onmouseup;
				this.editdoc.onkeyup = vB_Text_Editor_Events.prototype.editdoc_onkeyup;

				if (this.editdoc.attachEvent)
				{
					this.editdoc.body.attachEvent("onresizestart", vB_Text_Editor_Events.prototype.editdoc_onresizestart);
				}

				this.editwin.onfocus = vB_Text_Editor_Events.prototype.editwin_onfocus;
				this.editwin.onblur = vB_Text_Editor_Events.prototype.editwin_onblur;
			}
		}
	}
	else
	{
		if (this.editdoc.addEventListener)
		{
			if (!YAHOO.util.Event.getListeners(this.editdoc, "keypress"))
			{
				YAHOO.util.Event.on(this.editdoc, "keypress", vB_Text_Editor_Events.prototype.editdoc_onkeypress, this, true);
			}
		}
		else if (is_ie)
		{
			this.editdoc.onkeydown = vB_Text_Editor_Events.prototype.editdoc_onkeypress;
		}
		this.editwin.onfocus = vB_Text_Editor_Events.prototype.editwin_onfocus;
		this.editwin.onblur = vB_Text_Editor_Events.prototype.editwin_onblur;
	}
};

/**
* Set the CSS style of the editor
*/
vB_Text_Editor.prototype.set_editor_style = function()
{
	if (!this.wysiwyg_mode)
	{
		return;
	}

	var wysiwyg_csstext = '';
	var have_usercss = false;

	var all_stylesheets = fetch_all_stylesheets(document.styleSheets);

	for (var ss = 0; ss < all_stylesheets.length; ss++)
	{
		try
		{
			var rules = (all_stylesheets[ss].cssRules ? all_stylesheets[ss].cssRules : all_stylesheets[ss].rules);
			if (rules.length <= 0)
			{
				continue;
			}
		}
		catch(e)
		{
			// trying to access a stylesheet outside the allowed domain
			continue;
		}

		for (var i = 0; i < rules.length; i++)
		{
			if (!rules[i].selectorText)
			{
				continue;
			}

			var process = false;
			var selectors = new Array();

			if (rules[i].selectorText.indexOf('.wysiwyg') >= 0)
			{
				// contains wysiwyg redefinitions - pull out the wysiwyg-related selectors only
				var split_selectors = rules[i].selectorText.split(',');
				for (var selid = 0; selid < split_selectors.length; selid++)
				{
					if (split_selectors[selid].indexOf('.wysiwyg') >= 0)
					{
						// need to remove
						selectors.push(split_selectors[selid]);
					}

					if (split_selectors[selid].indexOf('#usercss') >= 0)
					{
						have_usercss = true;
					}
				}

				process = true;
			}

			if (process)
			{
				var css_rules = '{ ' + rules[i].style.cssText + ' }';

				// Moz only: make all rules important to override
				if (is_moz)
				{
					css_rules = css_rules.replace(/; /g, ' !important; ');
				}

				wysiwyg_csstext += selectors.join(', ') + ' ' + css_rules + '\n';
			}
		}
	}



	if (is_ie)
	{
		this.editdoc.createStyleSheet().cssText = wysiwyg_csstext;
	}
	else
	{
		var newss = this.editdoc.createElement('style');
		newss.type = 'text/css';
		newss.innerHTML = wysiwyg_csstext;
		this.editdoc.documentElement.childNodes[0].appendChild(newss);
	}

	if (have_usercss)
	{
		this.editdoc.body.parentNode.id = 'usercss';
	}

	this.editdoc.body.className = 'wysiwyg';
};

/**
* Sets the width of the editor. Works around an IE issue if necessary.
*
* @param	string	Width to set
* @param	bool	Whether to overwrite the "original" width setting
*/
vB_Text_Editor.prototype.set_editor_width = function(width, overwrite_original)
{
	if (this.wysiwyg_mode)
	{
		this.editbox.style.width = width;
	}
	else
	{
		if (typeof(this.textobj.style.oWidth) == 'undefined' || overwrite_original)
		{
			this.textobj.style.oWidth = width;
		}

		if (is_ie)
		{
			// IE has problems when a textarea has a percentage width (see bug 22816)
			this.textobj.style.width = this.textobj.style.oWidth;

			var orig_offset = this.textobj.offsetWidth;
			if (orig_offset > 0)
			{
				// this ensures that the effective width of the textarea is actually what we request!
				this.textobj.style.width = orig_offset + "px";
				this.textobj.style.width = (orig_offset + orig_offset - this.textobj.offsetWidth) + "px";
			}
		}
		else
		{
			this.textobj.style.width = width;
		}
	}
}

/**
* Set Font Context
*/
vB_Text_Editor.prototype.set_font_context = function(fontstate)
{
	if (this.buttons['fontname'])
	{
		if (typeof fontstate == 'undefined')
		{
			fontstate = this.editdoc.queryCommandValue('fontname');
		}
		switch (fontstate)
		{
			case '':
			{
				if (!is_ie && window.getComputedStyle)
				{
					fontstate = this.editdoc.body.style.fontFamily;
				}
			}
			break;

			case null:
			{
				fontstate = '';
			}
			break;
		}

		if (fontstate != this.fontstate)
		{
			this.fontstate = fontstate;
			var i;

			if (this.popupmode)
			{
				if (YAHOO.lang.hasOwnProperty(this.fontoptions, this.fontstate))
				{
					YAHOO.util.Dom.get(this.editorid + "_fontfield").innerHTML = this.fontoptions[this.fontstate];
				}
				else
				{
					YAHOO.util.Dom.get(this.editorid + "_fontfield").innerHTML = this.fontoptions[""];
				}
			}
			else
			{
				for (i = 0; i < this.buttons['fontname'].options.length; i++)
				{
					if (this.buttons['fontname'].options[i].value == thingy)
					{
						this.buttons['fontname'].selectedIndex = i;
						break;
					}
				}
			}
		}
	}
};

/**
* Set Size Context
*/
vB_Text_Editor.prototype.set_size_context = function(sizestate)
{
	if (this.buttons['fontsize'])
	{
		if (typeof sizestate == 'undefined')
		{
			sizestate = this.editdoc.queryCommandValue('fontsize');
		}
		switch (sizestate)
		{
			case null:
			case '':
			{
				if (is_moz)
				{
					sizestate = this.translate_fontsize(this.editdoc.body.style.fontSize);
				}
			}
			break;
		}
		if (sizestate != this.sizestate)
		{
			this.sizestate = sizestate;
			var i;

			if (this.popupmode)
			{
				if (YAHOO.lang.hasOwnProperty(this.sizeoptions, this.sizestate))
				{
					YAHOO.util.Dom.get(this.editorid + "_sizefield").innerHTML = this.sizeoptions[this.sizestate];
				}
				else
				{
					YAHOO.util.Dom.get(this.editorid + "_sizefield").innerHTML = this.sizeoptions[""];
				}
			}
			else
			{
				for (i = 0; i < this.buttons['fontsize'].options.length; i++)
				{
					if (this.buttons['fontsize'].options[i].value == this.sizestate)
					{
						this.buttons['fontsize'].selectedIndex = i;
						break;
					}
				}
			}
		}
	}
};

/**
* Show JS Prompt and filter result
*
* @param	string	Text for the dialog
* @param	string	Default value for the dialog
* @param	string	Whether to force LTR (for URLs, etc)
*
* @return	string
*/
vB_Text_Editor.prototype.show_prompt = function(dialogtxt, defaultval, forceltr)
{
	var returnvalue;
	if (YAHOO.env.ua.ie >= 7)
	{
		// respect a <base> tag (see #23097)
		var base_tag = fetch_tags(document, 'base');
		var modal_prefix;
		if (base_tag && base_tag[0] && base_tag[0].href)
		{
			modal_prefix = base_tag[0].href;
		}
		else
		{
			modal_prefix = '';
		}
		returnvalue = window.showModalDialog(modal_prefix + "clientscript/ieprompt.html?", { value: defaultval, label: dialogtxt, dir: document.dir, title: document.title, forceltr: (typeof(forceltr) != "undefined" ? forceltr : false) }, "dialogWidth:320px; dialogHeight:150px; dialogTop:" + (parseInt(window.screenTop) + parseInt(window.event.clientY) + parseInt(document.body.scrollTop) - 100) + "px; dialogLeft:" + (parseInt(window.screenLeft) + parseInt(window.event.clientX) + parseInt(document.body.scrollLeft) - 160) + "px; resizable: No;");
	}
	else
	{
		returnvalue = prompt(dialogtxt, defaultval);
	}

	// deal with unexpected return value
	if (typeof(returnvalue) == "undefined")
	{
		return false;
	}
	else if (returnvalue == false || returnvalue == null)
	{
		return returnvalue;
	}
	else
	{
		return PHP.trim(new String(returnvalue));
	}
};

/**
* Check Spelling (uses ieSpell from www.iespell.com)
*
* Eventually we hope to integrate SpellBound (http://spellbound.sourceforge.net) for Gecko.
*/
vB_Text_Editor.prototype.spelling = function()
{
	if (is_ie)
	{
		try
		{
			// attempt to instantiate ieSpell
			eval("new A" + "ctiv" + "eX" + "Ob" + "ject('ieSpell." + "ieSpellExt" + "ension').CheckD" + "ocumentNode(this.spellobj);");
		}
		catch(e)
		{
			// ask if user wants to download ieSpell
			if (e.number == -2146827859 && confirm(vbphrase['iespell_not_installed']))
			{
				// ooh they do...
				window.open('http://www.iespell.com/download.ph' + 'p');
			}
		}
	}
	else if (is_moz)
	{
		// attempt to instantiate SpellBound... when it supports this behaviour
	}
};

/**
* Strip a tag with an option
*/
vB_Text_Editor.prototype.strip_complex = function(tag, str, iterations)
{
	var opentag = '[' + tag + '=';
	var closetag = '[/' + tag + ']';

	if (typeof iterations == 'undefined')
	{
		iterations = -1;
	}

	while ((startindex = PHP.stripos(str, opentag)) !== false && iterations != 0)
	{
		iterations --;
		if ((stopindex = PHP.stripos(str, closetag)) !== false)
		{
			var openend = PHP.stripos(str, ']', startindex);
			if (openend !== false && openend > startindex && openend < stopindex)
			{
				var text = str.substr(openend + 1, stopindex - openend - 1);
				str = str.substr(0, startindex) + text + str.substr(stopindex + closetag.length);
			}
			else
			{
				break;
			}
		}
		else
		{
			break;
		}
	}

	return str;
};

/**
* Strip a simple tag...
*/
vB_Text_Editor.prototype.strip_simple = function(tag, str, iterations)
{
	var opentag = '[' + tag + ']';
	var closetag = '[/' + tag + ']';

	if (typeof iterations == 'undefined')
	{
		iterations = -1;
	}

	while ((startindex = PHP.stripos(str, opentag)) !== false && iterations != 0)
	{
		iterations --;
		if ((stopindex = PHP.stripos(str, closetag)) !== false)
		{
			var text = str.substr(startindex + opentag.length, stopindex - startindex - opentag.length);
			str = str.substr(0, startindex) + text + str.substr(stopindex + closetag.length);
		}
		else
		{
			break;
		}
	}

	return str;
};

/**
* Generates (and fires) Ajax call to switch the editor between the WYSIWYG and non-WYSIWYG modes.
*
*/
vB_Text_Editor.prototype.switch_editor_ajax = function()
{
	var mode = (this.wysiwyg_mode ? 0 : 1);

	YAHOO.util.Connect.asyncRequest("POST", 'ajax.php?do=editorswitch', {
		success: do_switch_editor_mode,
		timeout: vB_Default_Timeout,
		argument: [this.editorid, mode]
	//	scope: this
	}, SESSIONURL
		+ 'securitytoken=' + SECURITYTOKEN
		+ '&do=editorswitch'
		+ '&towysiwyg='	+ mode
		+ '&parsetype=' + this.parsetype
		+ '&allowsmilie=' + this.parsesmilies
		+ '&message=' + PHP.urlencode(this.get_editor_contents())
		+ (this.ajax_extra ? ('&' + this.ajax_extra) : '')
		+ (typeof this.textobj.form['options[allowbbcode]']  != 'undefined' ? '&allowbbcode=' + this.textobj.form['options[allowbbcode]'].checked : '')
	);
}

/**
* Function to translate the output from queryCommandState('forecolor') into something useful
*
* @param	string	Output from queryCommandState('forecolor')
*
* @return	string
*/
vB_Text_Editor.prototype.translate_color_commandvalue = function(forecolor)
{
	if (is_moz)
	{
		if (forecolor == '' || forecolor == null)
		{
			forecolor = window.getComputedStyle(this.editdoc.body, null).getPropertyValue('color');
		}

		if (forecolor.toLowerCase().indexOf('rgb') == 0)
		{
			var matches = forecolor.match(/^rgb\s*\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)$/);
			if (matches)
			{
				return this.translate_silly_hex((matches[1] & 0xFF).toString(16), (matches[2] & 0xFF).toString(16), (matches[3] & 0xFF).toString(16));
			}
			else
			{
				return this.translate_color_commandvalue(null);
			}
		}
		else
		{
			return forecolor;
		}
	}
	else
	{
		return this.translate_silly_hex((forecolor & 0xFF).toString(16), ((forecolor >> 8) & 0xFF).toString(16), ((forecolor >> 16) & 0xFF).toString(16));
	}
};

/**
* Translate CSS fontSize to HTML Font Size
*/
vB_Text_Editor.prototype.translate_fontsize = function(csssize)
{
	switch (csssize)
	{
		case '7.5pt':
		case '10px': return 1;
		case '10pt': return 2;
		case '12pt': return 3;
		case '14pt': return 4;
		case '18pt': return 5;
		case '24pt': return 6;
		case '36pt': return 7;
		default:     return '';
	}
}

/**
* Function to translate a hex like F AB 9 to #0FAB09 and then to coloroptions['#0FAB09']
*
* @param	string	Red value
* @param	string	Green value
* @param	string	Blue value
*
* @return	string	Option from coloroptions array
*/
vB_Text_Editor.prototype.translate_silly_hex = function(r, g, b)
{
	return "#" + (PHP.str_pad(r, 2, 0) + PHP.str_pad(g, 2, 0) + PHP.str_pad(b, 2, 0));
};

vB_Text_Editor.prototype.undo = function()
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('undo', false, true);
	}
	else
	{
		this.history.add_snapshot(this.get_editor_contents());
		this.history.move_cursor(-1);
		var str;
		if ((str = this.history.get_snapshot()) !== false)
		{
			this.editdoc.value = str;
		}
	}
};

/**
* Remove Link
*/
vB_Text_Editor.prototype.unlink = function(e)
{
	if (this.wysiwyg_mode)
	{
		return this.apply_format('unlink', false, true);
	}
	else
	{
		var sel = this.get_selection();
		sel = this.strip_simple('url', sel);
		sel = this.strip_complex('url', sel);
		this.insert_text(sel);
	}
};

/**
* Verify the return value of a javascript prompt
*
* @param	string	String to be checked
*
* @return	mixed	False on fail, string on success
*/
vB_Text_Editor.prototype.verify_prompt = function(str)
{
	switch(str)
	{
		case 'http://':
		case 'null':
		case 'undefined':
		case 'false':
		case '':
		case null:
		case false:
			return false;

		default:
			return str;
	}
};

/**
* Wrap Tags
*
* @param	string	Tag to wrap
* @param	boolean	Use option?
* @param	string	(Optional) selected text
*
* @return	boolean
*/
vB_Text_Editor.prototype.wrap_tags = function(tagname, useoption, selection)
{
	tagname = tagname.toUpperCase();

	switch (tagname)
	{
		case 'CODE':
		case 'HTML':
		case 'PHP':
		{
			this.apply_format('removeformat');
		}
		break;
	}

	if (typeof selection == 'undefined')
	{
		selection = this.get_selection();
		if (selection === false)
		{
			selection = '';
		}
		else
		{
			selection = new String(selection);
		}
	}

	var opentag;
	if (useoption === true)
	{
		var option = this.show_prompt(construct_phrase(vbphrase['enter_tag_option'], ('[' + tagname + ']')), '', false);
		if (option = this.verify_prompt(option))
		{
			opentag = '[' + tagname + '="' + option + '"' + ']';
		}
		else
		{
			return false;
		}
	}
	else if (useoption !== false)
	{
		opentag = '[' + tagname + '="' + useoption + '"' + ']';
	}
	else
	{
		opentag = '[' + tagname + ']';
	}

	var closetag = '[/' + tagname + ']';
	var text = opentag + selection + closetag;

	this.insert_text(text, opentag.vBlength(), closetag.vBlength());

	return false;
};

/**
* Writes contents to the editor
*
* @param	object	<textarea>
*/
vB_Text_Editor.prototype.write_editor_contents = function(text, doinit)
{
	if (this.wysiwyg_mode)
	{
		if (text == '')
		{
			if (is_ie)
			{
				text = '<p></p>';
			}
			else if (is_moz)
			{
				text = '<br />';
			}
		}
		if (this.editdoc && this.editdoc.initialized)
		{
			this.editdoc.body.innerHTML = text;
		}
		else
		{
			var doctype = "";
			var doct = document.childNodes[0];
			if (typeof(doct.text) != "undefined" && doct.nodeType == 8) // IE
			{
				doctype = doct.text;
			}
			else if (doct.nodeType == 10)
			{
				doctype = "<!DOCTYPE " + doct.name + " PUBLIC \"" + doct.publicId + "\"" + (doct.systemId ? "\"" + doct.systemId + "\"" : "") + ">";
			}
			doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

			// force ie7 mode for the iframe due to a bug with ie8 and imgs inserted at the end of a line
			var head = is_ie ? '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">' : '';
			text = doctype + "<html><head>" + head + "</head><body>" + text + "</body></html>";
			this.editdoc = this.editwin.document; // See: http://msdn.microsoft.com/workshop/author/dhtml/overview/XpSp2Compat.asp#caching
			this.editdoc.open('text/html', 'replace');
			this.editdoc.write(text);
			this.editdoc.close();

			if (doinit)
			{
				if (is_moz)
				{
					this.editdoc.designMode = 'on';
				}
				else
				{
					this.editdoc.body.contentEditable = true;
				}
			}
			this.editdoc.body.spellcheck = true;

			this.editdoc.initialized = true;

			this.set_editor_style();
		}
		this.resize_ie_body();
		this.set_direction();
	}
	else
	{
		this.textobj.value = text;
	}
}

/* Dialog management */

vB_Text_Editor.prototype.show_editor_progress = function()
{
	var progress = this.progress;

	if (!progress)
	{
		progress = document.createElement('div');
		document.body.appendChild(progress);

		progress.id = this.editorid + "_progress";
		YAHOO.util.Dom.setStyle(progress, "position", "absolute");
		YAHOO.util.Dom.setStyle(progress, "z-index", 1000);
		YAHOO.util.Dom.setStyle(progress, "border", "1px solid black");
		YAHOO.util.Dom.setStyle(progress, "background-color", "white");
	}

	progress.innerHTML = '<img src="' + IMGDIR_MISC + '/lightbox_progress.gif" alt="" />';
	this.position_dialog(progress, 300, 200);
	this.progress = progress;

	this.create_dialog_overlay();

	return progress;
}

vB_Text_Editor.prototype.hide_editor_progress = function()
{
	if (this.progress)
	{
		this.progress.parentNode.removeChild(this.progress);
		this.progress = null;
	}

	if (!this.dialog && this.dialog_overlay)
	{
		this.dialog_overlay.parentNode.removeChild(this.dialog_overlay);
		this.dialog_overlay = null;
	}
}

vB_Text_Editor.prototype.create_editor_dialog = function(html, confirm_callback, bookmark)
{
	var dialog = this.dialog;

	if (!dialog)
	{
		dialog = document.createElement('form');
		document.body.appendChild(dialog);

		dialog.encoding = "multipart/form-data";
		dialog.id = this.editorid + '_dialog';
		dialog.style.position = 'absolute';
		dialog.style.zIndex = 1000;
		dialog.style.border = '1px solid black';
		dialog.style.backgroundColor = 'white';

		YAHOO.util.Event.on(dialog, "submit", this.dialog_submit_event, this, true);
	}

	dialog.innerHTML = html;

	this.position_dialog(dialog);
	this.set_dialog_events(dialog);
	this.run_scripts_in_element(dialog);
	this.move_css_in_element(dialog);

	this.dialog = dialog;
	this.create_dialog_overlay();

	this.dialog_confirm_callback = confirm_callback;

	if (typeof(bookmark) != "undefined")
	{
		this.setbookmark();
	}

	return dialog;
}

vB_Text_Editor.prototype.position_dialog = function(dialog, width, height)
{
	var editor_region = YAHOO.util.Dom.getRegion(this.editorid);
	var dialog_region = YAHOO.util.Dom.getRegion(dialog);

	if (typeof(width) != "undefined")
	{
		dialog_region.height = height;
		dialog_region.width = width;
	}

	dialog.style.top =
		Math.max(
			parseInt(editor_region.top + (editor_region.height - dialog_region.height) / 2, 10),
			editor_region.top
		) + "px";

	dialog.style.left =
		Math.max(
			parseInt(editor_region.left + (editor_region.width - dialog_region.width) / 2, 10),
			editor_region.left
		) + "px";
}

vB_Text_Editor.prototype.set_dialog_events = function(dialog)
{
	var inputs = dialog.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++)
	{
		var submit = inputs[i];
		if (submit.type == 'submit')
		{
			YAHOO.util.Event.on(submit, 'click', this.dialog_button_click_event, this, true);
		}
	}
}

vB_Text_Editor.prototype.create_dialog_overlay = function()
{
	if (this.dialog_overlay)
	{
		return this.dialog_overlay;
	}

	var editor_region = YAHOO.util.Dom.getRegion(this.editorid);

	var overlay = document.createElement('div');
	document.body.appendChild(overlay);
	overlay.style.position = 'absolute';
	overlay.style.zIndex = 10;
	overlay.style.width = editor_region.width + "px";
	overlay.style.height = editor_region.height + "px";
	overlay.style.top = editor_region.top + "px";
	overlay.style.left = editor_region.left + "px";
	overlay.style.backgroundColor = "#000000";
	YAHOO.util.Dom.setStyle(overlay, 'opacity', .5);

	this.dialog_overlay = overlay;

	return overlay;
}

vB_Text_Editor.prototype.dialog_button_click_event = function(e)
{
	var submit = YAHOO.util.Event.getTarget(e);
	this.dialog_submitted = (submit && YAHOO.util.Dom.hasClass(submit, 'dialog_submit_button'));
}

vB_Text_Editor.prototype.dialog_submit_event = function(e)
{
	var char = e.charCode ? e.charCode : e.keyCode;
	if (typeof(char) != "undefined" && char != 0)
	{
		if (char == 13)
		{
			this.dialog_submitted = true;
		}
		else if (char == 27)
		{
			this.remove_editor_dialog();
		}
		else
		{
			return;
		}
	}

	var do_submit = this.dialog_submitted;
	this.dialog_submitted = false;

	YAHOO.util.Event.stopEvent(e);

	if (do_submit && this.dialog_confirm_callback)
	{
		this.dialog_confirm_callback.call(this, e);
	}
	else
	{
		this.remove_editor_dialog();
	}
}

vB_Text_Editor.prototype.remove_editor_dialog = function()
{
	if (this.dialog)
	{
		this.dialog.parentNode.removeChild(this.dialog);
		this.dialog = null;
	}

	if (this.dialog_overlay)
	{
		this.dialog_overlay.parentNode.removeChild(this.dialog_overlay);
		this.dialog_overlay = null;
	}

	if (this.progress)
	{
		this.progress.parentNode.removeChild(this.progress);
		this.progress = null;
	}

	this.dialog_props = {};
}

vB_Text_Editor.prototype.run_scripts_in_element = function(el)
{
	var script, i, script_tag;
	var scripts = el.getElementsByTagName('script'), head_tag = document.getElementsByTagName('head')[0];
	var length = scripts.length;

	for (i = 0; i < length; i++)
	{
		script = scripts[i];

		script_tag = document.createElement('script');
		if (script.type)
		{
			script_tag.type = script.type;
		}
		if (script.text)
		{
			script_tag.text = script.text;
		}
		if (script.src)
		{
			script_tag.src = script.src;
		}
		if (script.id)
		{
			script_tag.id = script.id;
		}
		head_tag.appendChild(script_tag);
	}

}

vB_Text_Editor.prototype.move_css_in_element = function(el)
{
	var links = el.getElementsByTagName("link");
	var head_tag = document.getElementsByTagName('head')[0];
	var length = links.length;

	for (i = 0; i < length; i++)
	{
		var link = links[i];

		link_tag = document.createElement("link");
		if (link.rel)
		{
			link_tag.rel = link.rel;
		}
		if (link.type)
		{
			link_tag.type = link.type;
		}
		if (link.href)
		{
			link_tag.href = link.href;
		}

		head_tag.appendChild(link_tag);
		link.parentNode.removeChild(link);
	}
}

vB_Text_Editor.prototype.remove_activeimg = function()
{
	if (this.activeimg)
	{
		YAHOO.util.Dom.removeClass(this.activeimg, "previewthumbactive");
		this.activeimg = null;
		var pencils = YAHOO.util.Dom.getElementsByClassName("previewthumbedit", "img", this.editdoc.body);
		var length = pencils.length;
		for (var x = 0; x < length; x++)
		{
			pencils[x].parentNode.removeChild(pencils[x]);
		}
	}
}

/**
* Resize iframe body for IE
*/
vB_Text_Editor.prototype.resize_ie_body = function()
{
	if (is_ie && this.wysiwyg_mode)
	{
		var scrollheight = this.editdoc.body.scrollHeight;
		var bodyheight = parseInt(YAHOO.util.Dom.getStyle(this.editdoc.body, "height"), 10);
		var boxheight = parseInt(YAHOO.util.Dom.getStyle(this.editbox, "height"), 10);
		if (scrollheight < boxheight)
		{
			YAHOO.util.Dom.setStyle(this.editdoc.body, "height", boxheight - 6 + "px");
		}
		else
		{
			YAHOO.util.Dom.setStyle(this.editdoc.body, "height", scrollheight - 7 + "px");
		}
		//alert(scrollheight + " " + bodyheight + " " + boxheight);
	}
};

// =============================================================================
// Editor event handler functions

/**
* Class containing editor event handlers
*/
function vB_Text_Editor_Events()
{
}

/**
* Handles a click on a smilie in the smiliebox
*/
vB_Text_Editor_Events.prototype.smilie_onclick = function(e)
{
	vB_Editor[this.editorid].insert_smilie(e,
		this.alt,
		this.src,
		this.id.substr(this.id.lastIndexOf('_') + 1)
	);

	if (typeof smilie_window != 'undefined' && !smilie_window.closed)
	{
		smilie_window.focus();
	}

	return false;
};

/**
* Handles a mouse event on a command button
*/
vB_Text_Editor_Events.prototype.command_button_onmouseevent = function(e)
{
	e = do_an_e(e);

	if (e.type == 'click' && !YAHOO.util.Dom.hasClass(this.editorid + "_cmd_" + this.cmd, "imagebutton_disabled"))
	{
		vB_Editor[this.editorid].format(e, this.cmd, false, true);
	}

	vB_Editor[this.editorid].button_context(this, e.type);
};


/**
* Handles a selection from a formatting <select> menu
*/
vB_Text_Editor_Events.prototype.formatting_select_onchange = function(e)
{
	var arg = this.options[this.selectedIndex].value;
	if (arg != '')
	{
		vB_Editor[this.editorid].format(e, this.cmd, arg);
	}
	this.selectedIndex = 0;
};

/**
* Handles a selection from the smilies <select> menu
*/
vB_Text_Editor_Events.prototype.smilieselect_onchange = function(e)
{
	if (this.options[this.selectedIndex].value != '')
	{
		vB_Editor[this.editorid].insert_smilie(e,
			this.options[this.selectedIndex].value,
			this.options[this.selectedIndex].smiliepath,
			this.options[this.selectedIndex].smilieid
		);
	}
	this.selectedIndex = 0;
};

/**
* Handles a selection from the attachment <select> menu
*/
vB_Text_Editor_Events.prototype.attachselect_onchange = function(e)
{
	var arg = this.options[this.selectedIndex].value;
	if (arg != '')
	{
		vB_Editor[this.editorid].wrap_tags('attach', false, arg);
	}
	this.selectedIndex = 0;
};

/**
* Handles a mouse over event for the attachment <select> menu
*/
vB_Text_Editor_Events.prototype.attachselect_onmouseover = function(e)
{
	if (this.options.length <= 2)
	{
		vB_Editor[this.editorid].build_attachments_popup(this);
		return true;
	}
};

/**
* Handles a mouse event on a menu option
*/
vB_Text_Editor_Events.prototype.menuoption_onmouseevent = function(e)
{
	e = do_an_e(e);
	vB_Editor[this.editorid].button_context(this, e.type, 'menu');
};

/**
* Handles a click on a formatting option in the font/size menus
*/
vB_Text_Editor_Events.prototype.formatting_option_onclick_font = function(e)
{
	vB_Editor[this.editorid].format(e, this.cmd, this.firstChild.innerHTML);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

/**
* Handles a click on a formatting option in the font/size menus
*/
vB_Text_Editor_Events.prototype.formatting_option_onclick_size = function(e)
{
	vB_Editor[this.editorid].format(e, this.cmd, this.firstChild.firstChild.innerHTML);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

/**
* Handles a click on a color option in the color menu
*/
vB_Text_Editor_Events.prototype.coloroption_onclick = function(e)
{
	fetch_object(this.editorid + '_color_bar').style.backgroundColor = this.colorname;
	vB_Editor[this.editorid].format(e, this.cmd, this.colorname);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

/**
* Handles a click on the color instant-select button
*/
vB_Text_Editor_Events.prototype.colorout_onclick = function(e)
{
	e = do_an_e(e);
	vB_Editor[this.editorid].format(e, 'forecolor', fetch_object(this.editorid + '_color_bar').style.backgroundColor);
	return false;
};

/**
* Handles a click on a smilie option in the smilie menu
*/
vB_Text_Editor_Events.prototype.smilieoption_onclick = function(e)
{
	vB_Editor[this.editorid].button_context(this, 'mouseout', 'menu');
	vB_Editor[this.editorid].insert_smilie(e, this.smilietext, fetch_tags(this, 'img')[0].src, this.smilieid);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

/**
* Handles a click on the 'More' link in the smilie menu
*/
vB_Text_Editor_Events.prototype.smiliemore_onclick = function(e)
{
	vB_Editor[this.editorid].open_smilie_window(smiliewindow_x, smiliewindow_y);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

/**
* Handles a click on the 'Manage' link in the attachments menu
*/
vB_Text_Editor_Events.prototype.attachmanage_onclick = function(e)
{
	YAHOO.vBulletin.vBPopupMenu.close_all();
	if (typeof(vB_Attachments) != "undefined")
	{
		vB_Attachments.attachmanage();
	}
};

/**
* Handles a click on an attachment option in the attachments menu
*/
vB_Text_Editor_Events.prototype.attachoption_onclick = function(e)
{
	vB_Editor[this.editorid].button_context(this, 'mouseout', 'menu');
	vB_Editor[this.editorid].wrap_tags('attach', false, this.attachmentid);
	YAHOO.vBulletin.vBPopupMenu.close_all();
};

vB_Text_Editor_Events.prototype.attachinsertall_onclick = function(e)
{
	var insert = '';
	var breakchar = (vB_Editor[this.editorid].wysiwyg_mode ? '<br /><br />' : '\r\n\r\n');

	for (var id in vB_Attachments.attachments)
	{
		if (YAHOO.lang.hasOwnProperty(vB_Attachments.attachments, id))
		{
			insert += insert != '' ? breakchar : '';
			insert += '[ATTACH]' + id + '[/ATTACH]';
		}
	}
	vB_Editor[this.editorid].insert_text(insert);
	YAHOO.vBulletin.vBPopupMenu.close_all();
}

/**
* Closes the smilie window when the main page exits
*/
vB_Text_Editor_Events.prototype.smiliewindow_onunload = function(e)
{
	if (typeof smilie_window != 'undefined' && !smilie_window.closed)
	{
		smilie_window.close();
	}
};

/**
* Sets editwin.hasfocus = true on focus
*
* this.editwin.hasFocus() does not appear to work reliably, so we have to set hasfocus manually
*/
vB_Text_Editor_Events.prototype.editwin_onfocus = function(e)
{
	this.hasfocus = true;
};

/**
* Sets editwin.hasfocus = false on blur
*
* this.editwin.hasFocus() does not appear to work reliably, so we have to set hasfocus manually
*/
vB_Text_Editor_Events.prototype.editwin_onblur = function(e)
{
	this.hasfocus = false;
};

/**
* Displays an image attachment as editable
*
*/
vB_Text_Editor_Events.prototype.editdoc_onmousemove = function(e)
{
	var targetentered = YAHOO.util.Event.getTarget(e);
	var targetleft = YAHOO.util.Event.getRelatedTarget(e);

	try
	{
		// this seems to throw an error when mousing over the scrollbars in IE due to targetentered being an object
		if (YAHOO.util.Dom.hasClass(targetentered, "previewthumbedit") || this.activeimg == targetentered)
		{
			if (YAHOO.util.Dom.hasClass(targetentered, "previewthumbedit"))
			{
				YAHOO.util.Dom.addClass(targetentered, "previewthumbedithover");
			}
			return;
		}
	}
	catch(e)
	{
		this.remove_activeimg();
		return;
	}

	this.remove_activeimg();

	if (YAHOO.util.Dom.hasClass(targetentered, "previewthumb"))
	{
		YAHOO.util.Dom.addClass(targetentered, "previewthumbactive");
		var editimg = this.editdoc.body.appendChild(this.editdoc.createElement("img"));
		YAHOO.util.Dom.addClass(editimg, "previewthumbedit");
		editimg.src = BBURL + "/" + IMGDIR_MISC + "/pencil.png";

		YAHOO.util.Dom.setStyle(editimg, 'cursor', 'pointer');
		// Prevent resize handles in IE
		editimg.unselectable = "on";
		editimg.contentEditable = "false";
		YAHOO.util.Dom.setStyle(editimg, '-moz-user-select', 'none');

		/* setXY has bug under IE rtl so we need to use other way to set (See bug #36110) */
		var xy = YAHOO.util.Dom.getXY(targetentered);
		if (is_ie && this.editdoc.dir == 'rtl')
		{
			YAHOO.util.Dom.setY(editimg, xy[1]);
			// Set editimg left style manually
			var editorwidth = this.editdoc.body.clientWidth;
			YAHOO.util.Dom.setStyle(editimg, 'left', (0 - (editorwidth - targetentered.offsetLeft - targetentered.width)) + 'px');
		}
		else
		{
			if (this.editdoc.dir == 'rtl')
			{
				xy[0] += targetentered.width - editimg.width;
			}
		YAHOO.util.Dom.setXY(editimg, xy);
		}
		this.activeimg = targetentered;
	}
}

/**
*	Fix an obscene bug with firefox? Probably not a bug but annoying nontheless
*
*/
vB_Text_Editor_Events.prototype.editdoc_ondragend = function(e)
{
	// Just go through all attachments and strip out junk
	var images = YAHOO.util.Dom.getElementsByClassName('previewthumb', 'img', this.editdoc);
	var pos = false;
	for (i = 0; i < images.length; i++)
	{
		if (pos = PHP.stripos(images[i].src, "attachment.php"))
		{
			images[i].src = images[i].src.substr(pos);
		}
	}
}

/**
* Stop the "edit" icon on an editable image from being manipulated (FF)
*
*/
vB_Text_Editor_Events.prototype.editdoc_onmousedown = function(e)
{
	var target = YAHOO.util.Event.getTarget(e);

	try
	{
		// this seems to throw an error when clicking on the scrollbar in IE8/XP
		if (YAHOO.util.Dom.hasClass(target, "previewthumbedit"))
		{
			YAHOO.util.Event.stopEvent(e);
		}
	}
	catch(e)
	{
		return;
	}
}

/**
* Edit an image
*
*/
vB_Text_Editor_Events.prototype.editdoc_onclick = function(e)
{
	this.check_focus();
	var target = YAHOO.util.Event.getTarget(e);
	if (YAHOO.util.Dom.hasClass(target, "previewthumbedit"))
	{
		this.create_editor_dialog('<img src="' + IMGDIR_MISC + '/lightbox_progress.gif" alt="" />', this.insertimagesettings_confirm);

		var match = this.activeimg.src.match(/attachmentid=(\d+)/i);
		var attachmentid = parseInt(match[1]);

		YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=fetchhtml_imagesettings", {
			success: this.insertimagesettings_ajax,
			failure: this.insertimagesettings_failure,
			timeout: vB_Default_Timeout,
			argument: [this.editorid],
			scope: this
		}, SESSIONURL
			+ "&securitytoken="
			+ SECURITYTOKEN
			+ "&ajax=1"
			+ "&do=fetchhtml_imagesettings"
			+ "&attachmentid=" + attachmentid
		);
	}
}

/**
* Sets context and hides menus on mouse clicks in the editor
*/
vB_Text_Editor_Events.prototype.editdoc_onmouseup = function(e)
{
	vB_Editor[this.editorid].set_context();
	YAHOO.vBulletin.vBPopupMenu.close_all();
	vB_Editor[this.editorid].resize_ie_body();
};

/**
* Sets context on key presses in the editor
*/
vB_Text_Editor_Events.prototype.editdoc_onkeyup = function(e)
{
	vB_Editor[this.editorid].set_context();
	vB_Editor[this.editorid].resize_ie_body();
};

/**
* Handle a keypress event in the editor window
*/
vB_Text_Editor_Events.prototype.editdoc_onkeypress = function(e)
{
	if (!e)
	{
		e = window.event;
	}

	if (e.ctrlKey && !e.altKey)
	{
		if (vB_Editor[this.editorid].allowbasicbbcode == false)
		{
			return;
		}
		var code = e.charCode ? e.charCode : e.keyCode;
		var cmd;
		switch (String.fromCharCode(code).toLowerCase())
		{
			case 'b': cmd = 'bold'; break;
			case 'i': cmd = 'italic'; break;
			case 'u': cmd = 'underline'; break;
			default: return;
		}

		e = do_an_e(e);
		vB_Editor[this.editorid].apply_format(cmd, false, null);
		return false;
	}
	else if (e.keyCode == 9)
	{
		if (e.shiftKey || (e.modifiers && (e.modifiers & 4)))
		{
			// shift-tab, let the browser handle
			return;
		}

		if (is_opera)
		{
			// can't supress tab events in Opera
			return;
		}

		// first lets try tag, then post icon, then submit, then just let it proceed the browser doing the tab
		if (fetch_object('tag_add_input') != null)
		{
			fetch_object('tag_add_input').focus();
		}
		else if (fetch_object('rb_iconid_0') != null)
		{
			fetch_object('rb_iconid_0').focus();
		}
		else if (fetch_object(this.editorid + '_save') != null)
		{
			fetch_object(this.editorid + '_save').focus();
		}
		else if (fetch_object('qr_submit') != null)
		{
			fetch_object('qr_submit').focus();
		}
		else
		{
			return;
		}
		e = do_an_e(e);
		return;
	}
};

/**
* Stop resizing of images in IE
*/
vB_Text_Editor_Events.prototype.editdoc_onresizestart = function(e)
{
	if (e.srcElement.tagName == 'IMG')
	{
		return false;
	}
};

/**
* Save editor contents to textarea so if we hit back / forward its not lost
* Only appears to work with Firefox at the moment
*/
function save_iframe_to_textarea()
{
	for (var editorid in vB_Editor)
	{
		if (!YAHOO.lang.hasOwnProperty(vB_Editor, editorid))
		{
			continue;
		}

		if (vB_Editor[editorid].wysiwyg_mode && vB_Editor[editorid].initialized)
		{
			vB_Editor[editorid].textobj.value = vB_Editor[editorid].get_editor_contents();
		}
	}
}

if (window.attachEvent)
{
	window.attachEvent('onbeforeunload', save_iframe_to_textarea);
}
else if(window.addEventListener)
{
	window.addEventListener('unload', save_iframe_to_textarea, true);
}

// #############################################################################
// Editor mode switcher system

/**
* Switch editor between standard and wysiwyg modes
*
* @param	string	EditorID (vB_Editor[x])
*/
function switch_editor_mode(editorid)
{
	if (AJAX_Compatible)
	{
		if (vB_Editor[editorid].influx == 1)
		{
			// already clicked, go away!
			return;
		}
		else
		{
			vB_Editor[editorid].influx = 1;
		}

		YAHOO.vBulletin.vBPopupMenu.close_all();

		vB_Editor[editorid].switch_editor_ajax();
	}
}

function do_switch_editor_mode(ajax)
{
	if (ajax.responseXML)
	{
		var editorid = ajax.argument[0];

		// destroyer
		var parsetype = vB_Editor[editorid].parsetype;
		var parsesmilies = vB_Editor[editorid].parsesmilies;
		var ajax_extra = vB_Editor[editorid].ajax_extra;

		vB_Editor[editorid].destroy();

		var message_node = ajax.responseXML.getElementsByTagName('message')[0];
		if (typeof message_node != 'undefined')
		{
			message_node = message_node.firstChild;
		}
		var parsed_text = (message_node ? message_node.nodeValue : '');

		var matches = parsed_text.match(/&#([0-9]+);/g);
		if (matches)
		{
			for (var i = 0; typeof matches[i] != 'undefined'; i++)
			{
				if (submatch = matches[i].match(/^&#([0-9]+);$/))
				{
					parsed_text = parsed_text.replace(submatch[0], String.fromCharCode(submatch[1]));
				}
			}
		}

		// removing this line because of IE7 focus issues - on second activation of IE7 WYWIWYG, focus is lost
		//vB_Editor[ajax.argument[0]] = null; // collect the garbage

		vB_Editor[editorid] = vB_Editor[editorid].recreate_editor(editorid, ajax.argument[1], parsetype, parsesmilies, parsed_text, ajax_extra);
		vB_Editor[editorid].check_focus();
		fetch_object(editorid + '_mode').value = ajax.argument[1];

		custom_editor_events['editor_switch'].fire(vB_Editor[editorid]);
	}
}


// #############################################################################
// Generic global editor variables

/**
* Define which buttons are context-controlled
*
* @var	array	Context controls
*/
var contextcontrols = new Array(
	'bold',
	'italic',
	'underline',
	'justifyleft',
	'justifycenter',
	'justifyright',
	'insertorderedlist',
	'insertunorderedlist'
);

// #############################################################################
// vB_History
// #############################################################################

function vB_History()
{
	this.cursor = -1;
	this.stack = new Array();
}

// =============================================================================
// vB_History methods

vB_History.prototype.move_cursor = function(increment)
{
	var test = this.cursor + increment;
	if (test >= 0 && this.stack[test] != null && typeof this.stack[test] != 'undefined')
	{
		this.cursor += increment;
	}
};

vB_History.prototype.add_snapshot = function(str)
{
	if (this.stack[this.cursor] == str)
	{
		return;
	}
	else
	{
		this.cursor++;
		this.stack[this.cursor] = str;

		if (typeof this.stack[this.cursor + 1] != 'undefined')
		{
			this.stack[this.cursor + 1] = null;
		}
	}
};

vB_History.prototype.get_snapshot = function()
{
	if (typeof this.stack[this.cursor] != 'undefined' && this.stack[this.cursor] != null)
	{
		return this.stack[this.cursor];
	}
	else
	{
		return false;
	}
};

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35418 $
|| ####################################################################
\*======================================================================*/
