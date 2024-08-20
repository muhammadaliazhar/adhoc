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
 * @author Justin Toyomitsu <justin@x2engine.com>, Peter Czupil <peter@x2engine.com>
 */

function getProperties(criteria) {
    $.fn.yiiGridView.update(
        'properties-grid',
         {data: {'criteria': criteria}} 
    );
}

function property_link(id) {
    if(id === null){
    url = yii.scriptUrl + '/properties/index';
    }else{
    url = yii.scriptUrl + '/properties/id/' + id;
    }
    window.location.href = url;
}

function property_create () {
    $.ajax({
        url: yii.scriptUrl + '/site/getLayout',
        type: 'GET',
        data: {
            model: 'Properties',
            scenario: 'Wizard',
            form: 1
        },
        success: function (data) {
            let form = $(data);

            // attach agent info widget
            let agentsSection = $('<div class="formSection container"/>');
            let agentsForm = $('#agents');
            agentsForm.css('display', 'flex');
            agentsSection.find('#options');
            form.find('#form-view').append(agentsSection.append(agentsForm));

            //attach file uploader widget
            let picturesSection = $('<div class="formSection container"/>');
            let picturesHeader = $('<div class="formSectionHeader"><span class="sectionTitle" title="Main Property Image">Main Property Image</span></div>');
            picturesSection.append(picturesHeader);
            let picturesWidget = $('#pictures');
            picturesWidget.find('#options').css('display', 'none');
            form.find('#form-view').append(picturesSection.append(picturesWidget));

            //setup dropzone
            let uploader = x2.FileUploader.list['pictures'];
            uploader.dropzone.options.autoProcessQueue = false;
            uploader.dropzone.options.parallelUploads = 1; //one at a time
            uploader.dropzone.removeAllFiles();
            uploader.toggle();

            // CSS form changes
            form.find('.formSection').each(function () {
                $(this).addClass('container');
            });
            form.find('.label').each(function () {
                $(this).css('width', 'auto');
            });

            //hide all but first section
            let sections = form.find('.formSection')
                .removeClass('active')
                .css({display:'none', 'border-top':'none'});
            sections.first().addClass('active').css('display', 'block');

            //create dialog
            form.dialog({
                title: 'Create Property',
                modal: true,
                width: '50%',
                height: 'auto'
            });
            let dialog = form.dialog('widget');

            //adjust height when widget resizes
            uploader.dropzone.on('thumbnail', function () {
                dialog.css('height', 'auto');
            });
            
            //progress bar and buttons
            let prog = $('<progress/>').attr({value:1, max:sections.length}).css('width','100%');
            form.prepend(prog);
            form.find('.formSectionHide').remove();
            let saveBtn = form.find('#save-button').css({float: 'right', display: 'none'});
            let backBtn = $('<input/>').addClass('x2-button').attr('type','button').css('float','left');
            let nextBtn = backBtn.clone();
            backBtn.insertBefore(saveBtn).val('Back');
            nextBtn.insertBefore(saveBtn).val('Next');

            //google maps autocomplete
            autoInput = new google.maps.places.Autocomplete($('#Properties_street')[0]);
            // init autocomplete
            autoInput.setFields(['address_component']);
            autoInput.addListener('place_changed', fillInAddress);

            function fillInAddress() {
                var place = autoInput.getPlace();
                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];
                    if (addressType == 'street_number') $('#Properties_street').val(place.address_components[i].long_name);
                    if (addressType == 'route') $('#Properties_street').val($('#Properties_street').val() + ' ' + place.address_components[i].long_name);
                    if (addressType == 'locality') $('#Properties_city').val(place.address_components[i].long_name);
                    if (addressType == 'administrative_area_level_1') $('#Properties_state').val(place.address_components[i].short_name);
                    if (addressType == 'postal_code') $('#Properties_zipcode').val(place.address_components[i].short_name);
                }
            }

            //navigation function
            function proceed(forward=true) {
                let active = dialog.find('.active');
                let target = forward ? active.next() : active.prev();
                let border = forward ? sections.last() : sections.first();
                if (!border.is(active)) {
                    h1 = dialog.height();
                    active.removeClass('active').css('display', 'none');
                    target.addClass('active').fadeIn();
                    dialog.css('height','auto');
                    h2 = dialog.height();
                    dialog.height(h1);
                    dialog.animate({height:h2});
                    v1 = prog.attr('value');
                    v2 = v1 + (forward ? 1 : -1);
                    prog.animate({value:v2});
                    let lastPage = forward && border.is(target);
                    if (lastPage) saveBtn.fadeIn(); else saveBtn.fadeOut();
                    target.find('input').first().focus();
                }
            }
            
            //button listeners
            backBtn.click(function (e) {
                proceed(false);
            });
            nextBtn.click(function (e) {
                proceed(true);
            });
            saveBtn.click(function (e) {
                let active = dialog.find('.active');
                if (!sections.last().is(active)) {
                    e.preventDefault();
                }
            });

            //create property and upload media
            form.submit(function (e) {
                e.preventDefault();   
                let data = form.serializeArray();
                data.push({name:'x2ajax', value:1});
                $.ajax({
                    url: yii.scriptUrl + '/properties/create',
                    type: 'POST',
                    data: data,
                    success: function (propId) {
                        let cleanUp = function () {
                            $('#pictures-wrapper').append(picturesWidget.css('display','none'));
                            dialog.remove();
                            $.fn.yiiGridView.update('properties-grid');
                        }
                        if (uploader.dropzone.files.length > 0) {
                            uploader.mediaParams.associationId = propId;
                            uploader.dropzone.on("complete", uploader.dropzone.processQueue);
                            uploader.dropzone.on("queuecomplete", cleanUp);
                            uploader.dropzone.processQueue();
                        } else {
                            cleanUp();
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseText);
                    }
                });
            });
        }
    });
};
