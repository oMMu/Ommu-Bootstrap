<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

$tableSchema = $generator->tableSchema;
$primaryKey = $generator->getPrimaryKey($tableSchema);

$label = ucwords($generator->modelLabel($modelClass));
$shortLabel = ucwords($generator->shortLabel($modelClass));

$attributeName =  key($generator->getNameAttributes($tableSchema));
$relationAttributeName = $generator->getNameAttribute();

$primaryKeyTriggerCondition = 0;

$primaryKeyColumn = $tableSchema->columns[$primaryKey];
if($primaryKeyColumn->comment == 'trigger')
	$primaryKeyTriggerCondition = 1;

$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$arrayRelation = [];
$i=0;
foreach($foreignKeys as $key => $val) {
	$arrayRelation[$i]['relation'] = $generator->setRelation($key);
	$arrayRelation[$i]['table'] = $val;
	if($val == 'ommu_users')
		$namespace = 'app\models\Users';
	else if($val == 'ommu_members')
		$namespace = 'ommu\member\models\Members';
	else {
		$module = $tableSchema->columns[$key]->comment;
		if($module)
			$namespace = $generator->getUseModel($module, $generator->generateClassName($val));
		else
			$namespace = str_replace($modelClass, $generator->generateClassName($val), $generator->modelClass);
	}
	$arrayRelation[$i]['namespace'] = $namespace;
	$i++;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo $controllerClass."\n"; ?>
 * @var $this <?php echo ltrim($generator->controllerClass, '\\')."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 *
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 * Reference start
 * TOC :
 *  Index
 *  Manage
 *  Create
 *  Update
 *  View
 *  Delete
<?php if(!$primaryKeyTriggerCondition) {
if(array_key_exists('publish', $tableSchema->columns)): ?>
 *  RunAction
<?php endif;
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, ['publish','headline'])):
		$actionName = Inflector::id2camel($column->name, '_');
		echo " *  $actionName\n";
	endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if($column->name[0] == '_')
		continue;
	if(in_array($column->name, ['publish','headline']))
		continue;
		
	if($column->dbType == 'tinyint(1)' && $column->comment != ''):
		$actionName = Inflector::id2camel($column->name, '_');
		echo " *  $actionName\n";
	endif;
endforeach;
}?>
 *
 *  findModel
 *
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php endif; ?>
 * @link <?php echo $generator->link."\n";?>
 *
 */

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
<?php if($generator->attachRBACFilter): ?>
use mdm\admin\components\AccessControl;
<?php endif; ?>
use yii\filters\VerbFilter;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif;?>

class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
        return [
<?php if($generator->attachRBACFilter): ?>
            'access' => [
                'class' => AccessControl::className(),
            ],
<?php endif; ?>
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
<?php if(!$primaryKeyTriggerCondition) {
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, ['publish','headline'])):
		$actionName = Inflector::camel2id($column->name);
		echo "\t\t\t\t\t'$actionName' => ['POST'],\n";
	endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if($column->name[0] == '_')
		continue;
	if(in_array($column->name, ['publish','headline']))
		continue;
		
	if($column->dbType == 'tinyint(1)' && $column->comment != ''):
		$actionName = Inflector::camel2id($column->name);
		echo "\t\t\t\t\t'$actionName' => ['POST'],\n";
	endif;
endforeach;
}?>
                ],
            ],
        ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function actionIndex()
	{
        return $this->redirect(['manage']);
	}

	/**
	 * Lists all <?= $modelClass ?> models.
	 * @return mixed
	 */
	public function actionManage()
	{
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $gridColumn = Yii::$app->request->get('GridColumn', null);
        $cols = [];
        if ($gridColumn != null && count($gridColumn) > 0) {
            foreach ($gridColumn as $key => $val) {
                if ($gridColumn[$key] == 1) {
                    $cols[] = $key;
                }
            }
        }
        $columns = $searchModel->getGridColumn($cols);
<?php if(!empty($arrayRelation)) {
	echo "\n";
	foreach($arrayRelation as $key => $val) {?>
        if (($<?php echo $arrayRelation[$key]['relation'];?> = Yii::$app->request->get('<?php echo $arrayRelation[$key]['relation'];?>')) != null) {
            $<?php echo $arrayRelation[$key]['relation'];?> = <?php echo '\\'.ltrim($arrayRelation[$key]['namespace'], '\\');?>::findOne($<?php echo $arrayRelation[$key]['relation'];?>);
        }
<?php }
}?>

		$this->view->title = <?php echo $generator->generateString(Inflector::pluralize($shortLabel));?>;
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('admin_manage', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
			'columns' => $columns,
<?php if(!empty($arrayRelation)) {
	foreach($arrayRelation as $key => $val) {?>
			'<?php echo $arrayRelation[$key]['relation'];?>' => $<?php echo $arrayRelation[$key]['relation'];?>,
<?php }
}?>
		]);
<?php else: ?>
		$dataProvider = new ActiveDataProvider([
			'query' => <?= $modelClass ?>::find(),
		]);

		$this->view->title = <?php echo $generator->generateString(Inflector::pluralize($shortLabel));?>;
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('admin_manage', [
			'dataProvider' => $dataProvider,
		]);
<?php endif; ?>
	}

	/**
	 * Creates a new <?= $modelClass ?> model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
        $model = new <?= $modelClass ?>();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            // $postData = Yii::$app->request->post();
            // $model->load($postData);
            // $model->order = $postData['order'] ? $postData['order'] : 0;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success created.');?>);
                return $this->redirect(['manage']);
                //return $this->redirect(['view', <?= $urlParams ?>]);

            } else {
                if (Yii::$app->request->isAjax) {
                    return \yii\helpers\Json::encode(\app\components\widgets\ActiveForm::validate($model));
                }
            }
        }

		$this->view->title = <?php echo $generator->generateString('Create '.$shortLabel);?>;
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('admin_create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing <?= $modelClass ?> model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionUpdate(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            // $postData = Yii::$app->request->post();
            // $model->load($postData);
            // $model->order = $postData['order'] ? $postData['order'] : 0;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success updated.');?>);
                return $this->redirect(['manage']);

            } else {
                if (Yii::$app->request->isAjax) {
                    return \yii\helpers\Json::encode(\app\components\widgets\ActiveForm::validate($model));
                }
            }
        }

<?php if($generator->enableI18N) {
	$pageTitle[Inflector::camel2id($attributeName)] = "\$model->$relationAttributeName";
?>
		$this->view->title = <?php echo $generator->generateString('Update '.$shortLabel.': '.Inflector::camel2id('{'.$attributeName.'}').'', $pageTitle);?>;
<?php } else {?>
		$this->view->title = <?= $generator->generateString('Update '.$shortLabel.': ', [Inflector::camel2id('modelClass') => $label]) ?>.$model-><?= $relationAttributeName ?>;
<?php }?>
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->render('admin_update', [
			'model' => $model,
		]);
	}

	/**
	 * Displays a single <?= $modelClass ?> model.
	 * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionView(<?= $actionParams ?>)
	{
        $model = $this->findModel(<?= $actionParams ?>);

<?php if($generator->enableI18N) {
	$pageTitle[Inflector::camel2id($attributeName)] = "\$model->$relationAttributeName";
?>
		$this->view->title = <?php echo $generator->generateString('Detail '.$shortLabel.': '.Inflector::camel2id('{'.$attributeName.'}').'', $pageTitle);?>;
<?php } else {?>
		$this->view->title = <?= $generator->generateString('Detail '.$shortLabel.': ', [Inflector::camel2id('modelClass') => $label]) ?>.$model-><?= $relationAttributeName; ?>;
<?php }?>
		$this->view->description = '';
		$this->view->keywords = '';
		return $this->oRender('admin_view', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing <?= $modelClass ?> model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionDelete(<?= $actionParams ?>)
	{
<?php if(array_key_exists('publish', $tableSchema->columns)): ?>
		$model = $this->findModel(<?= $actionParams ?>);
		$model->publish = 2;

        if ($model->save(false, ['publish'<?php echo array_key_exists('modified_id', $tableSchema->columns) ? ',\'modified_id\'' : ''?>])) {
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success deleted.');?>);
            return $this->redirect(Yii::$app->request->referrer ?: ['manage']);
        }
<?php else: ?>
		$model = $this->findModel($id);
		$model->delete();

		Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success deleted.');?>);
		return $this->redirect(Yii::$app->request->referrer ?: ['manage']);
<?php endif; ?>
	}
<?php 
if(!$primaryKeyTriggerCondition) {
foreach ($tableSchema->columns as $column): 
	if(in_array($column->name, ['publish','headline'])):
		$actionName = Inflector::id2camel($column->name, '_');?>

	/**
	 * action<?php echo $actionName;?> an existing <?= $modelClass ?> model.
	 * If <?php echo Inflector::camel2id($column->name);?> is successful, the browser will be redirected to the 'index' page.
	 * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function action<?php echo $actionName;?>(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);
<?php if($column->name == 'headline'):?>
		$model-><?php echo $column->name;?> = 1;
		$model->publish  = 1;
<?php else:?>
		$replace = $model-><?php echo $column->name;?> == 1 ? 0 : 1;
		$model-><?php echo $column->name;?> = $replace;
<?php endif;?>

<?php if($column->name == 'headline'):?>
        if ($model->save(false, ['publish','<?php echo $column->name;?>'<?php echo array_key_exists('modified_id', $tableSchema->columns) ? ',\'modified_id\'' : ''?>])) {
<?php else:?>
        if ($model->save(false, ['<?php echo $column->name;?>'<?php echo array_key_exists('modified_id', $tableSchema->columns) ? ',\'modified_id\'' : ''?>])) {
<?php endif;?>
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success updated.');?>);
            return $this->redirect(Yii::$app->request->referrer ?: ['manage']);
        }
	}
<?php endif;
endforeach;
foreach ($tableSchema->columns as $column): 
	if($column->name[0] == '_')
		continue;
	if(in_array($column->name, ['publish','headline']))
		continue;
		
	if($column->dbType == 'tinyint(1)' && $column->comment != '' && $column->comment[7] != '[' && $column->comment[0] != '"'):
		$actionName = Inflector::id2camel($column->name, '_');?>

	/**
	 * action<?php echo $actionName;?> an existing <?= $modelClass ?> model.
	 * If <?php echo Inflector::camel2id($column->name);?> is successful, the browser will be redirected to the 'index' page.
	 * <?= implode("\n   * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function action<?php echo $actionName;?>(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);
		$replace = $model-><?php echo $column->name;?> == 1 ? 0 : 1;
		$model-><?php echo $column->name;?> = $replace;
		
        if ($model->save(false, ['<?php echo $column->name;?>'<?php echo array_key_exists('modified_id', $tableSchema->columns) ? ',\'modified_id\'' : ''?>])) {
            Yii::$app->session->setFlash('success', <?php echo $generator->generateString(Inflector::titleize($label).' success updated.');?>);
            return $this->redirect(Yii::$app->request->referrer ?: ['manage']);
        }
	}
<?php endif;
endforeach;
}?>

	/**
	 * Finds the <?= $modelClass ?> model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * <?= implode("\n	 * ", $actionParamComments) . "\n" ?>
	 * @return <?= $modelClass ?> the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel(<?= $actionParams ?>)
	{
<?php
if (count($pks) === 1) {
	$condition = '$id';
} else {
	$condition = [];
	foreach ($pks as $pk) {
		$condition[] = "'$pk' => \$$pk";
	}
	$condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }

		throw new \yii\web\NotFoundHttpException(<?= $generator->generateString('The requested page does not exist.') ?>);
	}
}