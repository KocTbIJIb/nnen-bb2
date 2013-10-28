<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/styles.css" />

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

    <?php Yii::app()->bootstrap->register(); ?>
</head>

<body>

<?php $this->widget('bootstrap.widgets.TbNavbar',array(
    'items'=>array(
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'items'=>array(
                array('label'=>'Home', 'url'=>array('/index/index')),
                array('label'=>'Domains', 'url'=>array('/personal/domains'), 'active'=>$this->id=='personal/domains', 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Templates', 'url'=>array('/personal/templates'), 'active'=>$this->id=='personal/templates', 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/auth/logout'), 'visible'=>!Yii::app()->user->isGuest)
            ),
        ),
    ),
    'collapse' => true
)); 
?>

<div class="container" id="page">

    <?php if(isset($this->breadcrumbs)):?>
        <?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
            'links'=>$this->breadcrumbs,
        )); ?><!-- breadcrumbs -->
    <?php endif?>

    <?php
    //Use bootstrap.widgets.TbAlert here!!!
    if ($messages = Yii::app()->user->getFlashes()) {
    ?>
    <!-- Flash messages -->
        <div class="row" id="flash-wrapper">
            <div class="span12">
    <?php
        foreach ($messages as $key => $message) {
            if (!is_array($message)) {
                $message = array($message);
            }
            foreach ($message as $messageText) {
    ?>
            <div class="alert alert-<?php echo preg_replace('/[0-9\. ]/', '', $key) ?>">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo $messageText ?>
            </div>
    <?php
            }
        }
    ?>
            </div>
        </div>
    <?php
    }
    ?>

    <?php echo $content; ?>

    <div class="clear"></div>

    <div id="footer">
    </div><!-- footer -->

</div><!-- page -->

</body>
</html>
