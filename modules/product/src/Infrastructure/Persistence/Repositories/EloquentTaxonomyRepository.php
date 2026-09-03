<?php
namespace Modules\Product\Infrastructure\Persistence\Repositories;
use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\TaxonomyRepository;
use Modules\Product\Domain\Category;
use Modules\Product\Domain\Collection;
use Modules\Product\Domain\Tag;
/** Maps taxonomy definitions and product memberships to product.* tables. */
final class EloquentTaxonomyRepository implements TaxonomyRepository
{
    private function table(string $name): object { return DB::connection('product')->table($name); }
    public function productExists(string $id): bool { return $this->table('products')->where('id', $id)->exists(); }
    public function categoryExists(string $id): bool { return $this->table('categories')->where('id', $id)->exists(); }
    public function tagExists(string $id): bool { return $this->table('tags')->where('id', $id)->exists(); }
    public function collectionExists(string $id): bool { return $this->table('collections')->where('id', $id)->exists(); }
    public function createCategory(Category $category): void { $this->table('categories')->insert(['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'parent_id' => $category->parentId, 'position' => $category->position, 'created_at' => now(), 'updated_at' => now()]); }
    public function createTag(Tag $tag): void { $this->table('tags')->insert(['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug, 'created_at' => now(), 'updated_at' => now()]); }
    public function createCollection(Collection $collection): void { $this->table('collections')->insert(['id' => $collection->id, 'name' => $collection->name, 'slug' => $collection->slug, 'kind' => $collection->kind, 'match' => $collection->match, 'created_at' => now(), 'updated_at' => now()]); }
    public function assignCategory(string $productId, string $categoryId, bool $canonical, int $position): void { DB::connection('product')->transaction(function () use ($productId, $categoryId, $canonical, $position): void { if ($canonical) $this->table('product_categories')->where('product_id', $productId)->update(['is_canonical' => false]); $this->table('product_categories')->updateOrInsert(['product_id' => $productId, 'category_id' => $categoryId], ['is_canonical' => $canonical, 'position' => $position]); }); }
    public function assignTag(string $productId, string $tagId): void { $this->table('product_tags')->insertOrIgnore(['product_id' => $productId, 'tag_id' => $tagId]); }
    public function addCollectionProduct(string $collectionId, string $productId, int $position): void { $this->table('collection_products')->updateOrInsert(['collection_id' => $collectionId, 'product_id' => $productId], ['position' => $position]); }
    public function addCollectionRule(string $id, string $collectionId, string $field, string $operator, string $value): void { $this->table('collection_rules')->insert(['id' => $id, 'collection_id' => $collectionId, 'field' => $field, 'operator' => $operator, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]); }
}