<?php
namespace Modules\Product\Application\Taxonomy;
use Modules\Product\Application\Exceptions\ProductValidationException;
use Modules\Product\Application\Ports\TaxonomyRepository;
use Modules\Product\Domain\Category;
use Modules\Product\Domain\Collection;
use Modules\Product\Domain\Tag;
/** Orchestrates taxonomy definitions, assignments, and collection rules. */
final class TaxonomyHandler
{
    public function __construct(private TaxonomyRepository $taxonomy) {}
    public function createCategory(CreateCategoryCommand $c): void { if ($c->parentId !== null && !$this->taxonomy->categoryExists($c->parentId)) throw ProductValidationException::withMessages(['parent_id' => 'Parent category does not exist.']); $this->taxonomy->createCategory(new Category($c->id, $c->name, $c->slug, $c->parentId, $c->position)); }
    public function createTag(CreateTagCommand $c): void { $this->taxonomy->createTag(new Tag($c->id, $c->name, $c->slug)); }
    public function createCollection(CreateCollectionCommand $c): void { $this->taxonomy->createCollection(new Collection($c->id, $c->name, $c->slug, $c->kind, $c->match)); }
    public function assignCategory(AssignCategoryCommand $c): void { $this->assertProduct($c->productId); if (!$this->taxonomy->categoryExists($c->categoryId)) throw ProductValidationException::withMessages(['category_id' => 'Category does not exist.']); $this->taxonomy->assignCategory($c->productId, $c->categoryId, $c->canonical, $c->position); }
    public function assignTag(AssignTagCommand $c): void { $this->assertProduct($c->productId); if (!$this->taxonomy->tagExists($c->tagId)) throw ProductValidationException::withMessages(['tag_id' => 'Tag does not exist.']); $this->taxonomy->assignTag($c->productId, $c->tagId); }
    public function addCollectionProduct(AddCollectionProductCommand $c): void { if (!$this->taxonomy->collectionExists($c->collectionId)) throw ProductValidationException::withMessages(['collection_id' => 'Collection does not exist.']); $this->assertProduct($c->productId); if ($c->position < 0) throw ProductValidationException::withMessages(['position' => 'Position must be non-negative.']); $this->taxonomy->addCollectionProduct($c->collectionId, $c->productId, $c->position); }
    public function addCollectionRule(AddCollectionRuleCommand $c): void { if (!$this->taxonomy->collectionExists($c->collectionId)) throw ProductValidationException::withMessages(['collection_id' => 'Collection does not exist.']); $this->taxonomy->addCollectionRule($c->id, $c->collectionId, $c->field, $c->operator, $c->value); }
    private function assertProduct(string $id): void { if (!$this->taxonomy->productExists($id)) throw ProductValidationException::withMessages(['product_id' => 'Product does not exist.']); }
}