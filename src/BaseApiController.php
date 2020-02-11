<?php
namespace dynamikaweb\api;

use Yii;

use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * API Controller 
 * 
 * *
 * 
 * Consulta de dados do site
 * 
 * *
 * @copyright Dynamika Soluções WEB [ltda]
 * @author Rodrigo Dornelles <rodrigo@dornelles.me> <rodrigo@dynamika.com.br>
 * 
 * *
 * 
 * @throws Http 400 Bad Request
 * @throws Http 403 Forbiden
 * @throws Http 404 Not Found
 * @throws Http 415 The requested response format is not supported
 * 
 * *
 * 
 * @example https://github.com/dynamikaweb/yii2-dynamika-api/wiki
 */

class BaseApiController extends \yii\web\Controller
{
    const DEFAULT_PAGE_SIZE = 1;

    const DEFAULT_ORDER = ['id' => SORT_DESC];

    const DEFAULT_USE_FILE = false;

        
    const FORMAT_DEFAULT = \yii\web\Response::FORMAT_JSON;
    const FORMAT_JSONP = \yii\web\Response::FORMAT_JSONP;
    const FORMAT_JSON = \yii\web\Response::FORMAT_JSON;
    const FORMAT_XML = \yii\web\Response::FORMAT_XML;
    const FORMAT_YAML = 'yaml';
    const FORMAT_PHP = 'php';

    public $count = 0;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'apiBehavior' => [
                'class' => \dynamikaweb\api\behaviors\ApiResponse::className(),
                'formatAdd' => [
                    self::FORMAT_YAML,
                    self::FORMAT_PHP,
                ],
            ],
            'contentNegotiator' => [
                'class' => \dynamikaweb\api\behaviors\ContentNegotiator::className(),
                'formatParam' => 'format',
                'formatDefault' => self::FORMAT_DEFAULT,
                'formats' => [                    
                    'application/jsonp' => self::FORMAT_JSONP,
                    'application/json' => self::FORMAT_JSON,
                    'application/yaml' => self::FORMAT_YAML,
                    'application/xml' => self::FORMAT_XML,
                    'application/php' => self::FORMAT_PHP,
                ],
            ],
        ];
    }


    /**
     * modulos
     * 
     * Modulos autorizados a serem consumidos pela @api
     * 
     * @return array
     */
    public static function modulos()
    {
        return [];
    }
    
    /**
     * afterAction
     * 
     * @param object $action 
     * @param mixed resultado
     * 
     * @todo comentario sobre $this->parser()
     * 
     * @throws HttpException 415 {format} não é de resposta aceito pelo servidor.
     * 
     * @return boolean
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);    
        $result = $this->parser($result);

        return $result;
    }


    /**
     * actionIndex 
     *
     * estado de funcionamento da api
     * 
     * @return array message => API está funcionando!
     */
    public function actionIndex()
    {        
        return ['message' => 'API está funcionando!'];
    }

    /**
     * actionInfo
     *
     * informações sobre a API
     * 
     * @todo comparar versões [local/repo]
     * 
     * @return array
     *
    public function actionInfo()
    {        
        // valores defaults
        $message = 'API está atualizada!';
        $modulos = static::modulos();
        $local_version = static::LOCAL_UPDATE['version'];
        $last_version = null;
        $updated = true;

        try {
            // consultar por atualizações na api do github
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/dynamikaweb/yii2-dynamika-api/releases/latest');
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $response = curl_exec($ch);
            curl_close($ch);

            // decodificar json
            $githubApi = Json::decode($response, true);

            // extrair informações
            $last_version = ArrayHelper::getValue($githubApi, 'tag_name');
            $last_data = ArrayHelper::getValue($githubApi, 'published_at');

            // verificar se foi possivel buscar por atualizações
            if (!$githubApi || $last_version === null){
                throw new \yii\base\Exception('Não foi possível buscar por atualizações');
            }

            // datas [repo/local]
            $last_data = new \DateTime($last_data);
            $local_data = new \DateTime(static::LOCAL_UPDATE['date']);

            // comparar se api está atualizada
            if ($local_data < $last_data){
                throw new \yii\base\Exception('API está desatualizada!');
            }      
        } catch (\yii\base\Exception $e) {
            // houve um problema sobre a atualização
            $message = $e->getMessage();
            $updated = false;
        }        

        // output
        return [
            'updated' => $updated,
            'message' => $message,
            'last_version' => $last_version,
            'local_version' => $local_version,
            'modulos' => $modulos
        ];
    }
    */

    /**
     * actionView
     * 
     * Visualizar Registros do modulo
     *
     * @param  string $modulo 
     *
     * @return array @api
     */
    public function actionView($modulo)
    {
        // ActiveDataProvider Search
        $dataProvider = $this->findModels($modulo);
        $models = $dataProvider->getModels();  
    
        #$models = script\ImportFile::all();
    
        // Output
        return $models;
    }

    /**
     * actionFile
     * 
     * retorna array de links para arquivos
     *
     * @param  string $modulo => modulo a ser consultado
     * @param  integer $id => identificador do documento
     * @param  string $size => tamanho das imagens
     * 
     * @return array
     */
    public function actionFiles($modulo, $id, $size = 'thumb_')
    {
        $dataProvider = $this->findModels($modulo);
        $models = $dataProvider->getModels();

        // Numero de registros
        $this->count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($this->count == 0){
            throw new HttpException(404,'Nenhum registro foi encontrado!');
        }

        $model = array_shift($models);
        $arquivos = array();

        if ($model->canGetProperty('arquivo')){
            if($arquivo = $model->arquivo){
                self::importFile($arquivos, $arquivo, $modulo, $size, 'arquivo');
            }
        }
        
        if ($model->canGetProperty('arquivos')){
            if($arquivos = $model->arquivos){
                foreach($arquivos as $arquivo){
                    self::importFile($arquivos, $arquivo, $modulo, $size, 'arquivos');
                }
            }
        }

        // Contar arquivos
        $this->count = count($arquivos);


        // Verifica foi encontrado registros
        if($this->count == 0){
            throw new HttpException(404,'Nenhum arquivo foi encontrado!');
        }

        
        return $arquivos;
    }


    /**
     * actionBanners
     * 
     * retorna um array de links para os banners (json)
     *
     * @return array  Banners
     */
    public function actionBanners($prefix = 'original_')
    {
        $searchModel = new \common\models\search\BannerSearch; 
        $searchModel->pageSize = Yii::$app->request->get('limit', 10);
        $searchModel->id = Yii::$app->request->get('id', null);
        $searchModel->withFile = true;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();

        // Numero de registros
        $this->count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($this->count == 0){
            throw new HttpException(404,'Nenhum Banner foi encontrado!');
        }

        $arquivos = array();

        foreach ($models as $model) {
            self::importFile($arquivos, $model->arquivo, 'banner', $prefix, 'arquivo');
        }
        
        return $arquivos;
    }

    /**
     * findModels
     * 
     * encontra os registros de acordo com o modulo
     *
     * @param  string $modulo Modulo
     * 
     * @throws HttpException 403|404 Não autorizado!|Nenhum registro foi encontrado!
     *
     * @return object data provider
     */
    private function findModels($modulo)
    {
        // verificar permissão
        if (\yii\helpers\ArrayHelper::isIn($modulo, static::modulos()) != true) {
            throw new \yii\web\HttpException(403, 'Não autorizado!');
        } 

        // Classe {modulo}Search 
        $moduloClass = '\common\models\search\\'.ucfirst($modulo).'Search';        
        
        // Filtros ActiveRecord
        $searchModel = new $moduloClass;
        $searchModel->pageSize = Yii::$app->request->get('limit', static::DEFAULT_PAGE_SIZE); // Numero de registros por paginas
        $searchModel->order = Yii::$app->request->get('order', static::DEFAULT_ORDER); // Ordem dos registros
        $searchModel->id = Yii::$app->request->get('id', null); // Alias para ModuloSearch[id]
        
        // ActiveDataProvider resultado
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Verificar quantidade de registros
        if ($dataProvider->getCount() == 0){
            throw new \yii\web\HttpException(404, 'Nenhum registro foi encontrado!');
        }

        // Numero de registros
        $this->count = $dataProvider->getCount();  

        return $dataProvider;
    }


    /**
     * importFile
     *
     * @param  array $arquivos => array que sera importa o arquivo (referencia)
     * @param  object $arquivo => arquivo para ser importado
     * @param  string $modulo => modulo onde se encontra o arquivo
     * @param  string $size => tamanho das imagens
     *
     * @return boolean
     */
    private static function importFile(&$arquivos, $arquivo, $modulo, $size, $local = 'desconhecido')
    {
        // não executar quando não houver arquivo para importar
        if($arquivo === null){
            return false;
        }

        $prefix = $arquivo->tipo == $arquivo::TIPO_IMAGEM ? $size:''; // prefixo tamano da imagem
        $url = Url::to($arquivo->getFileUrl($modulo, $prefix), true); // Url Absoluta

        $arquivos []= [
            'local' =>  $local,
            'url' =>  $url            
        ];

        return true;
    }

    /**
     * parser
     * 
     * altera todos os links para serem absolutos e utilizarem target=_system
     * 
     * @param array $database => conteudo para ser corrigido
     * 
     * @return array
     */
    private static function parser($database)
    {
        // link da aplicação
        $url = Url::base(true).'/';

        // não tem o que substituir
        if(!isset($database[0]['descricao'])){
            return $database;
        }

        foreach ($database as $index => $data)
        {
            // capturar conteudo
            $data = $data['descricao'];

            // adicionar link absoluto e target
            $data = strtr($data, [
                'target' => 'data-oldtarget',
                'href="' => "target=\"_system\" href=\"{$url}",
                '../' => '',
            ]);

            // remover problemas com links de terceiros
            $data = strtr($data, [
                "{$url}mailto" => "mailto",
                "{$url}http" => "http"
            ]);

            // implementar conteudo
            $database[$index]['descricao'] = $data;
        }

        return $database;
    }
} 
