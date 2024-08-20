<?php
/* * *********************************************************************************
 * Copyright (C) 2011-2018 X2 Engine Inc. All Rights Reserved.
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
 * ********************************************************************************* */
/**
 * View file for customizing and creating fields.
 *
 * Intended to be rendered partially, via AJAX, in {@link AdminController::actionCreateUpdateField()}
 */
?><div class="page-title rounded-top"><h2><?php echo  Yii::t('admin', "Modify a Custom Mapping")?></h2></div>
<?php echo '<h3 id="createUpdateField-message" style="color:' . ($error ? 'red' : 'green') . '">' . $message . '</h3>'; ?>

<div class="form" id="createUpdateField-container">
    <div style="width:600px">
        <div class="form" id="createUpdateField-container">
            <div style="width:600px">
                <?php
                $form = $this->beginWidget('X2ActiveForm', array(
                ));
                ?>

                <div class="row">
                    <?php
                    $modelNames = X2Model::getModelNames();
                    //ModelFrom
                    echo $form->labelEx($convert, 'createModelFrom');
                    echo $form->genericDropDownList('createModelFrom', $createModelFrom, $modelNames, array(
                        'id' => 'createModelFrom-existing',
                        'empty' => 'select frome',
                    ));
                    //FieldFrom
                    echo $form->labelEx($convert, 'createFieldFrom');
                    echo $form->genericDropDownList('createFieldFrom', $createFieldFrom, $createModelFromFields, array(
                        'id' => 'createFieldFrom-existing',
                        'empty' => 'select field',
                    ));
                    //ModelTo
                    echo $form->labelEx($convert, 'createModelTo');
                    echo $form->genericDropDownList('createModelTo', $createModelTo, $modelNames, array(
                        'id' => 'createModelTo-existing',
                        'empty' => 'select too',
                    ));
                    //FieldTo
                    echo $form->labelEx($convert, 'createFieldTo');
                    echo $form->genericDropDownList('createFieldTo', $createFieldTo, $createModelToFields, array(
                        'id' => 'createFieldTo-existing',
                        'empty' => 'select field',
                    ));
                    ?>
                </div>
                <br>
                <br>
                <div class="row">
                    <?php
                    $modelNames = X2Model::getModelNames();
                    //ModelFrom
                    echo $form->labelEx($convert, 'removeModelFrom');
                    echo $form->genericDropDownList('removeModelFrom', $removeModelFrom, $modelNames, array(
                        'id' => 'removeModelFrom-existing',
                        'empty' => 'select frome',
                    ));
                    //FieldFrom
                    echo $form->labelEx($convert, 'removeFieldFrom');
                    echo $form->genericDropDownList('removeFieldFrom', $removeFieldFrom, $removeModelFromFields, array(
                        'id' => 'removeFieldFrom-existing',
                        'empty' => 'select field',
                    ));
                    
                    //ModelTo
                    echo $form->labelEx($convert, 'removeModelTo');
                    echo $form->genericDropDownList('removeModelTo', $removeModelTo, $modelNames, array(
                        'id' => 'removeModelTo-existing',
                        'empty' => 'select frome',
                    ));
                    //FieldTo
                    echo $form->labelEx($convert, 'removeFieldTo');
                    echo $form->genericDropDownList('removeFieldTo', $removeFieldTo, $removeModelToFields, array(
                        'id' => 'removeFieldTo-existing',
                        'empty' => 'select field',
                    ));
                    ?>
                </div>
                <br>
                <div class="row buttons">
                    <?php
                    echo CHtml::submitButton(Yii::t('app', 'Update'), array(
                        'class' => 'x2-button ' . ($new ? 'new' : 'existing'),
                        'id' => 'createUpdateField-updatebutton'
                    ));
                    ?>
                </div>

            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
