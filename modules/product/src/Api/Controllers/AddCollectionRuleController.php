<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CollectionRuleRequest;
use Modules\Product\Application\Taxonomy\{AddCollectionRuleCommand,TaxonomyHandler};
use Shared\Ids;
/** POST /api/v1/collections/{id}/rules. */
final class AddCollectionRuleController { public function __construct(private TaxonomyHandler $handler, private Ids $ids) {} public function __invoke(CollectionRuleRequest $request, string $id): JsonResponse { $ruleId = $this->ids->uuid7(); $this->handler->addCollectionRule(new AddCollectionRuleCommand($ruleId, $id, $request->string('field')->toString(), $request->string('operator')->toString(), $request->string('value')->toString())); return response()->json(['data' => ['id' => $ruleId]], 201); } }