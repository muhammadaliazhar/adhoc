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

/**
 * @author Justin Toyomitsu <justin@x2engine.com>
 * Currently the FileSysObj is in the Document directory, we want to move this in the future 
 * Feb 10th 2020
 */


x2.x2signReport = (function () { 

    function sendPost(reportType) {
        var url =  yii.scriptUrl + '/x2sign/report' + reportType;
        window.location.href = url;
    }

    var setUpVelocityInfo = function (title) { //set default Inbound Info Box
        var info = $('<div />');
        info.prop('id', 'event_velocity');
        info.prop('title', title);
        return info;
    }
   
    function getVelocity(type, title) {
        var url = yii.scriptUrl + '/x2sign/velocityDialogue' + '?type=' + type; 
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response){
                var message = '';
                var data = JSON.parse(response);
                var info = setUpVelocityInfo(title);
                info.dialog({
                     modal: true,
                     resizable: true,
                     height: 500,
                     width: 800,
                });
                info.html(data.message);
            }
        });
    }

    $(document).on('click','#thisWeek',function(){
       getVelocity('thisWeek', 'This Week');
    });

    $(document).on('click','#lastWeek',function(){
       getVelocity('lastWeek', 'Last Week');
    });
  
    $(document).on('click','#thisMonth',function(){
       getVelocity('thisMonth', 'This Month');
    });

    $(document).on('click','#lastMonth',function(){
       getVelocity('lastMonth', 'Last Month');
    });

    $(document).on('click','#last6Month',function(){
       getVelocity('last6Month', 'Last 6 Months');
    });

    $(document).on('click','#thisYear',function(){
       getVelocity('thisYear', 'This Year');
    });

    $(document).on('click','#row1',function(){
       sendPost('Default'); 
    });

    $(document).on('click','#row2',function(){
       sendPost('Activity');
    });

    $(document).on('click','#row3',function(){
       sendPost('Velocity');
    });
    
    $(document).on('click','#row4',function(){
       sendPost('Completed');
    });

}) ();
