<?php
namespace Yiisoft\Data;

/**
 * BaseDataProvider provides a base class that implements the [[DataProviderInterface]].
 *
 * For more details and usage information on BaseDataProvider, see the [guide article on data providers](guide:output-data-providers).
 */
abstract class BaseDataProvider implements DataProviderInterface
{
    /**
     * @var int Number of data providers on the current page. Used to generate unique IDs.
     */
    private static $counter = 0;
    /**
     * @var string an ID that uniquely identifies the data provider among all data providers.
     * Generated automatically the following way in case it is not set:
     *
     * - First data provider ID is empty.
     * - Second and all subsequent data provider IDs are: "dp-1", "dp-2", etc.
     */
    public $id;

    private $sort;
    private $pagination;
    private $keys;
    private $models;
    private $totalCount;


    public function __construct($id = null)
    {
        $this->id = $id;
        if ($this->id === null) {
            if (self::$counter > 0) {
                $this->id = 'dp-' . self::$counter;
            }
            self::$counter++;
        }
    }

    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    abstract protected function prepareModels(): array;

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    abstract protected function prepareKeys(array $models): array;

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return int total number of data models in this data provider.
     */
    abstract protected function prepareTotalCount(): int;

    /**
     * Prepares the data models and keys.
     *
     * This method will prepare the data models and keys that can be retrieved via
     * [[getModels()]] and [[getKeys()]].
     *
     * This method will be implicitly called by [[getModels()]] and [[getKeys()]] if it has not been called before.
     *
     * @param bool $forcePrepare whether to force data preparation even if it has been done before.
     */
    public function prepare(bool $forcePrepare = false): void
    {
        if ($forcePrepare || $this->models === null) {
            $this->models = $this->prepareModels();
        }
        if ($forcePrepare || $this->keys === null) {
            $this->keys = $this->prepareKeys($this->models);
        }
    }

    /**
     * Returns the data models in the current page.
     * @return array the list of data models in the current page.
     */
    public function getModels(): array
    {
        $this->prepare();

        return $this->models;
    }

    /**
     * Sets the data models in the current page.
     * @param array $models the models in the current page
     */
    public function setModels(array $models): void
    {
        $this->models = $models;
    }

    /**
     * Returns the key values associated with the data models.
     * @return array the list of key values corresponding to [[models]]. Each data model in [[models]]
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys(): array
    {
        $this->prepare();

        return $this->keys;
    }

    /**
     * Sets the key values associated with the data models.
     * @param array $keys the list of key values corresponding to [[models]].
     */
    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }

    /**
     * Returns the number of data models in the current page.
     * @return int the number of data models in the current page.
     */
    public function getCount(): int
    {
        return count($this->getModels());
    }

    /**
     * Returns the total number of data models.
     * When [[pagination]] is false, this returns the same value as [[count]].
     * Otherwise, it will call [[prepareTotalCount()]] to get the count.
     * @return int total number of possible data models.
     */
    public function getTotalCount(): int
    {
        if ($this->getPagination() === null) {
            return $this->getCount();
        }

        if ($this->totalCount === null) {
            $this->totalCount = $this->prepareTotalCount();
        }

        return $this->totalCount;
    }

    /**
     * Sets the total number of data models.
     * @param int $value the total number of data models.
     */
    public function setTotalCount(int $value): void
    {
        $this->totalCount = $value;
    }

    /**
     * Returns the pagination object used by this data provider.
     * Note that you should call [[prepare()]] or [[getModels()]] first to get correct values
     * of [[Pagination::totalCount]] and [[Pagination::pageCount]].
     * @return Pagination|null the pagination object. If this is null, it means the pagination is disabled.
     */
    public function getPagination(): ?Pagination
    {
        if ($this->pagination === null) {
            $this->setPagination([]);
        }

        return $this->pagination;
    }

    /**
     * Sets the pagination for this data provider.
     * @param Pagination|null $value the pagination to be used by this data provider.
     * This can be one of the following:
     *
     * - an instance of [[Pagination]] or its subclass
     * - null, if pagination needs to be disabled.
     */
    public function setPagination(?Pagination $value): void
    {
        $this->pagination = $value;
    }

    /**
     * Returns the sorting object used by this data provider.
     * @return Sort|null the sorting object. If this is null, it means the sorting is disabled.
     */
    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    /**
     * Sets the sort definition for this data provider.
     * @param Sort|null $value the sort definition to be used by this data provider.
     * This can be one of the following:
     *
     * - an instance of [[Sort]] or its subclass
     * - null, if sorting needs to be disabled.
     */
    public function setSort(?Sort $value): void
    {
        $this->sort = $value;
    }

    /**
     * Refreshes the data provider.
     * After calling this method, if [[getModels()]], [[getKeys()]] or [[getTotalCount()]] is called again,
     * they will re-execute the query and return the latest data available.
     */
    public function refresh(): void
    {
        $this->totalCount = null;
        $this->models = null;
        $this->keys = null;
    }
}
