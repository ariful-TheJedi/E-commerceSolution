<?php
namespace Modules\Product\Application\Ports;
use Modules\Product\Domain\Category;
use Modules\Product\Domain\Collection;
use Modules\Product\Domain\Tag;
/** Persists product-owned taxonomy definitions and memberships. */
interface TaxonomyRepository
{
    public function productExists(string $id): bool;
    public function categoryExists(string $id): bool;
    public function tagExists(string $id): bool;
    public function collectionExists(string $id): bool;
    public function createCategory(Category $category): void;
    public function createTag(Tag $tag): void;
    public function createCollection(Collection $collection): void;
    public function assignCategory(string $productId, string $categoryId, bool $canonical, int $position): void;
    public function assignTag(string $productId, string $tagId): void;
    public function addCollectionProduct(string $collectionId, string $productId, int $position): void;
    public function addCollectionRule(string $id, string $collectionId, string $field, string $operator, string $value): void;
}