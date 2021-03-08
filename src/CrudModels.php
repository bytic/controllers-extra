<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Common\Records\Traits\Media\Files\RecordTrait as HasFilesRecordTrait;
use Nip\Records\AbstractModels\Record;
use Nip\Records\AbstractModels\RecordManager;
use Nip\Request;
use Nip\View;
use Nip_Form_Model as Form;

/**
 * Class CrudModels
 * @package ByTIC\Controllers\Behaviors
 *
 * @method string getModel()
 * @method RecordManager getModelManager()
 * @method View getView()
 * @method Request getRequest()
 * @method Form getModelForm($model, $action = null)
 * @method Record getModelFromRequest($key = false)
 * @method string flashRedirect($message, $url, $type = 'success', $name = false)
 */
trait CrudModels
{
    use HasRecordPaginator;
    use Models\HasModelLister;
    use Models\HasAfterActions;

    /**
     * @deprecated variable
     * @var null
     */
    protected $query = null;

    /**
     * @deprecated variable
     * @var null
     */
    protected $filters = null;

    /**
     * @deprecated variable
     * @var null
     */
    protected $items = null;

    /**
     * @deprecated variable
     * @var null
     */
    protected $item = null;

    /**
     * @deprecated variable
     * @var null
     */
    protected $form = null;

    /**
     * Model index action / listing
     *
     * @return void
     */
    public function index()
    {
        $this->doModelsListing();
    }

    /**
     * Add item method
     *
     * @return void
     */
    public function add()
    {
        $record = $this->addNewModel();
        $form = $this->addGetForm($record);

        if ($form->execute()) {
            $this->addRedirect($record);
        }

        $this->getView()->set('item', $record);
        $this->getView()->set('form', $form);
        $this->getView()->set('title', $this->getModelManager()->getLabel('add'));

        $this->getView()->Breadcrumbs()
            ->addItem($this->getModelManager()->getLabel('add'));

        $this->getView()->TinyMCE()->setEnabled();
        $this->getView()->append('section', '.add');
    }

    /**
     * @return \ByTIC\Common\Records\Record
     */
    public function addNewModel()
    {
        $item = isset($this->item) ? $this->item : $this->newModel();

        return $item;
    }

    /**
     * @return \Nip\Records\AbstractModels\Record
     */
    public function newModel()
    {
        return $this->getModelManager()->getNew();
    }

    /**
     * @param Record $item
     * @return mixed
     */
    public function addGetForm($item)
    {
        if (isset($this->form)) {
            $form = $this->form;
        } else {
            $form = $this->getModelForm($item);
            $form->setAction($this->getModelManager()->compileURL('add', $_GET));
        }

        return $form;
    }

    public function view()
    {
        $record = $this->initExistingItem();

        $clone = clone $record;
        $form = $this->getModelForm($clone);

        $this->processForm($form);

        $this->getView()->set('item', $record);
        $this->getView()->set('clone', $clone);
        $this->getView()->set('form', $form);
        $this->getView()->set('title', $record->getName());

        $this->getView()->append('section', ".view");
        $this->getView()->TinyMCE()->setEnabled();

        $this->setItemBreadcrumbs();
        $this->postView();
    }

    /**
     * @return Record|HasFilesRecordTrait
     */
    protected function initExistingItem()
    {
        if (!$this->item) {
            $this->item = $this->getModelFromRequest();
        }

        return $this->item;
    }

    /**
     * @param Form $form
     */
    protected function processForm($form)
    {
        if ($form->execute()) {
            $this->viewRedirect($form->getModel());
        }
    }

    /**
     * @param bool|Record $item
     */
    public function setItemBreadcrumbs($item = false)
    {
        $item = $item ? $item : $this->getModelFromRequest();
        $this->getView()->Breadcrumbs()->addItem($item->getName(), $item->getURL());

        $this->getView()->Meta()->prependTitle($item->getName());
    }

    protected function postView()
    {
        $this->setItemBreadcrumbs();
    }

    public function edit()
    {
        $record = $this->initExistingItem();

        $clone = clone $record;
        $form = $this->getModelForm($clone);

        $this->processForm($form);

        $this->getView()->set('item', $record);
        $this->getView()->set('clone', $clone);
        $this->getView()->set('form', $form);
        $this->getView()->set('title', $record->getName());

        $this->getView()->append('section', ".edit");
        $this->getView()->TinyMCE()->setEnabled();

        $this->setItemBreadcrumbs();
    }

    /**
     * Duplicate item action
     *
     * @return void
     */
    public function duplicate()
    {
        $item = $this->initExistingItem();

        $item->duplicate();
        $this->duplicateRedirect($item);
    }

    public function delete()
    {
        $item = $this->initExistingItem();

        $item->delete();
        $this->deleteRedirect($item);
    }

    public function activate()
    {
        $item = $this->initExistingItem();

        $item->activate();
        $this->afterActionRedirect('activate', $item);
    }

    public function deactivate()
    {
        $item = $this->initExistingItem();

        $item->deactivate();
        $this->afterActionRedirect('deactivate', $item);
    }

    public function inplace()
    {
        $item = $this->initExistingItem();

        $pk = $this->getModelManager()->getPrimaryKey();

        foreach ($this->getModelManager()->getFields() as $key) {
            if ($key != $pk && $_POST[$key]) {
                $field = $key;
            }
        }

        if ($field) {
            $item->getFromRequest($_POST, [$field]);
            if ($item->validate()) {
                $item->save();
                $item->Async()->json(
                    [
                        "type" => "success",
                        "value" => $item->$field,
                        "message" => $this->getModelManager()->getMessage("update"),
                    ]
                );
            }
        }

        $this->Async()->json(["type" => "error"]);
    }

    public function uploadFile()
    {
        $record = $this->initExistingItem();

        $file = $record->uploadFile($_FILES['Filedata']);

        if ($file) {
            $response['type'] = "success";
            $response['url'] = $record->getFileURL($file);
            $response['name'] = $file->getName();
            $response['extension'] = $file->getExtension();
            $response['size'] = \Nip_File_System::instance()->formatSize($file->getSize());
            $response['time'] = date("d.m.Y H:i", $file->getTime());
        } else {
            $response['type'] = 'error';
        }

        $this->Async()->json($response);
    }

    /**
     * @return \Nip\Database\Query\Select
     */
    protected function newIndexQuery()
    {
        return $this->getModelManager()->paramsToQuery();
    }

    /**
     * @deprecated Use new processForm($form)
     */
    protected function processView()
    {
        $this->processForm($this->form);
    }

    protected function beforeAction()
    {
        parent::beforeAction();
        $this->getView()->set('section', inflector()->underscore($this->getModel()));
    }

    protected function afterAction()
    {
        if (!$this->getView()->has('modelManager')) {
            $this->getView()->set('modelManager', $this->getModelManager());
        }
        parent::afterAction();
    }

    /**
     * @param bool $parent
     */
    public function setClassBreadcrumbs($parent = false)
    {
        $this->getView()->Breadcrumbs()->addItem(
            $this->getModelManager()->getLabel('title'),
            $this->getModelManager()->getURL()
        );
        $this->getView()->Meta()->prependTitle($this->getModelManager()->getLabel('title'));
    }
}
