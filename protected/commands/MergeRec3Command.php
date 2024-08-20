<?php
class MergeRec3Command extends CConsoleCommand
{
    public function run($args)
    {
        echo "test";
        $status = 0;
        $alphas = array_merge(range('R', 'Z'), range('0', '9'));
        foreach($alphas as $start){
                $status = Contacts::model()->DeDupBuyers($start);
        }
        echo "done";
        return $status;
    }
 }
?>
