	xjxSWFup = xajax.ext.SWFupload;
	xjxSWFup.instances = {};
	
	xjxSWFup.forms = {};

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.selectFiles = function() 
	{
		xjxSWFup.swf.selectFiles();
	}	
	
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.processParameters = function(oRequest) 
	{

		if ("SWFupload" == oRequest.mode) 
		{


			var instances = {};
			var queued = 0;
			if ("string" == typeof oRequest.SWFform) {
				for (a in xjxSWFup.forms[oRequest.SWFform]) {
						var stats = xjxSWFup.instances[a].getStats();
						if (stats.files_queued > 0) {
							queued += stats.files_queued;
							instances[a] = xjxSWFup.instances[a];
						}
				}
			} else if ("string" == typeof oRequest.SWFfield) {
					instances[oRequest.SWFfield] = xjxSWFup.instances[oRequest.SWFfield];
					var stats = instances[oRequest.SWFfield].getStats();
					queued += stats.files_queued;
			} else {
					for (a in xjxSWFup.instances) {

						var stats = xjxSWFup.instances[a].getStats();
						if (stats.files_queued > 0) {
							queued += stats.files_queued;
							instances[a] = xjxSWFup.instances[a];
						}
					}					
			}
			
			oRequest.instances=instances;
			oRequest.queued=queued;
			if ( 0 == queued ) return xjxSWFup.bak.processParameters(oRequest);



				var xx = xajax;
				var xt = xx.tools;
				var rd = [];
				
				var separator = '';
				for (var sCommand in oRequest.functionName) 
				{
					if ('constructor' != sCommand) 
					{
						rd.push(separator);
						rd.push(sCommand);
						rd.push('=');
						rd.push(encodeURIComponent(oRequest.functionName[sCommand]));
						separator = '&';
					}
				}
				var dNow = new Date();
				rd.push('&xjxr=');
				rd.push(dNow.getTime());
				delete dNow;
			
				if (oRequest.parameters) 
				{
					var i = 0;
					var iLen = oRequest.parameters.length;
					while (i < iLen) {
						var oVal = oRequest.parameters[i];
						if ('object' == typeof oVal && null != oVal) 
						{
							try {
								var oGuard = {};
								oGuard.depth = 0;
								oGuard.maxDepth = oRequest.maxObjectDepth;
								oGuard.size = 0;
								oGuard.maxSize = oRequest.maxObjectSize;
								oVal = xt._objectToXML(oVal, oGuard);
							} catch (e) {
								oVal = '';
							}
							rd.push('&xjxargs[]=');
							oVal = encodeURIComponent(oVal);
							rd.push(oVal);
							++i;
						} else {
							rd.push('&xjxargs[]=');
							oVal = xt._escape(oVal);
							if ('undefined' == typeof oVal || null == oVal) {
								rd.push('*');
							} else {
								var sType = typeof oVal;
								if ('string' == sType)
									rd.push('S');
								else if ('boolean' == sType)
									rd.push('B');
								else if ('number' == sType)
									rd.push('N');
								oVal = encodeURIComponent(oVal);
								rd.push(oVal);
							}
							++i;
						}
					}
				}
				oRequest.requestURI = oRequest.URI;
				oRequest.requestURI += oRequest.requestURI.indexOf('?')== -1 ? '?' : '&';
				oRequest.requestURI += rd.join('');
				rd = [];
				oRequest.requestData = rd.join('');
			return;
		}
		xjxSWFup.bak.processParameters(oRequest);
	}
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.prepareRequest = function (oRequest) 
	{
		if ("SWFupload" == oRequest.mode && oRequest.queued > 0) 
		{


			return;
		}
		return xjxSWFup.bak.prepareRequest(oRequest);
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.submitRequest = function(oRequest) 
	{
		if ("SWFupload" == oRequest.mode && oRequest.queued > 0) {
			
			for (a in oRequest.instances) 
			{
				var swf = oRequest.instances[a];

				swf.customSettings.oRequest = oRequest;
				swf.setUploadURL(oRequest.requestURI);
		
				swf.settings.upload_success_handler =  function (oFile,response) 
				{
					if ('function' == typeof this.old_upload_success_handler) this.old_upload_success_handler(oFile);

					try {
						xjxSWFup.tools.removeFile(oFile);
						var xx = xajax;
						var xt = xx.tools;
						var xcb = xx.callback;
						var gcb = xcb.global;
						var lcb = oRequest.callback;
						var oRet = oRequest.returnValue;
						if (response) {
							var cmd = (new DOMParser()).parseFromString(response, "text/xml");
							var seq=0;
							var child = cmd.documentElement.firstChild;
							var oRet = oRequest.returnValue;
							xt.xml.processFragment(child, seq, oRet,oRequest);
				
							if (null == xx.response.timeout)
								xt.queue.process(xx.response);
						
						}
					} catch (ex) {
					}
				}


				swf.settings.upload_complete_handler = function(oFile) 
				{
					if ('function' == typeof this.old_upload_complete_handler) this.old_upload_complete_handler(oFile);
				}

  

				swf.settings.upload_start_handler = function(oFile) 
				{
					
					if ('function' == typeof this.old_upload_start_handler) this.old_upload_start_handler(oFile);
					
					
					oRequest.startTime = new Date();
					var fileDiv = xajax.$('SWFup_'+oFile.id);
					
					var progress = document.createElement('div');
					progress.style.backgroundColor='#00085F';
					progress.style.height='1px';
					progress.style.width='1px';
					progress.id='SWFup_progress_'+oFile.id;
					fileDiv.appendChild(progress);
				}
				swf.settings.upload_progress_handler = function (oFile, bytesLoaded, bytesTotal) 
				{

					upload = {};
					upload.received=bytesLoaded;
					upload.total=bytesTotal;
					upload.state="uploading";
					var reqTime = new Date();
		
					upload.lastbytes = oRequest.lastbytes;
					upload.now = reqTime.getTime() / 1000;
					upload.start = oRequest.startTime.getTime()/ 1000;
					
					var step = upload.received / (upload.total / 100);
					var progressbar = xajax.$('SWFup_progress_'+oFile.id);
					var w = Math.round(340 *  step / 100);
					progressbar.style.width=w+'px';
					
					var progress = xajax.$("swf_queued_filesize_"+oFile.id);
					var elapsed = upload.now-upload.start;
					var rate = xjxSWFup.tools.formatBytes(upload.received/ elapsed).toString() + '/s';
					progress.innerHTML = "<i>"+rate + "</i> "+xjxSWFup.tools.formatBytes(upload.received)+"/"+xjxSWFup.tools.formatBytes(upload.total);
					
					
			    oRequest.lastbytes = upload.received;					
					
				}
	
				swf.settings.upload_error_handler = function(file, errorCode, message) 
				{
					alert("Error Code: "+errorCode+", File name: " + file.name + ", Message: " + message);		
				};
				swf.setFilePostName(a);
				swf.startUpload();
			}
			return;
		}
		xjxSWFup.bak.submitRequest(oRequest);
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools = {};

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.mergeObj = function() 
	{
		if ('object' != typeof arguments) return;
		
		var res = {};
		var len = arguments.length;
		for (var i=0;i<len;i++) 
		{
			var obj = arguments[i];
			for (a in obj) 
			{
				res[a] = obj[a];
			}
		}
		return res;
	}
	
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools._parseFields = function(children,parent,config,multiple) 
	{
    var result={};
		var iLen = children.length;
		for (var i = 0; i < iLen; ++i) 
		{
			var child = children[i];
			if ('undefined' != typeof child.childNodes)
				var res2 = xjxSWFup.tools._parseFields(child.childNodes,child,config,multiple);
				result = xjxSWFup.tools.mergeObj(result,res2);
			if (child.name) 
			{
				if ('file' == child.type) 
				{
					var id = xjxSWFup.tools._transformField(child,parent,config,multiple);					
					result[id] = true;
				}
			}
		}
		return result;
	}
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools._transformField = function(child,parent,config,multiple) {
	
		var settings = {
											flash_url : xjxSWFup.config.javascript_URI+"swfupload_f9.swf",
											file_size_limit : "0",
											file_types : "*.*",
											file_types_description : "All Files",
											file_upload_limit : 0,
											file_queue_limit : 0,
											file_queue_error_handler  : xjxSWFup.tools.fileQueueError,
											debug: false
										};
		if ('object' == typeof config) settings = xjxSWFup.tools.mergeObj(settings,config);
		var swf =  new SWFUpload(settings);	
		
		var tmpName = child.name;
		
		xajax.forms.insertInput(child,"button","SWFup_Btn_"+tmpName,"SWFup_Btn_"+tmpName);

		parent.removeChild(child);

		var BtnSelect = xajax.$("SWFup_Btn_"+tmpName);		
		BtnSelect.value = "Browse files";
		if (true === multiple) 
		{
			BtnSelect.onclick = function () {
				swf.selectFiles();
			}
		} else {
			BtnSelect.onclick = function () {
				swf.selectFile();
			}
		}
		BtnSelect.name = tmpName;
		
		var Queue = document.createElement('div');
		Queue.id = 'SWFqueue_'+tmpName;
		parent.appendChild(Queue);
		
		swf.settings.file_queue_error_handler = function(oFile, errorCode, message) 
		{
			 xjxSWFup.tools.fileQueueError(oFile, errorCode, message,Queue,this);
		}
		
		swf.settings.file_queued_handler = function (oFile) 
		{
			xjxSWFup.tools._addQueuedFile(oFile,Queue,this);
		}

		swf.old_upload_start_handler = swf.settings.upload_success_handler;
		swf.old_upload_success_handler = swf.settings.upload_success_handler;
		swf.old_upload_complete_handler = swf.settings.upload_complete_handler;



		xjxSWFup.instances[child.name] = swf;
		return child.name;
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.transForm = function(form_id,config,multiple) 
	{
		
	var oForm = xajax.$(form_id);		
	if (oForm)
		if (oForm.childNodes) {
			var fields = xjxSWFup.tools._parseFields(oForm.childNodes,oForm,config,multiple);		
			xjxSWFup.forms[form_id] = fields;
		}
	}


/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.transField = function(elm_id,config,multiple) 
	{
		
	var oField = xajax.$(elm_id);		
	if (oField)
		xjxSWFup.tools._transformField(oField,oField.parentNode,config,multiple);		
	}
	
	
/* ---------------------------------------------------------------------------------------------------------------------------------- */
	xjxSWFup.tools._addQueuedFile = function(oFile,parent,swf) 
	{	

			var container = document.createElement('div');
			container.id = "SWFup_"+oFile.id;
			container.className="swf_queued_file";
			
			var remove = document.createElement('div');
			remove.className="swf_queued_file_remove";
			remove.innerHTML="&nbsp;";
			remove.onclick= function () {
				swf.cancelUpload(oFile.id);
				parent.removeChild(container);
			}
			container.appendChild(remove);
			
			var label = document.createElement('div');
			label.className="swf_queued_filename";
			label.innerHTML = oFile.name;
			container.appendChild(label);
		
			var fSize = document.createElement('div');
			fSize.className="swf_queued_filesize";
			fSize.id="swf_queued_filesize_"+oFile.id;
			fSize.innerHTML = xjxSWFup.tools.formatBytes(oFile.size);
			container.appendChild(fSize);
			
			var fClear= document.createElement('div');
			fClear.style.clear='both';
			container.appendChild(fClear);
			parent.appendChild(container);			
		
			return container;
	}


 
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.fileQueueError = function(oFile, errorCode, message,Queue,swf) 
	{
		try 
		{
			if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) 
			{
				alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
				return;
			}

			var fileDiv = xjxSWFup.tools._addQueuedFile(oFile,Queue,swf);
			
			var statusMsg = document.createElement('div');
			fileDiv.appendChild(statusMsg);
			fileDiv.style.backgroundColor='#FF7F7F';
	
			switch (errorCode) 
			{
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					statusMsg.innerHTML = "File is too big.";
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					statusMsg.innerHTML = "Cannot upload Zero Byte files.";
					break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					statusMsg.innerHTML = "Invalid File Type.";
					break;
				default:
					if (file !== null) {
						statusMsg.innerHTML = "Unhandled Error";
					}
					break;
			}
		} catch (ex) {
	       // this.debug(ex);
    }
		setTimeout(function () 
		{
			xjxSWFup.tools.FadeOut(fileDiv,100);
		}, 2500);
	}
 

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.removeFile = function(oFile) 
	{
		var fileDiv = xajax.$('SWFup_'+oFile.id);
		fileDiv.style.backgroundColor='#A4CDEF';
		setTimeout(function () {
			xjxSWFup.tools.FadeOut(fileDiv,100);
		}, 2500);

	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.FadeOut = function(elm,opacity) 
	{

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 40;	// 15 fps

	if (opacity > 0) 
	{
		opacity -= reduceOpacityBy;
		if (opacity < 0) 
		{
			opacity = 0;
		}

		if (elm.filters) 
		{
			try 
			{
				elm.filters.item("DXImageTransform.Microsoft.Alpha").opacity = opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				elm.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + opacity + ")";
			}
		} else {
			elm.style.opacity = opacity / 100;
		}
	}

	if (this.height > 0) 
	{
		this.height -= reduceHeightBy;
		if (this.height < 0) 
		{
			this.height = 0;
		}

		elm.style.height = this.height + "px";
	}

	if (this.height > 0 || opacity > 0) 
	{
		var oSelf = this;
		setTimeout(function () 
		{
			xjxSWFup.tools.FadeOut(elm,opacity);
		}, rate);
	} else {
		elm.style.display = "none";
	}
}


/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.formatBytes = function(bytes) 
	{

		var ret = {};
		if (bytes / 1204  < 1024) 
		{
		    return (Math.round(bytes / 1024 * 100)/100).toString()+ " KB";
		} else {
		    return (Math.round(bytes / 1024 / 1024 * 100)/100).toString()+ " MB";
		}
		return ret;		
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.getPid = function() 
	{
		var pid_str = "";
		for (i=0;i<=3;i++) 
		{
			var pid = 0;
			pid = Math.random();
			while( Math.ceil(pid).toString().length<8) 
			{
				pid *= 10;
			}
			pid = Math.ceil(pid).toString();		
			pid_str = pid_str+pid.toString();
		}
		return pid_str;
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.clearInstances = function() 
	{
		for (a in xjxSWFup.instances) 
		{
			delete(xjxSWFup.instances[a]);
		}
		this.instances = {};
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

SWFUpload.prototype.destroy = function() {

		this.stopUpload();
		var container = this.movieElement.parentNode;
		container.removeChild(this.movieElement);
		container.parentNode.removeChild(container);
		this.movieElement = null;
 		delete SWFUpload.instances[this.movieName];
	
}
/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.tools.destroyField = function(field_id) 
	{
		if ('undefined' == typeof xjxSWFup.instances[field_id]) return;
		xjxSWFup.instances[field_id].destroy();
		delete(xjxSWFup.instances[field_id]);
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */


	xjxSWFup.tools.destroyForm	= function(field_id) 
	{
		if ("undefined" == typeof xjxSWFup.forms[field_id]) return;
		for (a in xjxSWFup.forms[field_id]) 
		{
			xjxSWFup.tools.destroyField(a);
		}
		delete xjxSWFup.forms[field_id];
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */

	xjxSWFup.bak = {};
	xjxSWFup.bak.prepareRequest = xajax.prepareRequest;
	xjxSWFup.bak.submitRequest = xajax.submitRequest;
	xjxSWFup.bak.responseProcessor = xajax.responseProcessor;
	xjxSWFup.bak.processParameters = xajax.processParameters;

/* ---------------------------------------------------------------------------------------------------------------------------------- */
	
	xajax.prepareRequest = xjxSWFup.prepareRequest;
	xajax.submitRequest = xjxSWFup.submitRequest;
	xajax.processParameters = xjxSWFup.processParameters;

/* ---------------------------------------------------------------------------------------------------------------------------------- */


	xajax.commands['SWFup_tfo'] = function(args) {
		try {
			args.cmdFullName = 'ext.SWFupload.tools.transForm';
			
			if ("string" == typeof args.data.config.upload_complete_handler) {
				try {
					eval("var foo = "+args.data.config.upload_complete_handler);
					args.data.config.upload_complete_handler = foo;
				} catch(ex) {delete(args.data.config.upload_complete_handler);debugObj(ex);}
			}
	
			if ("string" == typeof args.data.config.upload_success_handler) {
				try {
					eval("var foo = "+args.data.config.upload_success_handler);
					args.data.config.upload_success_handler = foo;
				} catch(ex) {delete(args.data.config.upload_success_handler);debugObj(ex);}
			}
	
			
			xajax.ext.SWFupload.tools.transForm(args.id, args.data.config,args.data.multi);
		}catch(ex){alert(ex);}
		return true;
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */
	
	xajax.commands['SWFup_tfi'] = function(args) {
		try {
			args.cmdFullName = 'ext.SWFupload.tools.transField';
			
			
			if ("string" == typeof args.data.config.upload_complete_handler) {
				try {
					eval("var foo = "+args.data.config.upload_complete_handler);
					args.data.config.upload_complete_handler = foo;
				} catch(ex) {delete(args.data.config.upload_complete_handler);debugObj(ex);}
			}
	
			if ("string" == typeof args.data.config.upload_success_handler) {
				try {
					eval("var foo = "+args.data.config.upload_success_handler);
					args.data.config.upload_success_handler = foo;
				} catch(ex) {delete(args.data.config.upload_success_handler);debugObj(ex);}
			}
	
			
			xajax.ext.SWFupload.tools.transField(args.id, args.data.config,args.data.multi);
		}catch(ex){alert(ex);}
		return true;
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */
	
	xajax.commands['SWFup_dfo'] = function(args) {
		try {
			args.cmdFullName = 'ext.SWFupload.tools.destroyForm';
			xajax.ext.SWFupload.tools.destroyForm(args.id);
		}catch(ex){debugObj(ex);}
		return true;
	}

/* ---------------------------------------------------------------------------------------------------------------------------------- */
	
	xajax.commands['SWFup_dfi'] = function(args) {
		try {
			args.cmdFullName = 'ext.SWFupload.tools.destroyField';
			xajax.ext.SWFupload.tools.destroyField(args.id);
		}catch(ex){debugObj(ex);}
		return true;
	}
	


/* ---------------------------------------------------------------------------------------------------------------------------------- */

	/*

	Function: DOMParser
	
	Prototype DomParser for IE/Opera

*/
if (typeof DOMParser == "undefined") {
   DOMParser = function () {}

   DOMParser.prototype.parseFromString = function (str, contentType) {
      if (typeof ActiveXObject != "undefined") {
         	var d = new ActiveXObject("Microsoft.XMLDOM");
         	d.loadXML(str);
         return d;
      } else if (typeof XMLHttpRequest != "undefined") {
         var req = new XMLHttpRequest;
         req.open("GET", "data:" + (contentType || "application/xml") +
                         ";charset=utf-8," + encodeURIComponent(str), false);
         if (req.overrideMimeType) {
            req.overrideMimeType(contentType);
         }
         req.send(null);
         return req.responseXML;
      }
   }
}	