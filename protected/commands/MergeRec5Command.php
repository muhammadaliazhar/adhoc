<?php
class MergeRec5Command extends CConsoleCommand
{
    public function run($args)
    {
        echo "test";
        $status = 0;
        $alphas = array_merge(range('W', 'Z'), range('0', '9'));
        foreach($alphas as $start){
                $status = Contacts::model()->DeDupBuyers($start);
        }
        echo "done";
        return $status;
    }
 }
?>
