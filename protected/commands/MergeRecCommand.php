<?php
class MergeRecCommand extends CConsoleCommand
{
    public function run($args)
    {
        echo "test";
        $status = 0;
       // $alphas = array_merge(range('A', 'Z'), range('0', '9'));
       // foreach($alphas as $start){
                $status = Contacts::model()->checkShareLog();
       // }
        echo "done";
        return $status;
    }
 }
?>
