<?php
return array(
	'name'=>"X2sign",
        'install' => array(
            implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
            array('INSERT INTO x2_form_layouts (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES
        (25,"X2SignEnvelopes","Form",\'{"version":"5.2","sections":[{"rows":[{"cols":[{"items":[{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_name","labelType":"left","readOnly":0},{"name":"formItem_updatedBy","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_lastUpdated","labelType":"left","readOnly":0},{"name":"formItem_createDate","labelType":"left","readOnly":0},{"name":"formItem_completeDate","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":false,"title":"information"},{"rows":[{"cols":[{"items":[{"name":"formItem_sender","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_signDocIds","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":false,"title":"Signer Information"},{"rows":[{"cols":[{"items":[{"name":"formItem_status","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_completedDoc","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":true,"collapsedByDefault":false,"title":"Status"}]}\',"0","1","' . time() . '","' . time() . '"),
        (26,"X2SignEnvelopes","View",\'{"version":"5.2","sections":[{"rows":[{"cols":[{"items":[{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_name","labelType":"left","readOnly":0},{"name":"formItem_updatedBy","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_lastUpdated","labelType":"left","readOnly":0},{"name":"formItem_createDate","labelType":"left","readOnly":"0","tabindex":"0"},{"name":"formItem_completeDate","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":false,"title":"Information"},{"rows":[{"cols":[{"items":[{"name":"formItem_sender","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_signDocIds","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":false,"title":"Signer Information"},{"rows":[{"cols":[{"items":[{"name":"formItem_status","labelType":"left","readOnly":0}],"width":"49.82%"},{"items":[{"name":"formItem_completedDoc","labelType":"left","readOnly":0}],"width":"49.82%"}]}],"collapsible":true,"collapsedByDefault":false,"title":"Status"}]}\',"1","0","' . time() . '","' . time() . '")'
            ),
        ),
        'uninstall' => array(implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'))),
        'editable' => true,
        'searchable' => true,
        'adminOnly' => false,
        'custom' => false,
        'toggleable' => false,
	'version' => '3.6',
);
?>
