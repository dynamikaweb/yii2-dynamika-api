<?php 






/*
// incluir arquivos junto ao registro?
        /*if ($files){
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
        }*/
        