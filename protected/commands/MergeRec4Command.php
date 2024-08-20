<?php
class MergeRec4Command extends CConsoleCommand
{
    public function run($args)
    {
        echo "test";
        $status = 0;
        $alphas = array_merge(range('J', 'Z'), range('0', '9'));
        foreach($alphas as $start){
                $status = Contacts::model()->DeDupBuyers($start);
        }
        echo "done";
        return $status;
    }
 }
?>
