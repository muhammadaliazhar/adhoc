<?php
return array(
	'name'=>"Tmp_denver",
	'install'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
        dirname(__FILE__).'/sqlData.sql',
    ),
	'uninstall'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'))
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>true,
	'toggleable'=>true,
	'version' => '3.6',
);
?>
