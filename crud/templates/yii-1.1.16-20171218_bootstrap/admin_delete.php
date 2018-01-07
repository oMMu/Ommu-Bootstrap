<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
Yii::import('application.libraries.gii.Inflector');
$inflector = new Inflector;

?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $inflector->pluralize($this->class2name($this->modelClass)); ?> (<?php echo $this->class2id($this->modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 * @var $form CActiveForm
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @link http://opensource.ommu.co
 *
 */

<?php
$label=$inflector->pluralize($this->class2name($this->modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t'Delete',
\t);\n";
?>
?>

<?php echo "<?php ";?>$form=$this->beginWidget('application.libraries.core.components.system.OActiveForm', array(
	'id'=>'<?php echo $this->class2id($this->modelClass);?>-form',
	'enableAjaxValidation'=>true,
	//'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>

	<div class="dialog-content">
		<?php echo "<?php ";?>echo Yii::t('phrase', 'Are you sure you want to delete this item?');?>
	</div>
	<div class="dialog-submit">
		<?php echo "<?php ";?>echo CHtml::submitButton(Yii::t('phrase', 'Delete'), array('onclick' => 'setEnableSave()')); ?>
		<?php echo "<?php ";?>echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
	</div>
	
<?php echo "<?php ";?>$this->endWidget(); ?>