<?php
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






$noBackdrop = isset($this->noBackdrop) ? $this->noBackdrop : false;

$this->beginContent('//layouts/main');
$themeURL = Yii::app()->theme->getBaseUrl();

Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
    'dmFyIF8weDZjNzM9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
    .'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
    .'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZEXHg2Rlx4NjJceDY5XHg2Q1x4NjUiL'
    .'CJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiLCJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzM'
    .'Vx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4NjJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzO'
    .'Fx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4MzZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzM'
    .'Vx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4NjVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzM'
    .'lx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4NjRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzI'
    .'iwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0FceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2O'
    .'Vx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2NVx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4Njlce'
    .'DZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNceDczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiX'
    .'Hg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGXHg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4N'
    .'zRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4NkZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDY4X'
    .'Hg3Mlx4NjVceDY2IiwiXHg3Mlx4NjVceDZEXHg2Rlx4NzZceDY1XHg0MVx4NzRceDc0XHg3MiIsIlx4N'
    .'jEiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3MFx4NzVceDc0XHgyMFx4NzRceDY4XHg2N'
    .'Vx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M1x4NkJceDJFIiwiXHg2Rlx4NkUiXTtpZ'
    .'ihfMHg2YzczWzBdIT09IHR5cGVvZiBqUXVlcnkmJl8weDZjNzNbMF0hPT0gdHlwZW9mIFNIQTI1Nil7J'
    .'Ch3aW5kb3cpW18weDZjNzNbMjJdXShfMHg2YzczWzFdLGZ1bmN0aW9uKCl7dmFyIF8weDZlYjh4MT0kK'
    .'F8weDZjNzNbMl0pOyRbXzB4NmM3M1szXV18fF8weDZlYjh4MVtfMHg2YzczWzRdXSYmXzB4NmM3M1s1X'
    .'T09U0hBMjU2KF8weDZlYjh4MVtfMHg2YzczWzddXShfMHg2YzczWzZdKSkmJl8weDZlYjh4MVtfMHg2Y'
    .'zczWzldXShfMHg2YzczWzhdKSYmXzB4NmM3M1sxMF0hPV8weDZlYjh4MVtfMHg2YzczWzEyXV0oXzB4N'
    .'mM3M1sxMV0pJiYwIT1fMHg2ZWI4eDFbXzB4NmM3M1sxM11dKCkmJjAhPV8weDZlYjh4MVtfMHg2YzczW'
    .'zE0XV0oKSYmMT09XzB4NmViOHgxW18weDZjNzNbMTJdXShfMHg2YzczWzE1XSkmJl8weDZjNzNbMTZdP'
    .'T1fMHg2ZWI4eDFbXzB4NmM3M1sxMl1dKF8weDZjNzNbMTddKXx8KCQoXzB4NmM3M1syMF0pW18weDZjN'
    .'zNbMTldXShfMHg2YzczWzE4XSksYWxlcnQoXzB4NmM3M1syMV0pKTt9KX07Cg=='));

?>
<div id="content">
	<?php echo $content; ?>
</div>
<?php $this->endContent();
