<?php
namespace dynamikaweb\api;

use Yii;

use yii\helpers\Url;
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
 * @version 1.0 (12/09/2019) => primeira versÃ£o funcional
 * @version 2.0 (08/11/2019) => suporte de arquivos
 * @version 2.1 (27/11/2019) => request especial [banner/artigo]
 * @version 2.2 (20/01/2020) => links absolutos e target system
 * @version 2.3 (23/01/2020) => exibir localalização do arquivo
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

class ApiController extends \yii\base\Controller
{
    /**
     * @property MODULOS
     * 
     * Modulos autorizados a serem consumidos pela @api
     * 
     */
    const MODULOS = [
        
    ];

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
     * @return array @api
     */
    public function actionIndex()
    {        
        return $this->json( true, [
            'name' => Yii::$app->controller->id.'/status',
            'message' => 'API está funcionando!'
        ]);
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
        $count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($count == 0){
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
        }

        // Output
        return $this->json($files? $data: $models, ['count' => $count]);
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
     * @return void
     */
    public function actionFiles($modulo, $id, $size = 'thumb_')
    {
        $dataProvider = $this->findModels($modulo);
        $models = $dataProvider->getModels();
        $count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($count == 0){
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
        $count = count($arquivos);


        // Verifica foi encontrado registros
        if($count == 0){
            throw new HttpException(404,'Nenhum arquivo foi encontrado!');
        }

        
        return $this->json($arquivos, ['count' => $count]);
    }


    /**
     * actionBanners
     * 
     * retorna um array de links para os banners (json)
     *
     * @return object Banners
     */
    public function actionBanners($prefix = 'original_')
    {
        $searchModel = new \common\models\search\BannerSearch; 
        $searchModel->pageSize = Yii::$app->request->get('limit', 10);
        $searchModel->id = Yii::$app->request->get('id', null);
        $searchModel->withFile = true;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();
        $count = $dataProvider->getCount();

        // Verifica foi encontrado registros
        if($count == 0){
            throw new HttpException(404,'Nenhum Banner foi encontrado!');
        }

        $data = array();

        foreach ($models as $model) {
            $data []= $model->arquivo->getFileUrl('banner', $prefix);
        }
        
        return $this->json($data, ['count' => $count]);
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
     * json
     * 
     * formata array para padrÃ£o de saida da @api
     *
     * @param  mixed $data dados para ser consumidos pela @api
     * @param  mixed $params parametros de resposta personalizado
     *
     * @return array saida da @api
     */
    private function json($data, $params = [])
    {
        // parametros de resposta
        $default = [
            'name' => Yii::$app->controller->id.'/'.Yii::$app->controller->action->id,
            'message' => 'Concluído com sucesso!',
            'code' => 1,
            'status' => 200,
            'type' => 'yii\\web\\Application',
            'data' => self::parser($data) 
        ];

        // parametros personalizados sobrepoem os padrÃµes
        return ArrayHelper::merge($default, $params);
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
        if( array_search($modulo, self::MODULOS) === false ){
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

        foreach ($database as $index => $data)
        {
            // caso não possuir campo descrição ignorar função
            if (!isset($data['descricao'])){
                break;
            }

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