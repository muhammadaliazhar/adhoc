<?php
    Yii::import('application.commands.*');
    use function print_r as printR;

    class DwnldDocxCommand extends CConsoleCommand {
        public function run($args) {
            //const $PUBLIC;

            // Find all docx files that are a standard NDA template
            $docxFiles = Media::model()->findAllByAttributes(array(
                'template' => 'Standard NDA Template_1',
            ), 'name LIKE "%.docx"');
            
	    // Download each file
	    $count = 1;
            foreach($docxFiles as $docx) {
		if(isset($docx->name) && !empty($docx->name)) {
		    $path = $docx->path;
		    copy($path, '/tmp/docxFiles/' . $docx->fileName);
                    echo $count++ . ": " . $docx->id . "\n";
		}
            }

            exit();
        }
    }

?>
