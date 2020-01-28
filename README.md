Dynamika API Controller
==================
base para o controller utilizado como restful api em projetos da dynamika, verificar documentação sobre o funcionamento para o consumo de dados.

 * master link: https://crpsp.org/api
 * develop link: http://crpsp.rodrigo.dynamika.com.br:8080/api

## Exemplos de utilização

**Parametros de Entrada:**
 * `modulo` (obrigatório em view) modulo onde sera feito a consulta
 * `limit` (padrão: 1) limite de registros
 * `files` (padrão: 0) utilizar arquivos
 * `page` (padrão: 1) pagina de registros
 * `size` tamanho das imagens
 * `sort` ordenação dos registros 
 * `id` alias mais simples de  ***Modulo*Search[id]**

**Parametros de Saida:**
 * `name` Tipo da Saida
 * `message` Relatório da consulta
 * `code` 1 se existir algum dado em `data`
 * `status` codigo de estado da requisição (http code)
 * `type` class
 * `data` dados para consumo
 * `count` contagem de registros

#### Disponibilidade
sempre retorna `"data":true` enquanto à api estiver em funcionado. 

request | `name` | `code` | `status` 
:------ | ------ | ------ | -------
/api | Status | 1 | 200 
```JSON
{"name":"api/status","message":"API está funcionando!","code":1,"status":200,"type":"yii\\web\\Application","data":true}
```

#### Visualização
retorna os registros, o parametro `modulo` é obrigatório se estiver faltando ou não for autorizado vai ocorrer um erro.

request | `name` | `code` | `status` 
:------ | ------ | ------ | -------
/api/view | Bad Request | 0 | 400 
/api/view?modulo=usuario | Forbiden | 0 | 403 
/api/view?modulo=noticia | View | 1 | 200 
/api/view?modulo=noticia&limit=5 | View | 1 | 200 


```JSON
{"name":"Bad Request","message":"Parâmetros obrigatórios ausentes: modulo","code":0,"status":400,"type":"yii\\web\\BadRequestHttpException"}

{"name":"Forbidden","message":"Não autorizado!","code":0,"status":403,"type":"yii\\web\\HttpException"}

{"name":"Api/View","message":"Concluído com sucesso!","code":1,"status":200,"type":"yii\\web\\Application","data":[{"id":2356, ...}],"count":1}
```

#### Ordenação
por padrão os registros ficam em ordem por ID, mas é possivel escolher a ordenação com os atributos do modulo

request | ordem |
:------ | :---- | 
/api/view?modulo=noticia&limit=10 | Ordenação padrão `DESC id` |
/api/view?modulo=noticia&limit=10&sort=titulo | Titulo Crescente `ASC titulo` | 
/api/view?modulo=noticia&limit=10&sort=-titulo | Titulo Decrescente `DESC titulo` | 
/api/view?modulo=noticia&limit=10&sort=-data_publicacao | Data Decrescente `DESC data_publicacao` | 


#### Filtrar 
para estabelecer filtros dos registros basta adicionar o parametro ***Modulo*Search[*atributo*]**, onde **Modulo** é o nome do modulo iniciando de letra maiscula, e **atributo** é o campo a ser buscado

request | filtro |
:------ | :---- | 
/api/view?modulo=noticia?NoticiaSearch[titulo]=Sexta+Feira | noticias que contem `sexta feira` no titulo |
/api/view?modulo=noticia?NoticiaSearch[old_importado]=1 | apenas noticias antigas importadas |
/api/view?modulo=noticia?NoticiaSearch[id]=555 | existe um alias de apenas `id`
/api/view?modulo=noticia?NoticiaSearch[destaque]=1&NoticiaSearch[fonte_link]=crpsp | dois ou mais filtros



#### Arquivos

### Registros e Arquivos
pode ser feita uma busca conjunta por arquivos e registros, apenas adicionando o paremtro **files=true** na requisição. 

request |
:------ | 
/api/view?modulo=noticia?files=true |

```JSON
{"name":"Api/View","message":"Concluído com sucesso!","code":1,"status":200,"type":"yii\\web\\Application","data":[{"id":2356, ..., "files":[...]}],"count":1}
```

### Apenas arquivos
**Parametros de Entrada:**
 * `size` (obrigatório, padrão é `thumb_`) tamanho das imagens (`original_`, `maior_` `media_`, `thumb_`)
 * `id` (obrigatório) registro

request | `name` | `code` | `status` 
:------ | ------ | ------ | -------
/api/files?modulo=noticia&id=555 | Files | 1 | 200 
/api/files?modulo=noticia&id=555&size=original_ | Files | 1 | 200 

```JSON
{"name":"Api/Files","message":"Concluído com sucesso!","code":1,"status":200,"type":"yii\\web\\Application","data":[{"id":3230,"arquivo":"X_psrRaUva2uDgjIuMAECgQp_fcf-GHj.JPG","tipo_mime":"image/jpeg","tamanho":"190512","nome_original":"08.JPG","legenda":null,"posicao":1,"data_publicacao":"25/10/2019","tipo":"I","ativo":true,"data_criacao":"25/10/2019 18:06:45","data_modificacao":"25/10/2019 18:06:45","id_arquivo_categoria":null,"prefix":"thumb_","url":"/uploads/noticia/3230/thumb_X_psrRaUva2uDgjIuMAECgQp_fcf-GHj.JPG"}],"count":1}
```
