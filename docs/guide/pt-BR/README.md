# Yii Data

O pacote fornece abstrações de dados genéricas. O objetivo é ocultar o aspecto de armazenamento das operações de leitura,
escrever e processar dados.

## Conceitos

- Cada conjunto de dados consiste em itens.
- Cada item possui vários campos nomeados.
- Todos os itens de um conjunto de dados possuem a mesma estrutura.

## Lendo dados

O objetivo do leitor de dados é ler dados de um armazenamento como banco de dados, array ou API e convertê-los em um simples iterador de
campo => itens de valor.

```php
$reader = new MyDataReader(...);
$result = $reader->read(); 
```

O resultado é `iterável` então você pode usar `foreach` nele. Se você precisar de um array, isso pode ser feito da seguinte maneira:

```php
// using is foreach
foreach ($result as $item) {
    // ...
}

// preparing array
$dataArray = $result instanceof \Traversable ? iterator_to_array($result, true) : (array)$result;
```

### Limitando o número de itens para ler

O número de itens em um iterador pode ser limitado assim:

```php
$reader = (new MyDataReader(...))->withLimit(10);
foreach ($reader->read() as $item) {
    // ...
}
```

### Contando o número total de itens

Para saber o número total de itens em um leitor de dados implementando `CountableDataInterface`:

```php
$reader = new MyDataReader(...);
$total = count($reader);
```

### Filtragem

A filtragem de dados pode ser feita em duas etapas:

1. Formação de critérios para obtenção dos dados. Isso é feito por "filter".
2. Pós-filtragem de dados por iteração e verificação de cada item.
    Isso é feito por `IterableDataReader` com filtros.

Sempre que possível, é melhor limitar-se ao uso de critérios porque geralmente proporciona um desempenho muito melhor.

Para filtrar dados em um leitor de dados implementando `FilterableDataInterface` você precisa fornecer um filtro para o
método `withFilter()`:

```php
$filter = new AndX(
    new GreaterThan('id', 3),
    new Like('name', 'agent')
);

$reader = (new MyDataReader(...))
    ->withFilter($filter);

$data = $reader->read();
```

O filtro pode ser composto por:

- `AndX`
- `Between`
- `Equals`
- `EqualsNull`
- `GreaterThan`
- `GreaterThanOrEqual`
- `ILike`
- `In`
- `LessThan`
- `LessThanOrEqual`
- `Like`
- `Not`
- `OrX`

#### Filtrando com matrizes

Os filtros `AndX` e `OrX` possuem um método `withCriteriaArray()`, que permite definir filtros com arrays.

```php
$dataReader->withFilter((new AndX())->withCriteriaArray([
    ['=', 'id', 88],
    [
       'or',
       [
          ['=', 'color', 'red'],
          ['=', 'state', 1],
       ]
    ]
]));
```

#### Implementando seu próprio filtro

Para ter seu próprio filtro:

- Implementar pelo menos `FilterInterface`, que inclui:
   - Método `getOperator()` que retorna uma string que representa uma operação de filtro.
   - Método `toArray()` que retorna um array com parâmetros de filtragem.
- Se você deseja criar um manipulador de filtro para um tipo específico de leitor de dados, será necessário implementar pelo menos
`FilterHandlerInterface`. Possui um único método `getOperator()` que retorna uma string representando uma operação de filtro.
Além disso, cada leitor de dados especifica uma interface estendida necessária para manipular ou construir a operação.
*Por exemplo, `IterableDataFilter` define `IterableFilterHandlerInterface`, que contém o método adicional
`match()` para executar um filtro em variáveis PHP.*

Você pode adicionar seus próprios manipuladores de filtro ao leitor de dados usando o método `withFilterHandlers()`. Você pode adicionar qualquer filtro
manipulador para o Reader. Se o leitor não conseguir usar um filtro, o filtro será ignorado.

```php
// own filter for filtering
class OwnNotTwoFilter implenents FilterInterface
{
    private $field;

    public function __construct($field)
    {
        $this->field = $field;
    }
    public static function getOperator(): string
    {
        return 'my!2';
    }
    public function toArray(): array
    {
        return [static::getOperator(), $this->field];
    }
}

// own iterable filter handler for matching
class OwnIterableNotTwoFilterHandler implements IterableFilterHandlerInterface
{
    public function getOperator(): string
    {
        return OwnNotTwoFilter::getOperator();
    }

    public function match(array $item, array $arguments, array $filterHandlers): bool
    {
        [$field] = $arguments;
        return $item[$field] != 2;
    }
}

// and using it on a data reader
$filter = new AndX(
    new LessThan('id', 8),
    new OwnNotTwoFilter('id'),
);

$reader = (new MyDataReader(...))
    ->withFilter($filter)
    ->withFilterHandlers(
        new OwnIterableNotTwoFilter()
        new OwnSqlNotTwoFilter()    // for SQL
        // and for any supported readers...
    );

$data = $reader->read();
```

### Ordenação

Para classificar dados em um leitor de dados implementando `SortableDataInterface` você precisa fornecer um objeto de classificação para o
método `withSort()`:

```php
$sorting = Sort::only([
    'id',
    'name'
]);

$sorting = $sorting->withOrder(['name' => 'asc']);
// or $sorting = $sorting->withOrderString('name');

$reader = (new MyDataReader(...))
    ->withSort($sorting);

$data = $reader->read();
```

O objetivo do `Sort` é mapear lógicamente os campos para classificação de campos de conjunto de dados reais e formar um critério para os dados do
leitor. Os campos lógicos são aqueles com os quais o usuário opera. Campos reais são aqueles realmente presentes em um conjunto de dados.
Esse mapeamento ajuda quando você precisa classificar por um único campo lógico que, na verdade, consiste em vários campos
subjacente ao conjunto de dados. Por exemplo, fornecemos a um usuário um nome de usuário que consiste nos campos nome e sobrenome
no conjunto de dados real.

Para obter uma instância `Sort`, você pode usar `Sort::only()` ou `Sort::any()`. `Sort::only()` ignora a ordem especificada pelo usuário
para campos lógicos que não possuem configuração. `Sort::any()` usa o nome do campo lógico especificado pelo usuário e ordena diretamente
para campos que não possuem configuração.

De qualquer forma, você passa um array de configuração que especifica quais campos lógicos devem ser ordenados e, opcionalmente, detalhes sobre
como eles devem ser mapeados para a ordem real dos campos.

A ordem atual a ser aplicada é especificada via `withOrder()` onde você fornece um array com chaves lógicas correspondentes
aos nomes e valores dos campos correspondem à ordem (`asc` ou `desc`). Alternativamente `withOrderString()` pode ser usado. Nesse caso
a ordenação é representada como uma única string contendo nomes de campos lógicos separados por vírgulas. Se o nome for prefixado por `-`,
a direção do pedido está definida como `desc`.

### Pulando alguns itens

Caso você precise pular alguns itens do início da implementação do leitor de dados `OffsetableDataInterface`:

```php
$reader = (new MyDataReader(...))->withOffset(10);
```

### Implementando seu próprio leitor de dados

Para ter seu próprio leitor de dados você precisa implementar pelo menos `DataReaderInteface`. Possui um único método `read()`
que retorna iterável representando um conjunto de itens.

Interfaces adicionais poderiam ser implementadas para suportar diferentes tipos de paginação, ordenação e filtragem:

- `CountableDataInterface` - permite obter o número total de itens no leitor de dados.
- `FilterableDataInterface` - permite retornar subconjuntos de itens com base em critérios.
- `LimitableDataInterface` - permite retornar um subconjunto limitado de itens.
- `SortableDataInterface` - permite a classificação por um ou vários campos.
- `OffsetableDataInterface` - permite pular os primeiros N itens ao ler dados.

Observe que ao implementá-los, os métodos, em vez de modificar os dados, devem apenas definir critérios que serão utilizados posteriormente
em `read()` para afetar quais dados serão retornados.

## Paginação

A paginação permite obter um subconjunto limitado de dados que é útil para exibir itens página por página e para obter
desempenho aceitável em conjuntos de big data.

Existem dois tipos de paginação fornecidos: paginação de deslocamento tradicional e paginação de conjunto de chaves.

### Paginação Offset (deslocada)

A paginação offset é um método de paginação comum que seleciona itens OFFSET + LIMIT e depois ignora os itens OFFSET.

Vantagens:

- O número total de páginas está disponível
- Pode chegar a uma página específica
- Os dados podem ser desordenados

Desvantagens:

- O desempenho diminui com o aumento do número de páginas
- Inserções ou exclusões no meio dos dados estão tornando os resultados inconsistentes

O uso é o seguinte:

```php
$reader = (new MyDataReader(...));

$paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(10)
            ->withCurrentPage(2);


$total = $paginator->getTotalPages();
$data = $paginator->read();
```

### Paginação Keyset (conjunto de chaves)

A paginação do conjunto de chaves é um método de paginação alternativo que é bom para rolagem infinita e "carregar mais". Ele seleciona os ítens 
LIMIT que possuem campo-chave maior ou menor (dependendo da classificação) que o valor especificado.

Vantagens:

- O desempenho não depende do número da página
- Resultados consistentes independentemente de inserções e exclusões

Desvantagens:

- O número total de páginas não está disponível
- Não é possível acessar a página específica, apenas "anterior" e "próximo"
- Os dados não podem ser desordenados

O uso é o seguinte:

```php
$sort = Sort::only(['id', 'name'])->withOrderString('id');

$dataReader = (new MyDataReader(...))
    ->withSort($sort);

$paginator = (new KeysetPaginator($dataReader))
    ->withPageSize(10)
    ->withToken(PageToken::next('13'));
```

Ao exibir o ID da primeira página (ou outro nome de campo para paginar) do item exibido por último é usado com `withNextPageToken()`
para obter a próxima página.

## Escrevendo dados

```php
$writer = new MyDataWriter(...);
$writer->write($arrayOfItems);
```

## Processing data

```php
$processor = new MyDataProcessor(...);
$processor->process($arrayOfItems);
```
