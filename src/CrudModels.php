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

    protected $_urls = [];
    protected $_flash = [];

    public function index()
    {
        $query = isset($this->query) ? $this->query : $this->newIndexQuery();
        $filters = isset($this->filters) ? $this->filters : $this->getRequestFilters();
        $query = $this->getModelManager()->filter($query, $filters);

        $this->prepareRecordPaginator();
        $paginator = $this->getRecordPaginator();
        $paginator->paginate($query);

        if (isset($this->items)) {
            $items = $this->items;
        } else {
            $items = $this->getModelManager()->findByQuery($query);
        }

        $this->getView()->set('items', $items);
        $this->getView()->set('filters', $filters);
        $this->getView()->set('title', $this->getModelManager()->getLabel('title'));

        $this->getView()->Paginator()->setPaginator($paginator)->setURL($this->getModelManager()->getURL());
    }

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

        $this->getView()->Breadcrumbs()->addItem($this->getModelManager()->getLabel('add'));
        $this->getView()->TinyMCE()->setEnabled();
        $this->getView()->append('section', '.add');
    }

    /**
     * @return \Nip\Records\AbstractModels\Record
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

    /**
     * @param Record $item
     * @return mixed
     */
    public function addRedirect($item)
    {
        $url = isset($this->_urls["after-add"]) ? $this->_urls['after-add'] : $item->getURL();
        $flashName = isset($this->_flash["after-add"]) ? $this->_flash['after-add'] : $this->getModelManager()->getController();

        return $this->flashRedirect($this->getModelManager()->getMessage('add'), $url, 'success', $flashName);
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

    public function duplicate()
    {
        $record = $this->initExistingItem();

        $record->duplicate();

        $url = $this->getAfterUrl("after-duplicate", $this->getModelManager()->getURL());
        $flashName = $this->getAfterFlashName("after-duplicate", $this->getModelManager()->getController());
        $this->flashRedirect($this->getModelManager()->getMessage('duplicate'), $url, 'success', $flashName);
    }

    public function delete()
    {
        $item = $this->initExistingItem();

        $item->delete();
        $this->deleteRedirect();
    }

    public function activate()
    {
        $record = $this->initExistingItem();

        $record->activate();

        $this->flashRedirect(
            $this->getModelManager()->getMessage('activate'),
            $record->getURL()
        );
    }

    public function deactivate()
    {
        $record = $this->initExistingItem();

        $record->deactivate();

        $this->flashRedirect(
            $this->getModelManager()->getMessage('deactivate'),
            $record->getURL()
        );
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
                $item->Async()->json([
                    "type" => "success",
                    "value" => $item->$field,
                    "message" => $this->getModelManager()->getMessage("update"),
                ]);
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
     * @return Record|HasFilesRecordTrait
     */
    protected function initExistingItem()
    {
        if (!isset($this->item)) {
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
     * @param Record|boolean $item
     */
    protected function viewRedirect($item = null)
    {
        if ($item == null) {
            $item = $this->getModelFromRequest();
            trigger_error('$item needed in viewRedirect', E_USER_DEPRECATED);
        }

        $url = $this->getAfterUrl('after-edit', $item->getURL());
        $flashName = $this->getAfterFlashName("after-edit", $this->getModelManager()->getController());
        $this->flashRedirect($this->getModelManager()->getMessage('update'), $url, 'success', $flashName);
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string
     */
    protected function getAfterUrl($key, $default = null)
    {
        return isset($this->_urls[$key]) && $this->_urls[$key] ? $this->_urls[$key] : $default;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string
     */
    protected function getAfterFlashName($key, $default = null)
    {
        return isset($this->_flash[$key]) && $this->_flash[$key] ? $this->_flash[$key] : $default;
    }

    /**
     * @param bool|Record $item
     */
    protected function setItemBreadcrumbs($item = false)
    {
        $item = $item ? $item : $this->getModelFromRequest();
        $this->getView()->Breadcrumbs()->addItem($item->getName(), $item->getURL());

        $this->getView()->Meta()->prependTitle($item->getName());
    }

    protected function postView()
    {
        $this->setItemBreadcrumbs();
    }

    /**
     * @deprecated Use new processForm($form)
     */
    protected function processView()
    {
        $this->processForm($this->form);
    }

    protected function deleteRedirect()
    {
        $url = $this->getAfterUrl("after-delete", $this->getModelManager()->getURL());
        $flashName = $this->getAfterFlashName("after-delete", $this->getModelManager()->getController());
        $this->flashRedirect($this->getModelManager()->getMessage('delete'), $url, 'success', $flashName);
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
    protected function setClassBreadcrumbs($parent = false)
    {
        $this->getView()->Breadcrumbs()->addItem(
            $this->getModelManager()->getLabel('title'),
            $this->getModelManager()->getURL()
        );
        $this->getView()->Meta()->prependTitle($this->getModelManager()->getLabel('title'));
    }
}
