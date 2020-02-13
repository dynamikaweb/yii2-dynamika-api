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
 * 
 * @version 1.0     (12/09/2019) => primeira versÃ£o funcional
 * @version 2.0     (08/11/2019) => suporte de arquivos
 * @version 2.1     (27/11/2019) => request especial [banner/artigo]
 * @version 2.2     (20/01/2020) => links absolutos e target system
 * @version 2.3     (23/01/2020) => exibir localalização do arquivo
 * @version 2.3.1   (28/01/2020) => correções gerais para API global
 * @version 2.4     (05/02/2020) => Melhorias no feedback de resposta
 * @author Rodrigo Dornelles <rodrigo@dornelles.me> <rodrigo@dynamika.com.br>
 * 
 * *
 * 
 * @throws Http 400 Bad Request
 * @throws Http 403 Forbiden
 * @throws Http 404 Not Found
 * 
 * *
 * 
 * @example     
 *   "name":"Status"
 *   "message":"API está funcionando!"
 *   "code":1
 *   "status":200
 *   "type": "yii\\web\\Application"    
 */

class BaseApiController extends \yii\web\Controller
{
    const LOCAL_UPDATE = [
        'date' => '2020-02-05 23:59:59',
        'version' => '2.4.1'
    ];

    protected $count = 0;

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
     * beforeAction
     * 
     * antes da acton ser chamada
     *
     * @param  object $action
     *
     * @return boolean run action
     */
    public function beforeAction($action)
    {
        // Formato de saida sera em json
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Formato de estrura em ['sucess','count','data]
        Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) {
                // yii\web\Response
                $response = $event->sender;

                // Dados
                $count = $response->isSuccessful? $this->count: 0;
                $success = $response->isSuccessful;                
                $data = $response->data;        
                
                // Esconder classe do erro
                if  (!$success) {
                    \yii\helpers\ArrayHelper::remove($data, 'type');
                } else {
                    // Ajustes de dados
                    $data = $this->parser($data);
                }
                
                // Suprimir o status code
                if (Yii::$app->request->get('suppress_response_code')){
                    $response->statusCode = 200;
                }


                // Saida
                $response->data = [
                    'success' => $success,
                    'count' => $count,
                    'data' => $data                    
                ];
            }
        );


        // Verificar autorizaÃ§Ã£o do modulo
        if( $modulo = Yii::$app->request->get('modulo') ){
            self::can($modulo);
        }

        return parent::beforeAction($action); 
    }


    /**
     * actionIndex 
     *
     * estado de funcionamento da api
     * 
     * @return string API está funcionando!
     */
    public function actionIndex()
    {        
        return "API está funcionando!";
    }


    /**
     * actionView
     * 
     * Visualizar Registros do modulo
     *
     * @param  string $modulo 
     *
     * @return array @api
     */
    public function actionView($modulo, $files = false, $size = 'thumb_')
    {
        // ActiveDataProvider Search
        $dataProvider = $this->findModels($modulo);
        $models = $dataProvider->getModels();  
        
        // Numero de registros
        $this->count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($this->count == 0){
            throw new HttpException(404,'Nenhum registro foi encontrado!');
        }

        // incluir arquivos junto ao registro?
        if ($files){
            // instanciado data
            $data = array();

            // Adiciona arquivos aos registros
            foreach ($models as $key =>  $model) {
                // convert model to array
                $data []= ArrayHelper::toArray($model);
                
                // modulo possui arquivo unico
                if ($model->canGetProperty('arquivo')){
                    // adicionar arquivo unico
                    if($arquivo = $model->arquivo){
                        //importar arquivo para o registro
                        self::importFile($data[$key]['files'], $arquivo, $modulo, $size, 'arquivo');
                    }
                }

                // modulo possui arquivo unico
                if ($model->canGetProperty('id_banner')){
                    // adicionar arquivo unico
                    if($arquivo = $model->banner){
                        //importar arquivo para o registro
                        self::importFile($data[$key]['files'], $arquivo, $modulo, $size, 'banner');
                    }
                }

                // modulo possui arquivo unico
                if ($model->canGetProperty('id_capa')){
                    // adicionar arquivo unico
                    if($arquivo = $model->capa){
                        //importar arquivo para o registro
                        self::importFile($data[$key]['files'], $arquivo, $modulo, $size, 'capa');
                    }
                }

                // modulo possui arquivos multiplos
                if ($model->canGetProperty('arquivos')){
                    // adicionar arquivos multiplos
                    if($arquivos = $model->arquivos){
                        foreach($arquivos as $arquivo){
                            // importar arquivo para o registro
                            self::importFile($data[$key]['files'], $arquivo, $modulo, $size, 'arquivos');
                        }
                    }
                }
            }
            // models with files
            $models = $data;
        }

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
     * findModels
     * 
     * encontra os registros de acordo com o modulo
     *
     * @param  string $modulo Modulo
     *
     * @return object data provider
     */
    private function findModels($modulo, $limit = 1)
    {
        // Classe {modulo}Search 
        $modulo = '\common\models\search\\'.ucfirst($modulo).'Search';        
        
        // Filters SearchModel
        $searchModel = new $modulo;
        $searchModel->pageSize = Yii::$app->request->get('limit', $limit);
        $searchModel->id = Yii::$app->request->get('id', null);
        
        // ActiveDataProvider Search
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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
     * can
     * 
     * verifica se o modulo estÃ¡ disponivel para utilizaÃ§Ã£o
     *
     * @param  string $modulo
     * @throws HttpException 403 NÃ£o autorizado, se nÃ£o for possivel encontrar o modulo.
     *
     * @return void 
     */
    private static function can($modulo)
    {
        if( array_search($modulo, static::modulos()) === false ){
            throw new HttpException(403, 'Não autorizado!');
        }
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
