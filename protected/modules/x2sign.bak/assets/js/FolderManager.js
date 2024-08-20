/***********************************************************************************
* Copyright (C) 2011-2019 X2 Engine Inc. All Rights Reserved.
*
* X2 Engine Inc.
* P.O. Box 610121
* Redwood City, California 94061 USA
* Company website: http://www.x2engine.com
*
* X2 Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
* to install and use this Software for your internal business purposes only
* for the number of users purchased by you. Your use of this Software for
* additional users is not covered by this license and requires a separate
* license purchase for such users. You shall not distribute, license, or
* sublicense the Software. Title, ownership, and all intellectual property
* rights in the Software belong exclusively to X2 Engine. You agree not to file
* any patent applications covering, relating to, or depicting this Software
* or modifications thereto, and you agree to assign any patentable inventions
* resulting from your use of this Software to X2 Engine.
*
* THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
* EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
***********************************************************************************/






x2.FolderManager = (function () {

function FolderManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        urls: {
            moveFolder: null,
            index: null,
            deleteFileFolder: null
        },
        translations: {
            createFolder: '',
            deleteFolderConf: '',
            deleteDocConf: '',
            folderDeleted: '',
            docDeleted: '',
            permissionsMissing: '',
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}


FolderManager.prototype._moveStart = function (fileSysObj$) {
    fileSysObj$.addClass ('moving-file');
    fileSysObj$.find('.file-system-object-attributes').hide();
    fileSysObj$.find('.file-system-object-link').css('width','100%');
    $('#file-delete').show();
//    fileSysObj$.next ().width (fileSysObj$.width ()).animate ({
//        width: 150,
//    }, 500);
};

FolderManager.prototype._moveStop = function (fileSysObj$) {
    fileSysObj$.removeClass ('moving-file');
    fileSysObj$.find('.file-system-object-link').css('width','30%');
    fileSysObj$.find('.file-system-object-attributes').show();
    $('#file-delete').hide();
};
        
FolderManager.prototype.setUpDragAndDrop = function(){
    var that = this;
    $('.draggable-file-system-object').draggable({
        helper: 'clone',
        delay: 200,
        cursor:'move',
        cursorAt:{top:0,left:0},
        revert:'invalid',
        stack:'.draggable-file-system-object',
        start: function(){
            that._moveStart ($(this));
        },
        stop: function(){
            that._moveStop ($(this));
        }   
    });
    $('.droppable-file-system-object').droppable({
        accept:'.draggable-file-system-object',
        activeClass:'x2-active-folder',
        hoverClass:'x2-state-active highlight',
        drop: function(event, ui){
            ui.draggable.hide();
            var type = ui.draggable.find ('.file-system-object').attr('data-type');
            var objId = ui.draggable.find ('.file-system-object').attr('data-id');
            var destId = $(this).find ('.file-system-object').attr('data-id');
            $.ajax({
                url:that.urls.moveFolder,
                data:{type:type, objId:objId, destId:destId},
                error:function(){
                    ui.draggable.show();
                }
            });
        }
    });
}

FolderManager.prototype._init = function () {
    var that = this;
    $(document).on('click','#create-folder-button',function(){
        $('#folder-form').dialog({
            width: '500px',
            buttons: [
                {
                    text: that.translations.createFolder,
                    click: function () {
                        $('#folder-form input[type=\"submit\"]').click ();
                    }
                }
            ]
        });
    });
    $(document).on('click','.file-system-object-folder .folder-link',function(){
        $.fn.yiiGridView.update('folder-contents',{
            url:that.urls.index,
            data:{id:$(this).attr('data-id')},
            complete:function(){
                that.setUpDragAndDrop();
            }
        }); 
        $('#DocFolders_parentFolder').val($(this).attr('data-id')); 
        return false;
    });
    $(document).on('ready',function(){
        that.setUpDragAndDrop();
        $('#delete-drop').droppable({
            accept:'.draggable-file-system-object',
            hoverClass:'highlight',
            tolerance: 'touch',
            drop:function(event, ui){
                ui.draggable.hide();
                var type = ui.draggable.find ('.file-system-object').attr('data-type');
                var id = ui.draggable.find ('.file-system-object').attr('data-id');
                var message = type === 'folder' ?
                    that.translations.deleteFolderConf :
                    that.translations.deleteDocConf;
                if(window.confirm(message)){
                    $.ajax({
                        url:that.urls.deleteFileFolder,
                        method:'POST',
                        data:{YII_CSRF_TOKEN:x2.csrfToken,type:type, id:id},
                        success:function(){
                            x2.flashes.displayFlashes({
                                success:[type === 'folder' ? 
                                   that.translations.folderDeleted :
                                   that.translations.docDeleted]
                            });
                        },
                        error:function(){
                            x2.flashes.displayFlashes({
                                'error':[that.translations.permissionsMissing],
                                });
                            $.fn.yiiGridView.update(
                                'folder-contents', {
                                    complete:function(){ 
                                        that.setUpDragAndDrop(); 
                                    }});
                        }
                    });
                }else{
                    ui.draggable.show();
                    return false;
                }
            }
        });
    });
};


return FolderManager;

}) ();
