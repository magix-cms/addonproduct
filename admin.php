<?php
class plugins_addonproduct_admin extends plugins_addonproduct_db
{
    public $edit, $action, $tabs, $search, $plugin, $controller;
    protected $message, $template, $header, $data, $modelLanguage, $collectionLanguage, $order, $upload, $config, $modelPlugins, $routingUrl, $makeFiles, $finder, $plugins, $progress;
    public $id_adp, $content, $pages, $iso, $ajax, $tableaction, $tableform, $offset, $addonData;

    public $tableconfig = array(
        'all' => array(
            'id_adp',
            'name_adp' => array('title' => 'name'),
            'price_adp' => array('type' => 'price','input' => null),
            'date_register'
        )
    );
    /**
     * frontend_controller_home constructor.
     */
    public function __construct($t = null){
        $this->template = $t ? $t : new backend_model_template;
        $this->message = new component_core_message($this->template);
        $this->header = new http_header();
        $this->data = new backend_model_data($this);
        $formClean = new form_inputEscape();
        $this->modelLanguage = new backend_model_language($this->template);
        $this->collectionLanguage = new component_collections_language();
        $this->modelPlugins = new backend_model_plugins();
        $this->routingUrl = new component_routing_url();
        $this->finder = new file_finder();
        // --- GET
        if(http_request::isGet('controller')) $this->controller = $formClean->simpleClean($_GET['controller']);
        if (http_request::isGet('edit')) $this->edit = $formClean->numeric($_GET['edit']);
        if (http_request::isGet('action')) $this->action = $formClean->simpleClean($_GET['action']);
        elseif (http_request::isPost('action')) $this->action = $formClean->simpleClean($_POST['action']);
        if (http_request::isGet('tabs')) $this->tabs = $formClean->simpleClean($_GET['tabs']);
        if (http_request::isGet('ajax')) $this->ajax = $formClean->simpleClean($_GET['ajax']);
        if (http_request::isGet('offset')) $this->offset = intval($formClean->simpleClean($_GET['offset']));

        if (http_request::isGet('tableaction')) {
            $this->tableaction = $formClean->simpleClean($_GET['tableaction']);
            $this->tableform = new backend_controller_tableform($this,$this->template);
        }

        // --- Search
        if (http_request::isGet('search')) {
            $this->search = $formClean->arrayClean($_GET['search']);
            $this->search = array_filter($this->search, function ($value) { return $value !== ''; });
        }

        // --- ADD or EDIT
        if (http_request::isGet('id')) $this->id_adp = $formClean->simpleClean($_GET['id']);
        elseif (http_request::isPost('id')) $this->id_adp = $formClean->simpleClean($_POST['id']);
        if (http_request::isPost('content')) {
            $array = $_POST['content'];
            foreach($array as $key => $arr) {
                foreach($arr as $k => $v) {
                    $array[$key][$k] = ($k == 'content_lead') ? $formClean->cleanQuote($v) : $formClean->simpleClean($v);
                }
            }
            $this->content = $array;
        }
        if (http_request::isPost('addonData')) $this->addonData = $formClean->arrayClean($_POST['addonData']);
        // --- Recursive Actions
        if (http_request::isGet('addonproduct'))  $this->pages = $formClean->arrayClean($_GET['addonproduct']);
        # ORDER PAGE
        if (http_request::isPost('addonproduct')) $this->order = $formClean->arrayClean($_POST['addonproduct']);
        if (http_request::isGet('plugin')) $this->plugin = $formClean->simpleClean($_GET['plugin']);

        # JSON LINK (TinyMCE)
        //if (http_request::isGet('iso')) $this->iso = $formClean->simpleClean($_GET['iso']);
    }
    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string $context
     * @param boolean $assign
     * @param boolean $pagination
     * @return mixed
     */
    private function getItems($type, $id = null, $context = null, $assign = true, $pagination = false) {
        return $this->data->getItems($type, $id, $context, $assign, $pagination);
    }
    /**
     * Method to override the name of the plugin in the admin menu
     * @return string
     */
    public function getExtensionName()
    {
        return $this->template->getConfigVars('addonproduct_plugin');
    }
    /**
     * @param $ajax
     * @return mixed
     * @throws Exception
     */
    public function tableSearch($ajax = false)
    {
        $this->modelLanguage->getLanguage();
        $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
        $results = $this->getItems('pages', array('default_lang' => $defaultLanguage['id_lang']), 'all',true,true);
        $params = array();

        if($ajax) {
            $params['section'] = 'pages';
            $params['idcolumn'] = 'id_adp';
            $params['activation'] = false;
            $params['sortable'] = true;
            $params['checkbox'] = true;
            $params['edit'] = true;
            $params['dlt'] = true;
            $params['readonly'] = array();
            $params['cClass'] = 'plugins_addonproduct_admin';
        }

        $this->data->getScheme(array('mc_addonproduct','mc_addonproduct_content'),array('id_adp','name_adp','price_tr','date_register'),$this->tableconfig['all']);

        return array(
            'data' => $results,
            'var' => 'pages',
            'tpl' => 'index.tpl',
            'params' => $params
        );
    }

    /**
     * Update data
     * @param $data
     * @throws Exception
     */
    private function add($data)
    {
        switch ($data['type']) {
            case 'page':
            case 'contentPage':
                parent::insert(
                    array(
                        'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }

    /**
     * Mise a jour des données
     * @param $data
     * @throws Exception
     */
    private function upd($data)
    {
        switch ($data['type']) {
            /*case 'order':
                $p = $this->order;
                for ($i = 0; $i < count($p); $i++) {
                    parent::update(
                        array(
                            'type'=>$data['type']
                        ),array(
                            'id_cs'       => $p[$i],
                            'order_cs'    => $i + (isset($this->offset) ? ($this->offset + 1) : 0)
                        )
                    );
                }
                break;*/
            case 'page':
            case 'contentPage':
                parent::update(
                    array(
                        'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }
    /**
     * Insertion de données
     * @param $data
     * @throws Exception
     */
    private function del($data)
    {
        switch($data['type']){
            case 'delPages':
                parent::delete(
                    array(
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                $this->message->json_post_response(true,'delete',$data['data']);
                break;
        }
    }
    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    private function setItemData($data){
        $arr = [];
        foreach ($data as $page) {

            if (!array_key_exists($page['id_adp'], $arr)) {
                $arr[$page['id_adp']] = [];
                $arr[$page['id_adp']]['id_adp'] = $page['id_adp'];
                $arr[$page['id_adp']]['price_adp'] = $page['price_adp'];
                $arr[$page['id_adp']]['date_register'] = $page['date_register'];
            }
            $arr[$page['id_adp']]['content'][$page['id_lang']] = array(
                'id_lang'           => $page['id_lang'],
                'iso_lang'          => $page['iso_lang'],
                'name_adp'          => $page['name_adp']
            );
        }
        return $arr;
    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */
    private function saveContent($id)
    {

        foreach ($this->content as $lang => $content) {
            $content['id_lang'] = $lang;
            $content['id_adp'] = $id;
            $content['name_adp'] = (!empty($content['name_adp']) ? $content['name_adp'] : NULL);

            $contentPage = $this->getItems('contentPage', array('id_adp' => $id, 'id_lang' => $lang), 'one', false);

            if ($contentPage != null) {
                $this->upd(
                    array(
                        'type' => 'contentPage',
                        'data' => $content
                    )
                );
            } else {
                $this->add(
                    array(
                        'type' => 'contentPage',
                        'data' => $content
                    )
                );
            }
        }
        //$this->message->json_post_response(true, 'update', array('result'=>$id));
    }
    /**
     * @throws Exception
     */
    public function run(){
        if(isset($this->tableaction)) {
            $this->tableform->run();
        }
        elseif(isset($this->action)) {
            switch ($this->action) {
                case 'add':
                    if(isset($this->addonData)){
                        $newdata = array();
                        $newdata['price_adp'] = (!empty($this->addonData['price_adp'])) ? number_format(str_replace(",", ".", $this->addonData['price_adp']), 4, '.', '') : NULL;
                        // Add data
                        $this->add(array(
                            'type' => 'page',
                            'data' => $newdata
                        ));
                        $page = $this->getItems('root',null,'one',false);
                        if ($page['id_adp']) {
                            $this->saveContent($page['id_adp']);
                            $this->message->json_post_response(true,'add_redirect');
                        }
                    }else{
                        $this->modelLanguage->getLanguage();
                        $this->template->display('add.tpl');
                    }
                    break;
                case 'edit':
                    if(isset($this->addonData)){
                        $newdata = array();
                        $newdata['id_adp'] = $this->id_adp;
                        $newdata['price_adp'] = (!empty($this->addonData['price_adp'])) ? number_format(str_replace(",", ".", $this->addonData['price_adp']), 4, '.', '') : NULL;
                        // Add data
                        $this->upd(array(
                            'type' => 'page',
                            'data' => $newdata
                        ));
                        $this->saveContent($this->id_adp);
                        $this->message->json_post_response(true, 'update', $this->addonData);
                    }else{
                        $this->modelLanguage->getLanguage();
                        $setEditData = $this->getItems('page', array('edit'=>$this->edit),'all',false);
                        $setEditData = $this->setItemData($setEditData);
                        $this->template->assign('page',$setEditData[$this->edit]);
                        $this->template->display('edit.tpl');
                    }
                    break;
            }
        }else{
            $this->modelLanguage->getLanguage();
            $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
            $this->getItems('pages', array('default_lang' => $defaultLanguage['id_lang']), 'all',true,true);
            $this->data->getScheme(array('mc_addonproduct','mc_addonproduct_content'),array('id_adp','name_adp','price_tr','date_register'),$this->tableconfig['all']);
            $this->template->display('index.tpl');
        }
    }
}