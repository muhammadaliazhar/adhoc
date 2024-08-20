<?php
Yii::import('application.commands.*');
use function print_r as printR;

/**
 * A test area for executing experimental PHP code inside of a Yii run environment.
 *
 * @package application.commands
 */
class ChangeRequiredSignaturesCommand extends CConsoleCommand {
	public function run($args) {
        $signDocs = X2SignDocs::model()->findAll();
        
        // Loop through each sign doc
        foreach($signDocs as &$signDoc) {
            $fields = json_decode($signDoc->fieldInfo);
            if($fields == NULL || $fields == '')
                continue;
            else if(is_object($fields))
                $fields = array($fields);

            // Loop through each field for the current sign doc
            foreach($fields as &$field)
                if(strpos($field->{'id'}, "Signature") !== false)
                    $field->req = 1;

            $signDoc->fieldInfo = json_encode($fields);
            if($signDoc->save())
                printR("Fixed sign doc id: " . $signDoc->id);
            else
                printR("Error saving sign doc id: " . $signDoc->id . "; " . CJSON::encode($signDoc->getErrors()));
            printR("\n");
        }

        return;
    }
}

?>